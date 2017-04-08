<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

class Profile implements PlExportable
{

    /* name tokens */
    const LASTNAME  = 'lastname';
    const FIRSTNAME = 'firstname';
    const NICKNAME  = 'nickname';
    const PSEUDONYM = 'pseudonym';
    const NAME      = 'name';
    /* name variants */
    const VN_MARITAL  = 'marital';
    const VN_ORDINARY = 'ordinary';
    const VN_OTHER    = 'other';
    const VN_INI      = 'ini';
    /* display names */
    const DN_FULL      = 'directory_name';
    const DN_DISPLAY   = 'yourself';
    const DN_YOURSELF  = 'yourself';
    const DN_DIRECTORY = 'directory_name';
    const DN_PRIVATE   = 'private_name';
    const DN_PUBLIC    = 'public_name';
    const DN_SHORT     = 'short_name';
    const DN_SORT      = 'sort_name';
    /* education related names */
    const EDU_X    = 'École polytechnique';
    const DEGREE_X = 'Ingénieur';
    const DEGREE_M = 'Master';
    const DEGREE_D = 'Doctorat';

    static public $cycles = array(
        self::DEGREE_X => 'polytechnicien',
        self::DEGREE_M => 'master',
        self::DEGREE_D => 'docteur'
    );
    static public $cycle_prefixes = array(
        self::DEGREE_X => 'X',
        self::DEGREE_M => 'M',
        self::DEGREE_D => 'D'
    );

    static public $name_variants = array(
            self::LASTNAME => array(self::VN_MARITAL, self::VN_ORDINARY),
            self::FIRSTNAME => array(self::VN_ORDINARY, self::VN_INI, self::VN_OTHER)
        );

    const ADDRESS_MAIN       = 0x00000001;
    const ADDRESS_PERSO      = 0x00000002;
    const ADDRESS_PRO        = 0x00000004;
    const ADDRESS_ALL        = 0x00000006;
    const ADDRESS_POSTAL     = 0x00000008;

    const EDUCATION_MAIN     = 0x00000010;
    const EDUCATION_EXTRA    = 0x00000020;
    const EDUCATION_ALL      = 0x00000040;
    const EDUCATION_FINISHED = 0x00000080;
    const EDUCATION_CURRENT  = 0x00000100;

    const JOBS_MAIN          = 0x00001000;
    const JOBS_ALL           = 0x00002000;
    const JOBS_FINISHED      = 0x00004000;
    const JOBS_CURRENT       = 0x00008000;

    const NETWORKING_ALL     = 0x00070000;
    const NETWORKING_WEB     = 0x00010000;
    const NETWORKING_IM      = 0x00020000;
    const NETWORKING_SOCIAL  = 0x00040000;

    const PHONE_LINK_JOB     = 0x00100000;
    const PHONE_LINK_ADDRESS = 0x00200000;
    const PHONE_LINK_PROFILE = 0x00400000;
    const PHONE_LINK_COMPANY = 0x00800000;
    const PHONE_LINK_ANY     = 0x00F00000;

    const PHONE_TYPE_FAX     = 0x01000000;
    const PHONE_TYPE_FIXED   = 0x02000000;
    const PHONE_TYPE_MOBILE  = 0x04000000;
    const PHONE_TYPE_ANY     = 0x07000000;

    const PHONE_ANY          = 0x07F00000;

    const FETCH_ADDRESSES      = 0x000001;
    const FETCH_CORPS          = 0x000002;
    const FETCH_EDU            = 0x000004;
    const FETCH_JOBS           = 0x000008;
    const FETCH_MEDALS         = 0x000010;
    const FETCH_NETWORKING     = 0x000020;
    const FETCH_MENTOR_COUNTRY = 0x000080;
    const FETCH_PHONES         = 0x000100;
    const FETCH_JOB_TERMS      = 0x000200;
    const FETCH_MENTOR_TERMS   = 0x000400;
    const FETCH_DELTATEN       = 0x000800;
    const FETCH_PARTNER        = 0x001000;
    const FETCH_SKILL          = 0x002000;
    const FETCH_LANGUAGE       = 0x004000;

    const FETCH_MINIFICHES   = 0x00012D; // FETCH_ADDRESSES | FETCH_EDU | FETCH_JOBS | FETCH_NETWORKING | FETCH_PHONES

    const FETCH_ALL          = 0x007FFF; // OR of FETCH_*

    const EXAMPLE_EMAIL = 'new@example.org'; // email used in profile email management

    static public $descriptions = array(
        'search_names'    => 'Noms',
        'nationality1'    => 'Nationalité',
        'nationality2'    => '2e nationalité',
        'nationality3'    => '3e nationalité',
        'promo_display'   => 'Promotion affichée',
        'email_directory' => 'Email annuaire papier',
        'networking'      => 'Messageries…',
        'tels'            => 'Téléphones',
        'edus'            => 'Formations',
        'main_edus'       => 'Formations à l\'X',
        'promo'           => 'Promotion de sortie',
        'birthdate'       => 'Date de naissance', // This field must be field upon registration if birthdate_ref is known, and may be updated by the user
        'birthdate_ref'   => 'Date de naissance de référence', // This field is used to check users when they register
        'deathdate'       => 'Date de décès',
        'yourself'        => 'Nom affiché',
        'freetext'        => 'Commentaire',
        'freetext_pub'    => 'Affichage du commentaire',
        'axfreetext'      => 'Commentaire AX',
        'photo'           => 'Photographie',
        'photo_pub'       => 'Affichage de la photographie',
        'addresses'       => 'Adresses',
        'corps'           => 'Corps',
        'cv'              => 'CV',
        'jobs'            => 'Emplois',
        'section'         => 'Section',
        'binets'          => 'Binets',
        'medals'          => 'Décorations',
        'medals_pub'      => 'Affichage des décorations',
        'competences'     => 'Compétences',
        'langues'         => 'Langues',
        'expertise'       => 'Expertises (mentoring)',
        'terms'           => 'Compétences (mentoring)',
        'countries'       => 'Pays (mentoring)',
        'deltaten'        => 'Opération N N-10',
    );

