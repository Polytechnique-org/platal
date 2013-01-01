<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/


class WSDirectoryRequest
{
    // Default number of returned results.
    const DEFAULT_AMOUNT = 20;

    public $fields;
    public $criteria;
    public $order = array();
    public $amount = 0;
    protected $partner = null;

    const ORDER_RAND = 'rand';
    const ORDER_NAME = 'name';
    const ORDER_PROMOTION = 'promotion';

    public static $order_choices = array(
        self::ORDER_RAND,
        self::ORDER_NAME,
        self::ORDER_PROMOTION,
    );

    public function __construct($partner, PlDict $payload)
    {
        $this->partner = $partner;
        global $globals;

        $this->fields = array_intersect($payload->v('fields'), WSRequestFields::$choices);
        $this->order = array_intersect($payload->v('order', array()), self::$order_choices);

        $this->criteria = array();
        $criteria = new PlDict($payload->v('criteria'));
        foreach (WSRequestCriteria::$choices_simple as $criterion) {
            if ($criteria->has($criterion)) {
                $this->criteria[$criterion] = $criteria->s($criterion);
            }
        }
        foreach (WSRequestCriteria::$choices_enum as $criterion) {
            if ($criteria->has($criterion)) {
                $this->criteria[$criterion] = $criteria->s($criterion);
            }
        }
        foreach (WSRequestCriteria::$choices_list as $criterion) {
            if ($criteria->has($criterion)) {
                $this->criteria[$criterion] = $criteria->v($criterion);
            }
        }

        // Amount may not exceed $globals->sharingapi->max_result_per_query.
        $amount = $payload->i('amount', self::DEFAULT_AMOUNT);
        $this->amount = min($amount, $globals->sharingapi->max_result_per_query);
    }

    public function get()
    {
        $cond = $this->getCond();
        $cond->addChild(new UFC_PartnerSharing($this->partner->id));
        $pf = new ProfileFilter($cond, $this->getOrders());
        $pf->restrictVisibilityForPartner($this->partner->id);
        $response = array();
        $matches = $pf->getTotalProfileCount();
        $response['matches'] = $matches;

        $profiles = array();
        if ($matches) {
            // TODO : improve fetching by passing an adequate FETCH field
            $iter = $pf->iterProfiles(new PlLimit($this->amount), 0x0000, Visibility::get(Visibility::VIEW_PRIVATE));
            while ($profile = $iter->next()) {
                if ($profile->getPartnerSettings($this->partner->id)->exposed_uid !== 0) {
                    $profile_data = new WSRequestEntry($this->partner, $profile);
                    $profiles[] = $profile_data->getFields($this->fields);
                }
            }
        }
        $response['profiles'] = $profiles;
        return $response;
    }

    public function assignToPage(PlPage $page)
    {
        $response = $this->get();
        $page->jsonAssign('matches', $response['matches']);
        $page->jsonAssign('profiles', $response['profiles']);
    }

    /** Compute the orders to use for the current request.
     * @return array of PlFilterOrder
     */
    protected function getOrders()
    {
        $orders = array();
        foreach ($this->order as $order)
        {
            switch ($order) {
            case self::ORDER_RAND:
                $orders[] = new PFO_Random();
                break;
            case self::ORDER_NAME:
                $orders[] = new UFO_Name(Profile::DN_SORT);
                break;
            case self::ORDER_PROMOTION:
                $orders[] = new UFO_Promo();
                break;
            default:
                break;
            }
        }
        return $orders;
    }

    /** Compute the conditions to use for the current request.
     * @return A PlFilterCondition instance (actually a PFC_And)
     */
    protected function getCond()
    {
        $cond = new PFC_And();
        foreach ($this->criteria as $criterion => $value) {
            switch ($criterion) {

            // ENUM fields
            case WSRequestCriteria::SCHOOL:
                // Useless criterion: we don't need to check on origin school
                if (WSRequestCriteria::$choices_enum[$criterion][$value]) {
                    $cond->addChild(new PFC_True());
                } else {
                    $cond->addChild(new PFC_False());
                };
                break;
            case WSRequestCriteria::DIPLOMA:
                $diploma = WSRequestCriteria::$choices_enum[$criterion][$value];
                $id_X = XDB::fetchOneCell('SELECT  id
                                             FROM  profile_education_enum
                                            WHERE  abbreviation = {?}', 'X');
                $cond->addChildren(array(
                    new UFC_EducationSchool($id_X),
                    new UFC_EducationDegree($diploma),
                ));
                break;

            // TEXT fields
            case WSRequestCriteria::FIRSTNAME:
            case WSRequestCriteria::LASTNAME:
                $cond->addChild(new UFC_NameTokens($value, UFC_NameTokens::FLAG_PUBLIC, false, false, $criterion));
                break;
            case WSRequestCriteria::PROMOTION:
                $cond->addChild(new PFC_Or(
                    new UFC_Promo(UserFilter::OP_EQUALS,
                                  UserFilter::GRADE_ING,
                                  $value),
                    new UFC_Promo(UserFilter::OP_EQUALS,
                                  UserFilter::GRADE_MST,
                                  $value),
                    new UFC_Promo(UserFilter::OP_EQUALS,
                                  UserFilter::GRADE_PHD,
                                  $value)
                ));
                break;
            case WSRequestCriteria::ALT_DIPLOMA:
                $cond->addChild(
                    new UFC_EducationDegree(
                        DirEnum::getIds(DirEnum::EDUDEGREES, $value)));
                break;
            case WSRequestCriteria::DIPLOMA_FIELD:
                $cond->addChild(
                    new UFC_EducationField(
                        DirEnum::getIds(DirEnum::EDUFIELDS, $value)));
                break;
            case WSRequestCriteria::CITY:
                $cond->addChild(
                    new UFC_AddressField($value,
                                         UFC_AddressField::FIELD_LOCALITY,
                                         UFC_Address::TYPE_HOME,
                                         UFC_Address::FLAG_CURRENT));
                break;
            case WSRequestCriteria::COUNTRY:
                $cond->addChild(
                    new UFC_AddressField($value,
                                         UFC_AddressField::FIELD_COUNTRY,
                                         UFC_Address::TYPE_HOME,
                                         UFC_Address::FLAG_CURRENT));
                break;
            case WSRequestCriteria::ZIPCODE:
                $cond->addChild(
                    new UFC_AddressField($value,
                                         UFC_AddressField::FIELD_ZIPCODE,
                                         UFC_Address::TYPE_HOME,
                                         UFC_Address::FLAG_CURRENT));
                break;
            case WSRequestCriteria::JOB_ANY_COUNTRY:
                $cond->addChild(
                    new UFC_AddressField($value,
                                         UFC_AddressField::FIELD_COUNTRY,
                                         UFC_Address::TYPE_PRO,
                                         UFC_Address::FLAG_ANY));
                break;
            case WSRequestCriteria::JOB_CURRENT_CITY:
                $cond->addChild(
                    new UFC_AddressField($value,
                                         UFC_AddressField::FIELD_LOCALITY,
                                         UFC_Address::TYPE_PRO,
                                         UFC_Address::FLAG_ANY));
                break;
            case WSRequestCriteria::JOB_ANY_COMPANY:
            case WSRequestCriteria::JOB_CURRENT_COMPANY:
                $cond->addChild(
                    new UFC_Job_Company(UFC_Job_Company::JOBNAME,
                                        $value));
                break;
            case WSRequestCriteria::JOB_ANY_SECTOR:
            case WSRequestCriteria::JOB_CURRENT_SECTOR:
            case WSRequestCriteria::JOB_CURRENT_TITLE:
                $cond->addChild(
                    new UFC_Job_Terms(DirEnum::getIds(DirEnum::JOBTERMS, $value)));
                break;

            // LIST fields
            case WSRequestCriteria::HOBBIES:
                $subcond = new PFC_Or();
                foreach ($value as $val) {
                    $subcond->addChild(new UFC_Comment($value));
                }
                $cond->addChild($subcond);
                break;
            case WSRequestCriteria::JOB_COMPETENCIES:
            case WSRequestCriteria::JOB_RESUME:
            case WSRequestCriteria::PROFESSIONAL_PROJECT:
                $subcond = new PFC_Or();
                foreach ($value as $val) {
                    $subcond->addChild(
                        new UFC_Job_Description($value, UserFilter::JOB_USERDEFINED));
                }
                $cond->addChild($subcond);
                break;
            case WSRequestCriteria::NOT_UID:
                $cond->addChild(
                    new PFC_Not(
                        new UFC_PartnerSharingID($this->partner->id, $value)));
                break;
            default:
                break;
            }
        }

        return $cond;
    }

    /** Input validation
     */
    const ERROR_MISSING_FIELDS = 'missing_fields';
    const ERROR_MISSING_CRITERIA = 'missing_criteria';
    const ERROR_MALFORMED_AMOUNT = 'malformed_amount';
    const ERROR_MALFORMED_ORDER = 'malformed_order';

    public static $ERROR_MESSAGES = array(
        self::ERROR_MISSING_FIELDS => "The 'fields' field is mandatory.",
        self::ERROR_MISSING_CRITERIA => "The 'criteria' field is mandatory.",
        self::ERROR_MALFORMED_AMOUNT => "The 'amount' value is invalid (expected an int)",
        self::ERROR_MALFORMED_ORDER => "The 'order' value is invalid (expected an array)",
    );

    /** Static method performing all input validation on the payload.
     * @param PlDict $payload The payload to validate
     * @return array Errors discovered when validating input
     */
    public static function validatePayload(PlDict $payload)
    {
        $errors = array();
        if (!$payload->has('fields')) {
            $errors[] = self::ERROR_MISSING_FIELDS;
        }
        if (!$payload->has('criteria')) {
            $errors[] = self::ERROR_MISSING_CRITERIA;
        }

        if ($payload->has('amount') && $payload->i('amount', -1) < 0) {
            $errors[] = self::ERROR_MALFORMED_AMOUNT;
        }

        if (!is_array($payload->v('order', array()))) {
            $errors[] = self::ERROR_MALFORMED_ORDER;
        }

        return $errors;
    }
}

// {{{ WSRequestEntry
/** Performs field retrieval for a profile.
 */
class WSRequestEntry
{
    private $profile = null;
    private $partner = null;
    private $settings = null;