    private $fetched_fields  = 0x000000;

    private $pid;
    private $hrpid;
    private $owner;
    private $owner_fetched = false;
    private $data = array();

    private $visibility = null;


    private function __construct(array $data, Visibility $visibility)
    {
        $this->data = $data;
        $this->pid = $this->data['pid'];
        $this->hrpid = $this->data['hrpid'];
        $this->visibility = $visibility;
    }

    public function __destruct()
    {
        // Need to delete objects allocated by the profile
        // http://www.php.net/manual/en/function.unset.php#98692
        unset($this->owner);
    }

    public function id()
    {
        return $this->pid;
    }

    public function hrid()
    {
        return $this->hrpid;
    }

    public function owner()
    {
        if ($this->owner == null && !$this->owner_fetched) {
            $this->owner_fetched = true;
            $this->owner = User::getSilent($this);
        }
        return $this->owner;
    }

    public function isActive()
    {
        if ($this->owner()) {
            return $this->owner->isActive();
        }
        return false;
    }

    public function promo($details = false)
    {
        if ($details && ($this->program || $this->fieldid)) {
            $text = array();
            if ($this->program) {
                $text[] = $this->program;
            }
            if ($this->fieldid) {
                $fieldsList = DirEnum::getOptions(DirEnum::EDUFIELDS);
                $text[] = $fieldsList[$this->fieldid];
            }
            return $this->promo . ' (' . implode(', ', $text) . ')';
        }

        return $this->promo;
    }

    public function yearpromo()
    {
        return $this->promo_year;
    }

    /** Check if user is an orange (associated with several promos)
     */
    public function isMultiPromo()
    {
        return $this->grad_year != $this->entry_year + $this->mainEducationDuration();
    }

    /** Returns an array with all associated promo years.
     */
    public function yearspromo()
    {
        $promos = array();
        $d = -$this->deltaPromoToGradYear();
        for ($g = $this->entry_year + $this->mainEducationDuration(); $g <= $this->grad_year; ++$g) {
            $promos[] = $g + $d;
        }
        return $promos;
    }

    public function mainEducation()
    {
        if (empty($this->promo)) {
            return null;
        } else {
            return $this->promo{0};
        }
    }

    public function mainGrade()
    {
        switch ($this->mainEducation()) {
          case 'X':
            return UserFilter::GRADE_ING;
          case 'M':
            return UserFilter::GRADE_MST;
          case 'D':
            return UserFilter::GRADE_PHD;
          default:
            return null;
        }
    }

    public function mainEducationDuration()
    {
        switch ($this->mainEducation()) {
          case 'X':
            return 3;
          case 'M':
            return 2;
          case 'D':
            return 3;
          default:
            return 0;
        }
    }

    public static function educationDuration($education)
    {
        switch ($education) {
          case self::DEGREE_X:
            return 3;
          case self::DEGREE_M:
            return 2;
          case self::DEGREE_D:
            return 3;
          default:
            return 0;
        }
    }

    /** Number of years between the promotion year until the
     * graduation year. In standard schools it's 0, but for
     * Polytechnique the promo year is the entry year.
     */
    public function deltaPromoToGradYear()
    {
        if ($this->mainEducation() == 'X') {
            return $this->mainEducationDuration();
        }
        return 0;
    }

    // Returns the profile's color.
    public function promoColor()
    {
        switch ($this->mainEducation()) {
          case 'X':
            if (($this->yearpromo() % 2) === 0) {
                return 'red';
            } else {
                return 'yellow';
            }
          case 'M':
            return 'green';
          case 'D':
            return 'blue';
          default:
            return 'gray';
        }
    }

    // Returns younger/older promotion year for a given education.
    static public function extremePromotions($education)
    {
        return XDB::fetchOneRow("SELECT  MIN(pe.promo_year) AS min, MAX(pe.promo_year) AS max
                                     FROM  profile_education             AS pe
                               INNER JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                                    WHERE  pede.degree = {?} AND FIND_IN_SET('primary', pe.flags)",
                                  $education);
    }

    /** Print a name with the given formatting:
     * %s = • for women
     * %f = firstname
     * %l = lastname
     * %F = fullname
     * %S = shortname
     * %p = promo
     */
    public function name($format)
    {
        return str_replace(array('%s', '%f', '%l', '%F', '%S', '%p'),
                           array($this->isFemale() ? '•' : '',
                                 $this->firstName(), $this->lastName(),
                                 $this->fullName(), $this->shortName(),
                                 $this->promo()), $format);
    }

    public function fullName($with_promo = false)
    {
        if ($with_promo) {
            return $this->full_name . ' (' . $this->promo . ')';
        }
        return $this->full_name;
    }

    public function shortName($with_promo = false)
    {
        if ($with_promo) {
            return $this->short_name . ' (' . $this->promo . ')';
        }
        return $this->short_name;
    }

    public function sortName()
    {
        return $this->sort_name;
    }

    public function firstName()
    {
        return $this->firstname_ordinary;
    }

    public function firstNames()
    {
        return $this->nameVariants(self::FIRSTNAME);
    }

    public function lastName()
    {
        return $this->lastname_ordinary;
    }

    public function lastNames()
    {
        return $this->nameVariants(self::LASTNAME);
    }

    public function isFemale()
    {
        return $this->sex == PlUser::GENDER_FEMALE;
    }

    public function isDead()
    {
        return ($this->deathdate != null);
    }

    public function displayEmail()
    {
        $o = $this->owner();
        if ($o != null && $this->isVisible(Visibility::EXPORT_PRIVATE)) {
            return $o->bestEmail();
        } else {
            return $this->email_directory;
        }
    }

    public function data()
    {
        $this->first_name;
        return $this->data;
    }