    public function __construct($partner, $profile)
    {
        $this->partner = $partner;
        $this->profile = $profile;
        $this->settings = $this->profile->getPartnerSettings($this->partner->id);
    }

    public function isVisible($level)
    {
        return $this->settings->sharing_visibility->isVisible($level);
    }

    public function getFields($fields)
    {
        $data = array();
        foreach ($fields as $field)
        {
            $val = $this->getFieldValue($field);
            if ($val !== null) {
                $data[$field] = $val;
            }
        }
        $data['uid'] = $this->settings->exposed_uid;
        return $data;
    }

    protected function getFieldValue($field)
    {
        // Shortcut
        $p = $this->profile;

        switch ($field) {
        case WSRequestFields::UID:
            // UID is always included
            return;
        case WSRequestFields::BIRTHDATE:
        case WSRequestFields::FAMILY_POSITION:
        case WSRequestFields::HONORARY_TITLES:
        case WSRequestFields::LANGS:
        case WSRequestFields::JOB_COMPETENCIES:
        case WSRequestFields::RESUME:
        case WSRequestFields::PROFESSIONAL_PROJECT:
        case WSRequestFields::HOBBIES:
            // Ignored fields
            return;

        // Public fields
        case WSRequestFields::FIRSTNAME:
            return $p->firstName();
        case WSRequestFields::LASTNAME:
            return $p->lastName();
        case WSRequestFields::GENDER:
            if ($p->isFemale()) {
                return WSRequestFields::GENDER_WOMAN;
            } else {
                return WSRequestFields::GENDER_MAN;
            }
        case WSRequestFields::SCHOOL:
            return WSRequestCriteria::SCHOOL_X;
        case WSRequestFields::DIPLOMA:
            $edu = $p->getEducations(Profile::EDUCATION_MAIN);
            if (count($edu)) {
                return WSRequestFields::profileDegreeToWSDiploma(
                    array_pop($edu)->degree);
            } else {
                return null;
            }
        case WSRequestFields::DIPLOMA_FIELD:
            $edu = $p->getEducations(Profile::EDUCATION_MAIN);
            if (count($edu)) {
                return array_pop($edu)->field;
            } else {
                return null;
            }
        case WSRequestFields::PROMOTION:
            return $p->yearpromo();
        case WSRequestFields::ALT_DIPLOMAS:
            $diplomas = array();
            foreach ($p->getExtraEducations() as $edu) {
                $diplomas[] = WSRequestFields::profileDegreeToWSDiploma(
                    $edu->degree);
            }
            return $diplomas;

        // Other generic profile fields
        case WSRequestFields::EMAIL:
            if ($this->settings->sharing_visibility->isVisible(Visibility::EXPORT_PRIVATE)) {
                // If sharing "all" data, share best email.
                return $p->displayEmail();
            } elseif ($this->settings->sharing_visibility->isVisible(Visibility::EXPORT_AX)) {
                // If sharing "AX" level, share "AX" email.
                return $p->email_directory;
            } else {
                // Otherwise, don't share.
                return null;
            }
        case WSRequestFields::MOBILE_PHONE:
            $phones = $p->getPhones(Profile::PHONE_TYPE_MOBILE | Profile::PHONE_LINK_PROFILE);
            if (count($phones)) {
                $phone = array_pop($phones);
                if ($this->isVisible($phone->pub)) {
                    return $phone->display;
                }
            }
            return null;
        case WSRequestFields::PIC_SMALL:
        case WSRequestFields::PIC_MEDIUM:
        case WSRequestFields::PIC_LARGE:
            if ($this->isVisible($p->photo_pub)) {
                $token = sha1(uniqid(rand(), true));
                XDB::execute('DELETE FROM  profile_photo_tokens
                                    WHERE  pid = {?}', $p->pid);
                XDB::execute('INSERT INTO  profile_photo_tokens
                                      SET  pid = {?}, token = {?},
                                           expires = ADDTIME(NOW(), \'0:05:00\')',
                                           $p->pid, $token);
                $size_mappings = array(
                    WSRequestFields::PIC_SMALL => 'small',
                    WSRequestFields::PIC_MEDIUM => 'medium',
                    WSRequestFields::PIC_LARGE => 'large',
                );
                $size = $size_mappings[$field];
                return pl_url("api/1/sharing/picture/$size/$token");
            } else {
                return null;
            }

        // Address related
        case WSRequestFields::CURRENT_CITY:
            $address = $p->getMainAddress();
            if ($address != null && $this->isVisible($address->pub)) {
                return $address->locality;
            } else {
                return null;
            }
        case WSRequestFields::CURRENT_COUNTRY:
            $address = $p->getMainAddress();
            if ($address != null && $this->isVisible($address->pub)) {
                return $address->country;
            } else {
                return null;
            }
        case WSRequestFields::ADDRESS:
            $address = $p->getMainAddress();
            if ($address != null && $this->isVisible($address->pub)) {
                return $this->addressToResponse($address);
            } else {
                return null;
            }

        // Job related
        case WSRequestFields::CURRENT_COMPANY:
            $job = $p->getMainJob();
            if ($job != null && $this->isVisible($job->pub)) {
                return $job->company->name;
            } else {
                return null;
            }
        case WSRequestFields::JOB:
            $jobs = $p->getJobs(Profile::JOBS_ALL);
            $res = array();
            foreach ($jobs as $job) {
                if ($this->isVisible($job->pub)) {
                    $res[] = $this->jobToResponse($job);
                }
            }
            return $res;
        case WSRequestFields::MINI_RESUME:
            if ($this->isVisible(Visibility::EXPORT_PRIVATE)) {
                return $p->cv;
            } else {
                return null;
            }

        // Community
        case WSRequestFields::GROUPS:
            $groups = array();
            if ($this->isVisible(Visibility::EXPORT_PRIVATE)) {
                foreach ($p->owner()->groups(true, true) as $group) {
                    $groups[] = array('name' => $group['nom']);
                }
            }
            return $groups;
        case WSRequestFields::FRIENDS:
            $friends = array();
            if ($this->isVisible(Visibility::EXPORT_PRIVATE)) {
                $contacts = $p->owner()->iterContacts();
                if ($contacts == null) {
                    return $friends;
                }

                while ($contact = $contacts->next()) {
                    $cps = $contact->getPartnerSettings($this->partner->id);
                    if ($cps->sharing_visibility->isVisible(Visibility::EXPORT_PRIVATE)) {
                        $friends[] = $cps->exposed_uid;
                    }
                }
            }
            return $friends;
        case WSRequestFields::NETWORKING:
            $networks = array();
            if ($this->isVisible(Visibility::EXPORT_PRIVATE)) {
                foreach ($p->getNetworking(Profile::NETWORKING_ALL) as $nw) {
                    $networks[] = array(
                        'network' => $nw['name'],
                        'login' => $nw['address'],
                    );
                }
            }
            return $networks;

        default:
            return null;
        }
    }

    protected function jobToResponse($job)
    {
        $data = array(
            'company' => $job->company->name,
            'title' => $job->description,
            'sector' => array_pop($job->terms),
            'entry' => null,
            'left' => null,
        );
        foreach($job->phones() as $phone) {
            if ($this->isVisible($phone->pub)) {
                $data['phone'] = $phone->display;
                break;
            }
        }
        if ($job->address && $this->isVisible($job->address->pub)) {
            $data['address'] = $this->addressToResponse($job->address);
        }
        return $data;
    }

    protected function addressToResponse($address)
    {
        $data = array(
            'street' => $address->postalText,
            'zipcode' => $address->postalCode,
            'city' => $address->locality,
            'country' => $address->country,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
        );
        return $data;
    }
}
// }}}
// {{{ WSRequestCriteria
/** Holds all enums and related mappings for criterias.
 */
class WSRequestCriteria
{
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const SCHOOL = 'school';
    const DIPLOMA = 'diploma';
    const DIPLOMA_FIELD = 'diploma_field';
    const PROMOTION = 'promotion';
    const HOBBIES = 'hobbies';
    const ZIPCODE = 'zipcode';
    const CITY = 'city';
    const COUNTRY = 'country';
    const JOB_CURRENT_SECTOR = 'job_current_sector';
    const JOB_CURRENT_TITLE = 'job_current_title';
    const JOB_CURRENT_COMPANY = 'job_current_company';
    const JOB_CURRENT_CITY = 'job_current_city';
    const JOB_CURRENT_COUNTRY = 'job_current_country';
    const JOB_ANY_SECTOR = 'job_any_sector';
    const JOB_ANY_COMPANY = 'job_any_company';
    const JOB_ANY_COUNTRY = 'job_any_country';
    const JOB_RESUME = 'job_resume';
    const JOB_COMPETENCIES = 'job_competencies';
    const PROFESSIONAL_PROJECT = 'professional_project';
    const ALT_DIPLOMA = 'alt_diploma';
    const NOT_UID = 'not_uid';

    public static $choices_simple = array(
        self::FIRSTNAME,
        self::LASTNAME,
        self::PROMOTION,
        self::ALT_DIPLOMA,
        self::DIPLOMA_FIELD,
        self::CITY,
        self::ZIPCODE,
        self::COUNTRY,
        self::JOB_ANY_COUNTRY,
        self::JOB_CURRENT_CITY,
        self::JOB_CURRENT_COUNTRY,
        self::JOB_ANY_COMPANY,
        self::JOB_ANY_SECTOR,
        self::JOB_CURRENT_COMPANY,
        self::JOB_CURRENT_SECTOR,
        self::JOB_CURRENT_TITLE,
    );

    const SCHOOL_AGRO = 'agro';
    const SCHOOL_ENSAE = 'ensae';
    const SCHOOL_ENSCP = 'enscp';
    const SCHOOL_ENST = 'enst';
    const SCHOOL_ENSTA = 'ensta';
    const SCHOOL_ESPCI = 'espci';
    const SCHOOL_GADZ = 'gadz';
    const SCHOOL_HEC = 'hec';
    const SCHOOL_MINES = 'ensmp';
    const SCHOOL_PONTS = 'enpc';
    const SCHOOL_SUPELEC = 'supelec';
    const SCHOOL_SUPOP = 'supop';
    const SCHOOL_X = 'X';

    const DIPLOMA_ING = 'ING';
    const DIPLOMA_MASTER = 'MASTER';
    const DIPLOMA_PHD = 'PHD';

    public static $choices_enum = array(
        self::SCHOOL => array(
            self::SCHOOL_AGRO => false,
            self::SCHOOL_ENSAE => false,
            self::SCHOOL_ENSCP => false,
            self::SCHOOL_ENST => false,
            self::SCHOOL_ENSTA => false,
            self::SCHOOL_ESPCI => false,
            self::SCHOOL_GADZ => false,
            self::SCHOOL_HEC => false,
            self::SCHOOL_MINES => false,
            self::SCHOOL_PONTS => false,
            self::SCHOOL_SUPELEC => false,
            self::SCHOOL_SUPOP => false,
            self::SCHOOL_X => true,
        ),
        self::DIPLOMA => array(
            self::DIPLOMA_ING => UserFilter::GRADE_ING,
            self::DIPLOMA_MASTER => UserFilter::GRADE_MST,
            self::DIPLOMA_PHD => UserFilter::GRADE_PHD,
        ),
    );

    public static $choices_list = array(
        self::HOBBIES,
        self::JOB_COMPETENCIES,
        self::JOB_RESUME,
        self::NOT_UID,
        self::PROFESSIONAL_PROJECT,
    );
}

// }}}
// {{{ WSRequestFields
/** Holds all enums for fields.
 */
class WSRequestFields
{
    const UID = 'uid';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const BIRTHDATE = 'birthdate';
    const GENDER = 'gender';
    const FAMILY_POSITION = 'family_position';
    const SCHOOL = 'school';
    const DIPLOMA = 'diploma';
    const DIPLOMA_FIELD = 'diploma_field';
    const PROMOTION = 'promotion';
    const ALT_DIPLOMAS = 'alt_diplomas';
    const CURRENT_COMPANY = 'current_company';
    const CURRENT_CITY = 'current_city';
    const CURRENT_COUNTRY = 'current_country';
    const MOBILE_PHONE = 'mobile_phone';
    const HONORARY_TITLES = 'honorary_titles';
    const EMAIL = 'email';
    const PIC_SMALL = 'pic_small';
    const PIC_MEDIUM = 'pic_medium';
    const PIC_LARGE = 'pic_large';
    const ADDRESS = 'address';
    const JOB = 'job';
    const GROUPS = 'groups';
    const LANGS = 'langs';
    const JOB_COMPETENCIES = 'job_competencies';
    const MINI_RESUME = 'mini_resume';
    const RESUME = 'resume';
    const PROFESSIONAL_PROJECT = 'professional_project';
    const HOBBIES = 'hobbies';
    const FRIENDS = 'friends';
    const NETWORKING = 'networking';

    const GENDER_MAN = 'man';
    const GENDER_WOMAN = 'woman';

    const DIPLOMA_ING = 'engineer';
    const DIPLOMA_MASTER = 'master';
    const DIPLOMA_PHD = 'phd';

    public static $choices = array(
        self::UID,
        self::FIRSTNAME,
        self::LASTNAME,
        self::BIRTHDATE,
        self::GENDER,
        self::FAMILY_POSITION,
        self::SCHOOL,
        self::DIPLOMA,
        self::DIPLOMA_FIELD,
        self::PROMOTION,
        self::ALT_DIPLOMAS,
        self::CURRENT_COMPANY,
        self::CURRENT_CITY,
        self::CURRENT_COUNTRY,
        self::MOBILE_PHONE,
        self::HONORARY_TITLES,
        self::EMAIL,
        self::PIC_SMALL,
        self::PIC_MEDIUM,
        self::PIC_LARGE,
        self::ADDRESS,
        self::JOB,
        self::GROUPS,
        self::LANGS,
        self::JOB_COMPETENCIES,
        self::MINI_RESUME,
        self::RESUME,
        self::PROFESSIONAL_PROJECT,
        self::HOBBIES,
        self::FRIENDS,
        self::NETWORKING,
    );

    public static function profileDegreeToWSDiploma($degree)
    {
        switch ($degree) {
            case Profile::DEGREE_X:
                return self::DIPLOMA_ING;
            case Profile::DEGREE_M:
                return self::DIPLOMA_MASTER;
            case Profile::DEGREE_D:
                return self::DIPLOMA_PHD;
            default:
                return null;
        }
    }

}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