    private function nameVariants($type)
    {
        $vals = array($this->$type);
        foreach (self::$name_variants[$type] as $var) {
            $vartype = $type . '_' . $var;
            $varname = $this->$vartype;
            if ($varname != null && $varname != "") {
                $vals[] = $varname;
            }
        }
        return array_unique($vals);
    }

    public function nationalities()
    {
        $nats = array();
        $nationalities = DirEnum::getOptions(DirEnum::NATIONALITIES);
        if ($this->nationality1) {
            $nats[$this->nationality1] = $nationalities[$this->nationality1];
        }
        if ($this->nationality2) {
            $nats[$this->nationality2] = $nationalities[$this->nationality2];
        }
        if ($this->nationality3) {
            $nats[$this->nationality3] = $nationalities[$this->nationality3];
        }
        return $nats;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    public function __isset($name)
    {
        return property_exists($this, $name) || isset($this->data[$name]);
    }

    public function __unset($name)
    {
        if (property_exists($this, $name)) {
            $this->$name = null;
        } else {
            unset($this->data[$name]);
        }
    }


    /**
     * Clears a profile.
     *  *always deletes in: profile_addresses, profile_binets, profile_deltaten,
     *      profile_job, profile_langskills, profile_mentor, profile_networking,
     *      profile_partnersharing_settings, profile_phones, profile_skills,
     *      watch_profile
     *  *always keeps in: profile_corps, profile_display, profile_education,
     *      profile_medals, profile_*_names, profile_photos, search_name
     *  *modifies: profiles
     */
    public function clear()
    {
        $tables = array(
            'profile_job', 'profile_langskills', 'profile_mentor',
            'profile_networking', 'profile_skills', 'watch_profile',
            'profile_phones', 'profile_addresses', 'profile_binets',
            'profile_deltaten', 'profile_partnersharing_settings');

        foreach ($tables as $t) {
            XDB::execute('DELETE FROM  ' . $t . '
                                WHERE  pid = {?}',
                                $this->id());
        }

        XDB::execute("UPDATE  profiles
                         SET  cv = NULL, freetext = NULL, freetext_pub = 'private', axfreetext = NULL,
                              medals_pub = 'private', alias_pub = 'hidden',
                              email_directory = NULL
                       WHERE  pid = {?}",
                     $this->id());
    }

    /** Determine whether an item with visibility $visibility can be displayed
     * with the current level of visibility of the profile
     * @param $visibility The level of visibility to be checked
     */
    public function isVisible($visibility)
    {
        return $this->visibility->isVisible($visibility);
    }

    /** Stores the list of fields which have already been fetched for this Profile
     */
    public function setFetchedFields($fields)
    {
        if (($fields | self::FETCH_ALL) != self::FETCH_ALL) {
            Platal::page()->kill("Invalid fetched fields: $fields");
        }

        $this->fetched_fields = $fields;
    }

    /** Have we already fetched this field ?
     */
    private function fetched($field)
    {
        if (!array_key_exists($field, ProfileField::$fields)) {
            Platal::page()->kill("Invalid field: $field");
        }

        return ($this->fetched_fields & $field);
    }

    /** If not already done, fetches data for the given field
     * @param $field One of the Profile::FETCH_*
     * @return A ProfileField, or null
     */
    private function getProfileField($field)
    {
        if (!array_key_exists($field, ProfileField::$fields)) {
            Platal::page()->kill("Invalid field: $field");
        }
        if ($this->fetched($field)) {
            return null;
        } else {
            $this->fetched_fields = $this->fetched_fields | $field;
        }

        $cls = ProfileField::$fields[$field];

        return ProfileField::getForPID($cls, $this->id(), $this->visibility);
    }

    /** Consolidates internal data (addresses, phones, jobs)
     */
    private function consolidateFields()
    {
        // Link phones to addresses
        if ($this->phones != null) {
            if ($this->addresses != null) {
                $this->addresses->addPhones($this->phones);
            }

            if ($this->jobs != null) {
                $this->jobs->addPhones($this->phones);
            }
        }

        // Link addresses to jobs
        if ($this->addresses != null && $this->jobs != null) {
            $this->jobs->addAddresses($this->addresses);
        }

        // Link jobterms to jobs
        if ($this->jobs != null && $this->jobterms != null) {
            $this->jobs->addJobTerms($this->jobterms);
        }
    }

    /* Photo
     */
    private $photo = null;
    public function getPhoto($fallback = true, $data = false)
    {
        if ($this->has_photo) {
            if ($data && ($this->photo == null || $this->photo->mimeType == null)) {
                $res = XDB::fetchOneAssoc('SELECT  attach, attachmime, x, y, last_update
                                             FROM  profile_photos
                                            WHERE  pid = {?}', $this->pid);
                $this->photo = PlImage::fromData($res['attach'], 'image/' .$res['attachmime'], $res['x'], $res['y'], $res['last_update']);
            } else if ($this->photo == null) {
                $this->photo = PlImage::fromData(null, null, $this->photo_width, $this->photo_height);
            }
            return $this->photo;
        } else if ($fallback) {
            if ($this->mainEducation() == 'X') {
                return PlImage::fromFile(dirname(__FILE__) . '/../htdocs/images/none_x.png', 'image/png');
            }
            return PlImage::fromFile(dirname(__FILE__) . '/../htdocs/images/none_md.png', 'image/png');
        }
        return null;
    }

    /* Addresses
     */
    private $addresses = null;
    public function setAddresses(ProfileAddresses $addr)
    {
        $this->addresses = $addr;
        $this->consolidateFields();
    }

    private function fetchAddresses()
    {
        if ($this->addresses == null  && !$this->fetched(self::FETCH_ADDRESSES)) {
            $addr = $this->getProfileField(self::FETCH_ADDRESSES);
            if ($addr) {
                $this->setAddresses($addr);
                $this->fetchPhones();
            }
        }
    }

    public function getAddresses($flags, $limit = null)
    {
        $this->fetchAddresses();

        if ($this->addresses == null) {
            return array();
        }
        return $this->addresses->get($flags, $limit);
    }

    public function iterAddresses($flags, $limit = null)
    {
        return PlIteratorUtils::fromArray($this->getAddresses($flags, $limit), 1, true);
    }

    public function getMainAddress()
    {
        $main = $this->getAddresses(self::ADDRESS_MAIN);
        $perso = $this->getAddresses(self::ADDRESS_PERSO);

        if (count($main)) {
            return array_pop($main);
        } else if (count($perso)) {
            return array_pop($perso);
        } else {
            return null;
        }
    }

    /* Phones
     */
    private $phones = null;
    public function setPhones(ProfilePhones $phones)
    {
        $this->phones = $phones;
        $this->consolidateFields();
    }

    private function fetchPhones()
    {
        if ($this->phones == null && !$this->fetched(self::FETCH_PHONES)) {
            $phones = $this->getProfileField(self::FETCH_PHONES);
            if (isset($phones)) {
                $this->setPhones($phones);
            }
        }
    }

    public function getPhones($flags, $limit = null)
    {
        $this->fetchPhones();
        if ($this->phones == null) {
            return array();
        }
        return $this->phones->get($flags, $limit);
    }

    /* Educations
     */
    private $educations = null;
    public function setEducations(ProfileEducation $edu)
    {
        $this->educations = $edu;
    }

    public function getEducations($flags, $limit = null)
    {
        if ($this->educations == null && !$this->fetched(self::FETCH_EDU)) {
            $educations = $this->getProfileField(self::FETCH_EDU);
            if ($educations) {
                $this->setEducations($educations);
            }
        }

        if ($this->educations == null) {
            return array();
        }
        return $this->educations->get($flags, $limit);
    }

    public function getExtraEducations($limit = null)
    {
        return $this->getEducations(self::EDUCATION_EXTRA, $limit);
    }

    /* Corps
     */
    private $corps = null;
    public function setCorps(ProfileCorps $corps)
    {
        $this->corps = $corps;
    }

    public function getCorps()
    {
        if ($this->corps == null && !$this->fetched(self::FETCH_CORPS)) {
            $corps = $this->getProfileField(self::FETCH_CORPS);
            if ($corps) {
                $this->setCorps($corps);
            }
        }
        return $this->corps;
    }

    /**
     * Retrieve the name of the corps which has been done.
     *
     * Note: this function first tries getCorps(), and if this field is blank
     * tries to find an education which degree is "Corps".
     *
     * Returns an empty string if nothing has been found.
     */
    public function getCorpsName()
    {
        $corps = $this->getCorps();
        if ($corps && $corps->current) {
            $corpsList = DirEnum::getOptions(DirEnum::CURRENTCORPS);
            return $corpsList[$corps->current];
        }

        foreach ($this->getExtraEducations() as $edu) {
            if (!strcasecmp($edu->degree, 'Corps')) {
                if ($edu->school_short) {
                    return $edu->school_short;
                } elseif ($edu->school) {
                    return $edu->school;
                }
            }
        }
        return '';
    }

    /** Networking
     */
    private $networks = null;
    public function setNetworking(ProfileNetworking $nw)
    {
        $this->networks = $nw;
    }

    public function getNetworking($flags, $limit = null)
    {
        if ($this->networks == null && !$this->fetched(self::FETCH_NETWORKING)) {
            $nw = $this->getProfileField(self::FETCH_NETWORKING);
            if ($nw) {
                $this->setNetworking($nw);
            }
        }
        if ($this->networks == null) {
            return array();
        }
        return $this->networks->get($flags, $limit);
    }

    public function getWebSite()
    {
        $site = $this->getNetworking(self::NETWORKING_WEB, 1);
        if (count($site) != 1) {
            return null;
        }
        $site = array_pop($site);
        return $site;
    }


    /** Jobs
     */
    private $jobs = null;
    public function setJobs(ProfileJobs $jobs)
    {
        $this->jobs = $jobs;
        $this->consolidateFields();
    }

    private function fetchJobs()
    {
        if ($this->jobs == null && !$this->fetched(self::FETCH_JOBS)) {
            $jobs = $this->getProfileField(self::FETCH_JOBS);
            if ($jobs) {
                $this->setJobs($jobs);
                $this->fetchAddresses();
            }
        }
    }

    public function getJobs($flags, $limit = null)
    {
        $this->fetchJobs();

        if ($this->jobs == null) {
            return array();
        }
        return $this->jobs->get($flags, $limit);
    }

    public function getMainJob()
    {
        $job = $this->getJobs(self::JOBS_MAIN, 1);
        if (count($job) != 1) {
            return null;
        }
        return array_pop($job);
    }

    /** JobTerms
     */
    private $jobterms = null;
    public function setJobTerms(ProfileJobTerms $jobterms)
    {
        $this->jobterms = $jobterms;
        $this->consolidateFields();
    }

    private $mentor_countries = null;
    public function setMentoringCountries(ProfileMentoringCountries $countries)
    {
        $this->mentor_countries = $countries;
    }

    public function getMentoringCountries()
    {
        if ($this->mentor_countries == null && !$this->fetched(self::FETCH_MENTOR_COUNTRY)) {
            $countries = $this->getProfileField(self::FETCH_MENTOR_COUNTRY);
            if ($countries) {
                $this->setMentoringCountries($countries);
            }
        }

        if ($this->mentor_countries == null) {
            return array();
        } else {
            return $this->mentor_countries->countries;
        }
    }

    /** List of job terms to specify mentoring */
    private $mentor_terms = null;
    /**
     * set job terms to specify mentoring
     * @param $terms a ProfileMentoringTerms object listing terms only for this profile
     */
    public function setMentoringTerms(ProfileMentoringTerms $terms)
    {
        $this->mentor_terms = $terms;
    }
    /**
     * get all job terms that specify mentoring
     * @return an array of JobTerms objects
     */
    public function getMentoringTerms()
    {
        if ($this->mentor_terms == null && !$this->fetched(self::FETCH_MENTOR_TERMS)) {
            $terms = $this->getProfileField(self::FETCH_MENTOR_TERMS);
            if ($terms) {
                $this->setMentoringTerms($terms);
            }
        }

        if ($this->mentor_terms == null) {
            return array();
        } else {
            return $this->mentor_terms->get();
        }
    }

    /* Skills */
    private $skills = null;
    public function setSkills(ProfileSkills $skills)
    {
        $this->skills = $skills;
    }
    public function getSkills()
    {
        if ($this->skills == null && !$this->fetched(self::FETCH_SKILL)) {
            $skills = $this->getProfileField(self::FETCH_SKILL);
            if ($skills) {
                $this->setSkills($skills);
            }
        }

        if ($this->skills == null) {
            return array();
        } else {
            return $this->skills->skills;
        }
    }

    /* Languades */
    private $languages = null;
    public function setLanguages(ProfileLanguages $languages)
    {
        $this->languages = $languages;
    }
    public function getLanguages()
    {
        if ($this->languages == null && !$this->fetched(self::FETCH_LANGUAGE)) {
            $languages = $this->getProfileField(self::FETCH_LANGUAGE);
            if ($languages) {
                $this->setLanguages($languages);
            }
        }

        if ($this->languages == null) {
            return array();
        } else {
            return $this->languages->languages;
        }
    }

    /** DeltaTen
     */

    /** Find out whether this profile may take part to the "DeltaTen" operation.
     * @param $role Which role to select ('young' or 'old')
     * @return Boolean: whether it is enabled.
     */
    const DELTATEN_YOUNG = 'young';
    const DELTATEN_OLD = 'old';
    public function isDeltaTenEnabled($role)
    {
        global $globals;
        switch ($role) {
        case self::DELTATEN_YOUNG:
            return ($this->mainGrade() == UserFilter::GRADE_ING && $this->yearpromo() >= $globals->deltaten->first_promo_young);
        case self::DELTATEN_OLD:
            // Roughly compute the current promo in second year on the campus:
            // Promo 2010 is in second year between 09/2011 and 08/2012 => use 2012.
            // DeltaTen program begins around January of the second year.
            $promo_on_platal = ((int) date('Y')) - 2;
            return ($this->mainGrade() == UserFilter::GRADE_ING && $this->yearpromo() >= $globals->deltaten->first_promo_young - 10 && $this->yearpromo() <= $promo_on_platal - 10);
        default:
            Platal::assert(false, "Invalid DeltaTen role $role");
        }
    }

    /** Retrieve the "Deltaten" message of the user.
     * Returns "null" if the message is empty or the user is not taking part to the
     * DeltaTen operation.
     */
    public function getDeltatenMessage()
    {
        if ($this->isDeltaTenEnabled(self::DELTATEN_OLD)) {
            return $this->deltaten_message;
        } else {
            return null;
        }
    }

    /* Binets
     */
    public function getBinets()
    {
        if ($this->visibility->isVisible(Visibility::EXPORT_PRIVATE)) {
            return XDB::fetchColumn('SELECT  binet_id
                                       FROM  profile_binets
                                      WHERE  pid = {?}', $this->id());
        } else {
            return array();
        }
    }

    public function getFullBinets()
    {
        if ($this->visibility->isVisible(Visibility::EXPORT_PRIVATE)) {
            return XDB::fetchAllAssoc('SELECT  binet_id, text, url
                                         FROM  profile_binets AS pb
                                    LEFT JOIN  profile_binet_enum AS pbe ON (pbe.id = pb.binet_id)
                                        WHERE  pid = {?}', $this->id());
        } else {
            return array();
        }
    }

    public function getBinetsNames()
    {
        if ($this->visibility->isVisible(Visibility::EXPORT_PRIVATE)) {
            return XDB::fetchColumn('SELECT  text
                                       FROM  profile_binets AS pb
                                  LEFT JOIN  profile_binet_enum AS pbe ON (pbe.id = pb.binet_id)
                                      WHERE  pb.pid = {?}', $this->id());
        } else {
            return array();
        }
    }

    /* Hobbies
     */
    public function getHobbies() {
        if ($this->visibility->isVisible(Visibility::EXPORT_PRIVATE)) {
            return XDB::fetchAllAssoc('type', 'SELECT  type, GROUP_CONCAT(text)
                                         FROM  profile_hobby
                                        WHERE  pid = {?}
                                     GROUP BY  type', $this->id());
        } else {
            return XDB::fetchAllAssoc('type', 'SELECT  type, GROUP_CONCAT(text)
                                         FROM  profile_hobby
                                        WHERE  pub = \'public\' AND pid = {?}
                                      GROUP BY type', $this->id());
        }
    }

    /* Medals
     */
    private $medals = null;
    public function setMedals(ProfileMedals $medals)
    {
        $this->medals = $medals;
    }

    public function getMedals()
    {
        if ($this->medals == null && !$this->fetched(self::FETCH_MEDALS)) {
            $medals = $this->getProfileField(self::FETCH_MEDALS);
            if ($medals) {
                $this->setMedals($medals);
            }
        }
        if ($this->medals == null) {
            return array();
        }
        return $this->medals->medals;
    }

    /** Sharing data with partner websites
     */
    private $partners_settings = null;
    public function setPartnersSettings(ProfilePartnerSharing $partners_settings)
    {
        $this->partners_settings = $partners_settings;
    }

    public function getPartnerSettings($partner_id)
    {
        if ($this->partners_settings === null && !$this->fetched(self::FETCH_PARTNER)) {
            $settings = $this->getProfileField(self::FETCH_PARTNER);
            if ($settings) {
                $this->setPartnersSettings($settings);
            }
        }
        if ($this->partners_settings === null) {
            return PartnerSettings::getEmpty($partner_id);
        }
        return $this->partners_settings->get($partner_id);
    }

    public function compareNames($firstname, $lastname)
    {
        $_lastname  = mb_strtoupper($this->lastName());
        $_firstname = mb_strtoupper($this->firstName());
        $lastname   = mb_strtoupper($lastname);
        $firstname  = mb_strtoupper($firstname);

        $isOk  = (mb_strtoupper($_firstname) == mb_strtoupper($firstname));
        $tokens = preg_split("/[ \-']/", $lastname, -1, PREG_SPLIT_NO_EMPTY);
        $maxlen = 0;

        foreach ($tokens as $str) {
            $isOk &= (strpos($_lastname, $str) !== false);
            $maxlen = max($maxlen, strlen($str));
        }

        return ($isOk && ($maxlen > 2 || $maxlen == strlen($_lastname)));
    }

    /* Export to JSON
     */
    public function export()
    {
        return array(
            'hrpid'        => $this->hrid(),
            'display_name' => $this->shortName(),
            'full_name'    => $this->fullName(),
            'directory_name' => $this->directory_name,
            'promo'        => $this->promo(),
            'year_promo'   => $this->yearpromo(),
            'is_active'    => $this->isActive(),
            'first_name'   => $this->firstName(),
            'last_name'    => $this->lastName(),
            'is_female'    => $this->isFemale(),
        );
    }

    private static function fetchProfileData(array $pids, $respect_order = true, $fields = 0x0000, $visibility = null)
    {
        if (count($pids) == 0) {
            return null;
        }

        if ($respect_order) {
            $order = 'ORDER BY  ' . XDB::formatCustomOrder('p.pid', $pids);
        } else {
            $order = '';
        }

        if ($visibility === null) {
            $visibility = Visibility::defaultForRead();
        }

        $it = XDB::Iterator('SELECT  p.pid, p.hrpid, p.xorg_id, p.ax_id, p.birthdate, p.birthdate_ref,
                                     p.next_birthday, p.deathdate, p.deathdate_rec, p.sex = \'female\' AS sex,
                                     IF ({?}, p.cv, NULL) AS cv, p.medals_pub, p.alias_pub, p.email_directory,
                                     p.last_change, p.nationality1, p.nationality2, p.nationality3,
                                     IF (p.freetext_pub >= {?}, p.freetext, NULL) AS freetext,
                                     pe.entry_year, pe.grad_year, pe.promo_year, pe.program, pe.fieldid,
                                     IF ({?}, pse.text, NULL) AS section,
                                     ppn.firstname_main AS firstname, ppn.lastname_main AS lastname, IF ({?}, pn.name, NULL) AS nickname,
                                     IF (ppn.firstname_ordinary = \'\', ppn.firstname_main, ppn.firstname_ordinary) AS firstname_ordinary,
                                     IF (ppn.lastname_ordinary = \'\', ppn.lastname_main, ppn.lastname_ordinary) AS lastname_ordinary,
                                     pd.yourself, pd.promo, pd.short_name, pd.public_name AS full_name,
                                     pd.directory_name, pd.public_name, pd.private_name, pd.sort_name,
                                     IF (pp.pub >= {?}, pp.display_tel, NULL) AS mobile,
                                     (ph.pub >= {?} AND ph.attach IS NOT NULL) AS has_photo, ph.pub as photo_pub,
                                     ph.x AS photo_width, ph.y AS photo_height,
                                     p.last_change < DATE_SUB(NOW(), INTERVAL 365 DAY) AS is_old,
                                     pm.expertise AS mentor_expertise,
                                     IF ({?}, pdt.message, NULL) AS deltaten_message,
                                     ap.uid AS owner_id
                               FROM  profiles AS p
                         INNER JOIN  profile_display AS pd ON (pd.pid = p.pid)
                         INNER JOIN  profile_education AS pe ON (pe.pid = p.pid AND FIND_IN_SET(\'primary\', pe.flags))
                          LEFT JOIN  profile_section_enum AS pse ON (pse.id = p.section)
                         INNER JOIN  profile_public_names AS ppn ON (ppn.pid = p.pid)
                          LEFT JOIN  profile_private_names AS pn ON (pn.pid = p.pid AND type = \'nickname\')
                          LEFT JOIN  profile_phones AS pp ON (pp.pid = p.pid AND pp.link_type = \'user\' AND tel_type = \'mobile\')
                          LEFT JOIN  profile_photos AS ph ON (ph.pid = p.pid)
                          LEFT JOIN  profile_mentor AS pm ON (pm.pid = p.pid)
                          LEFT JOIN  profile_deltaten AS pdt ON (pdt.pid = p.pid)
                          LEFT JOIN  account_profiles AS ap ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', ap.perms))
                              WHERE  p.pid IN {?}
                           GROUP BY  p.pid
                                     ' . $order,
                           $visibility->isVisible(Visibility::EXPORT_PRIVATE), // CV
                           $visibility->level(), // freetext
                           $visibility->isVisible(Visibility::EXPORT_PRIVATE), // section
                           $visibility->isVisible(Visibility::EXPORT_PRIVATE), // nickname
                           $visibility->level(), // mobile
                           $visibility->level(), // photo
                           $visibility->isVisible(Visibility::EXPORT_PRIVATE), // deltaten_message
                           $pids
                       );
        return new ProfileIterator($it, $pids, $fields, $visibility);
    }

    public static function getPID($login)
    {
        if ($login instanceof PlUser) {
            return XDB::fetchOneCell('SELECT  pid
                                        FROM  account_profiles
                                       WHERE  uid = {?} AND FIND_IN_SET(\'owner\', perms)',
                                     $login->id());
        } else if (ctype_digit($login)) {
            return XDB::fetchOneCell('SELECT  pid
                                        FROM  profiles
                                       WHERE  pid = {?}', $login);
        } else {
            return XDB::fetchOneCell('SELECT  pid
                                        FROM  profiles
                                       WHERE  hrpid = {?}', $login);
        }
    }

    public static function getPIDsFromUIDs($uids, $respect_order = true)
    {
        if ($respect_order) {
            $order = 'ORDER BY ' . XDB::formatCustomOrder('uid', $uids);
        } else {
            $order = '';
        }
        return XDB::fetchAllAssoc('uid', 'SELECT  ap.uid, ap.pid
                                            FROM  account_profiles AS ap
                                           WHERE  FIND_IN_SET(\'owner\', ap.perms)
                                                  AND ap.uid IN ' . XDB::formatArray($uids) .'
                                               ' . $order);
    }

    /** Return the profile associated with the given login.
     */
    public static function get($login, $fields = 0x0000, $visibility = null)
    {
        if ($visibility === null) {
            $visibility = Visibility::defaultForRead();
        }

        if (is_array($login)) {
            $pf = new Profile($login, $visibility);
            return $pf;
        }
        $pid = self::getPID($login);
        if (!is_null($pid)) {
            $it = self::iterOverPIDs(array($pid), false, $fields, $visibility);
            return $it->next();
        } else {
            /* Let say we can identify a profile using the identifiers of its owner.
             */
            if (!($login instanceof PlUser)) {
                $user = User::getSilent($login);
                if ($user && $user->hasProfile()) {
                    return $user->profile(false, $fields, $visibility);
                }
            }
            return null;
        }
    }

    public static function iterOverUIDs($uids, $respect_order = true, $fields = 0x0000, $visibility = null)
    {
        return self::iterOverPIDs(self::getPIDsFromUIDs($uids), $respect_order, $fields, $visibility);
    }

    public static function iterOverPIDs($pids, $respect_order = true, $fields = 0x0000, $visibility = null)
    {
        return self::fetchProfileData($pids, $respect_order, $fields, $visibility);
    }

    /** Return profiles for the list of pids.
     */
    public static function getBulkProfilesWithPIDs(array $pids, $fields = 0x0000, $visibility = null)
    {
        if (count($pids) == 0) {
            return array();
        }
        $it = self::iterOverPIDs($pids, true, $fields, $visibility);
        $profiles = array();
        while ($p = $it->next()) {
            $profiles[$p->id()] = $p;
        }
        return $profiles;
    }

    /** Return profiles for uids.
     */
    public static function getBulkProfilesWithUIDS(array $uids, $fields = 0x000, $visibility = null)
    {
        if (count($uids) == 0) {
            return array();
        }
        return self::getBulkProfilesWithPIDs(self::getPIDsFromUIDs($uids), $fields, $visibility);
    }

    public static function isDisplayName($name)
    {
        return $name == self::DN_FULL || $name == self::DN_DISPLAY
            || $name == self::DN_YOURSELF || $name == self::DN_DIRECTORY
            || $name == self::DN_PRIVATE || $name == self::DN_PUBLIC
            || $name == self::DN_SHORT || $name == self::DN_SORT;
    }

    /** Returns the closest "accounts only" name type for $name
     */
    public static function getAccountEquivalentName($name)
    {
        switch ($name) {
          case self::DN_DIRECTORY:
            return 'directory_name';
          case self::DN_SORT:
            return 'sort_name';
          case self::DN_FULL:
          case self::DN_PUBLIC:
          case self::DN_PRIVATE:
          case self::DN_SHORT:
            return 'full_name';
          case self::DN_YOURSELF:
            return 'display_name';
          default:
            return 'display_name';
        }
    }

    public static function rebuildSearchTokens($pids, $transaction = true)
    {
        require_once 'name.func.inc.php';
        if (!is_array($pids)) {
            $pids = array($pids);
        }
        $keys = XDB::iterator("(SELECT  pid, name, type, IF(type = 'nickname', 2, 1) AS score, '' AS public
                                  FROM  profile_private_names
                                 WHERE  pid IN {?})
                                UNION
                               (SELECT  pid, lastname_main, 'lastname' AS type, 10 AS score, 'public' AS public
                                  FROM  profile_public_names
                                 WHERE  lastname_main != '' AND pid IN {?})
                                UNION
                               (SELECT  pid, lastname_marital, 'lastname' AS type, 10 AS score, 'public' AS public
                                  FROM  profile_public_names
                                 WHERE  lastname_marital != '' AND pid IN {?})
                                UNION
                               (SELECT  pid, lastname_ordinary, 'lastname' AS type, 10 AS score, 'public' AS public
                                  FROM  profile_public_names
                                 WHERE  lastname_ordinary != '' AND pid IN {?})
                                UNION
                               (SELECT  pid, firstname_main, 'firstname' AS type, 10 AS score, 'public' AS public
                                  FROM  profile_public_names
                                 WHERE  firstname_main != '' AND pid IN {?})
                                UNION
                               (SELECT  pid, firstname_ordinary, 'firstname' AS type, 10 AS score, 'public' AS public
                                  FROM  profile_public_names
                                 WHERE  firstname_ordinary != '' AND pid IN {?})
                                UNION
                               (SELECT  pid, pseudonym, 'nickname' AS type, 10 AS score, 'public' AS public
                                  FROM  profile_public_names
                                 WHERE  pseudonym != '' AND pid IN {?})",
                              $pids, $pids, $pids, $pids, $pids, $pids, $pids);
        $names = array();
        while ($key = $keys->next()) {
            if ($key['name'] == '') {
                continue;
            }
            $pid  = $key['pid'];
            $toks = split_name_for_search($key['name']);
            $toks = array_reverse($toks);

            /* Split the score between the tokens to avoid the user to be over-rated.
             * Let says my user name is "Machin-Truc Bidule" and I also have a user named
             * 'Machin Truc'. Distributing the score force "Machin Truc" to be displayed
             * before "Machin-Truc" for both "Machin Truc" and "Machin" searches.
             */
            $eltScore = ceil(((float)$key['score'])/((float)count($toks)));
            $token = '';
            foreach ($toks as $tok) {
                $token = $tok . $token;
                $names["$pid-$token"] = XDB::format('({?}, {?}, {?}, {?}, {?}, {?})',
                                                    $token, $pid, soundex_fr($token),
                                                    $eltScore, $key['public'], $key['type']);
            }
        }
        if ($transaction) {
            XDB::startTransaction();
        }
        XDB::execute('DELETE FROM  search_name
                            WHERE  pid IN {?}',
                     $pids);
        if (count($names) > 0) {
            XDB::rawExecute('INSERT INTO  search_name (token, pid, soundex, score, flags, general_type)
                                  VALUES  ' . implode(', ', $names));
        }
        if ($transaction) {
            XDB::commit();
        }
    }

    /** The school identifier consists of 6 digits. The first 3 represent the
     * promotion entry year. The last 3 indicate the student's rank.
     *
     * Our identifier consists of 8 digits and both half have the same role.
     * This enables us to deal with bigger promotions and with a wider range
     * of promotions.
     *
     * getSchoolId returns a school identifier given one of ours.
     * getXorgId returns a X.org identifier given a school identifier.
     */
    public static function getSchoolId($xorgId)
    {
        if (!preg_match('/^[0-9]{8}$/', $xorgId)) {
            return null;
        }

        $year = intval(substr($xorgId, 0, 4));
        $rank = intval(substr($xorgId, 5, 3));
        if ($year < 1996) {
            return null;
        } elseif ($year < 2000) {
            $year = intval(substr(1900 - $year, 1, 3));
            return sprintf('%02u0%03u', $year, $rank);
        } else {
            $year = intval(substr(1900 - $year, 1, 3));
            return sprintf('%03u%03u', $year, $rank);
        }
    }

    public static function getXorgId($schoolId)
    {
        if (!preg_match('/^[0-9]{6}$/', $schoolId)) {
            return null;
        }

        $year = intval(substr($schoolId, 0, 3));
        $rank = intval(substr($schoolId, 3, 3));

        if ($year > 200) {
            $year /= 10;
        }
        if ($year < 96) {
            return null;
        } else {
            return sprintf('%04u%04u', 1900 + $year, $rank);
        }
    }

    public static function field_display($field_name)
    {
        if (array_key_exists($field_name, Profile::$descriptions)) {
            return Profile::$descriptions[$field_name];
        } else {
            // Return the raw field_name, as legacy updates in DB used to store the field_display
            // instead of its name.
            return $field_name;
        }
    }
}


/** Iterator over a set of Profiles
 */
class ProfileIterator implements PlIterator
{
    private $iterator = null;
    private $fields;
    private $visibility;

    const FETCH_ALL    = 0x000033F; // FETCH_ADDRESSES | FETCH_CORPS | FETCH_EDU | FETCH_JOBS | FETCH_MEDALS | FETCH_NETWORKING | FETCH_PHONES | FETCH_JOB_TERMS

    public function __construct(PlIterator $it, array $pids, $fields = 0x0000, $visibility = null)
    {
        require_once 'profilefields.inc.php';

        if ($visibility === null) {
            $visibility = Visibility::defaultForRead();
        }

        $this->fields = $fields;
        $this->visibility = $visibility;

        $subits = array();
        $callbacks = array();

        $subits[0] = $it;
        $callbacks[0] = PlIteratorUtils::arrayValueCallback('pid');
        $cb = PlIteratorUtils::objectPropertyCallback('pid');

        $fields = $fields & self::FETCH_ALL;
        for ($field = 1; $field < $fields; $field *= 2) {
            if (($fields & $field) ) {
                $callbacks[$field] = $cb;
                $subits[$field] = new ProfileFieldIterator($field, $pids, $visibility);
            }
        }

        $this->iterator = PlIteratorUtils::parallelIterator($subits, $callbacks, 0);
    }

    private function hasData($field, $vals)
    {
        return ($this->fields & $field) && ($vals[$field] != null);
    }

    private function fillProfile(array $vals)
    {
        $pf = Profile::get($vals[0], 0x0, $this->visibility);
        $pf->setFetchedFields($this->fields);

        if ($this->hasData(Profile::FETCH_PHONES, $vals)) {
            $pf->setPhones($vals[Profile::FETCH_PHONES]);
        }
        if ($this->hasData(Profile::FETCH_ADDRESSES, $vals)) {
            $pf->setAddresses($vals[Profile::FETCH_ADDRESSES]);
        }
        if ($this->hasData(Profile::FETCH_JOBS, $vals)) {
            $pf->setJobs($vals[Profile::FETCH_JOBS]);
        }
        if ($this->hasData(Profile::FETCH_JOB_TERMS, $vals)) {
            $pf->setJobTerms($vals[Profile::FETCH_JOB_TERMS]);
        }

        if ($this->hasData(Profile::FETCH_CORPS, $vals)) {
            $pf->setCorps($vals[Profile::FETCH_CORPS]);
        }
        if ($this->hasData(Profile::FETCH_EDU, $vals)) {
            $pf->setEducations($vals[Profile::FETCH_EDU]);
        }
        if ($this->hasData(Profile::FETCH_MEDALS, $vals)) {
            $pf->setMedals($vals[Profile::FETCH_MEDALS]);
        }
        if ($this->hasData(Profile::FETCH_NETWORKING, $vals)) {
            $pf->setNetworking($vals[Profile::FETCH_NETWORKING]);
        }

        return $pf;
    }

    public function next()
    {
        $vals = $this->iterator->next();
        if ($vals == null) {
            return null;
        }
        return $this->fillProfile($vals);
    }

    public function first()
    {
        return $this->iterator->first();
    }

    public function last()
    {
        return $this->iterator->last();
    }

    public function total()
    {
        return $this->iterator->total();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
