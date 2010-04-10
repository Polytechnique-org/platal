<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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


/******************
 * CONDITIONS
 ******************/

// {{{ interface UserFilterCondition
/** This interface describe objects which filter users based
 *      on various parameters.
 * The parameters of the filter must be given to the constructor.
 * The buildCondition function is called by UserFilter when
 *     actually building the query. That function must call
 *     $uf->addWheteverFilter so that the UserFilter makes
 *     adequate joins. It must return the 'WHERE' condition to use
 *     with the filter.
 */
interface UserFilterCondition extends PlFilterCondition
{
}
// }}}

// {{{ class UFC_HasProfile
/** Filters users who have a profile
 */
class UFC_HasProfile implements UserFilterCondition
{
    public function buildCondition(PlFilter &$uf)
    {
        return '$PID IS NOT NULL';
    }
}
// }}}

// {{{ class UFC_Hruid
/** Filters users based on their hruid
 * @param $val Either an hruid, or a list of those
 */
class UFC_Hruid implements UserFilterCondition
{
    private $hruids;

    public function __construct($val)
    {
        if (!is_array($val)) {
            $val = array($val);
        }
        $this->hruids = $val;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireAccounts();
        return XDB::format('a.hruid IN {?}', $this->hruids);
    }
}
// }}}

// {{{ class UFC_Hrpid
/** Filters users based on the hrpid of their profiles
 * @param $val Either an hrpid, or a list of those
 */
class UFC_Hrpid implements UserFilterCondition
{
    private $hrpids;

    public function __construct($val)
    {
        if (!is_array($val)) {
            $val = array($val);
        }
        $this->hrpids = $val;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        return XDB::format('p.hrpid IN {?}', $this->hrpids);
    }
}
// }}}

// {{{ class UFC_Ip
/** Filters users based on one of their last IPs
 * @param $ip IP from which connection are checked
 */
class UFC_Ip implements UserFilterCondition
{
    private $ip;

    public function __construct($ip)
    {
        $this->ip = $ip;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addLoggerFilter();
        $ip = ip_to_uint($this->ip);
        return XDB::format($sub . '.ip = {?} OR ' . $sub . '.forward_ip = {?}', $ip, $ip);
    }
}
// }}}

// {{{ class UFC_Comment
class UFC_Comment implements UserFilterCondition
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        return 'p.freetext ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->text);
    }
}
// }}}

// {{{ class UFC_Promo
/** Filters users based on promotion
 * @param $comparison Comparison operator (>, =, ...)
 * @param $grade Formation on which to restrict, UserFilter::DISPLAY for "any formation"
 * @param $promo Promotion on which the filter is based
 */
class UFC_Promo implements UserFilterCondition
{

    private $grade;
    private $promo;
    private $comparison;

    public function __construct($comparison, $grade, $promo)
    {
        $this->grade = $grade;
        $this->comparison = $comparison;
        $this->promo = $promo;
        if ($this->grade != UserFilter::DISPLAY) {
            UserFilter::assertGrade($this->grade);
        }
    }

    public function buildCondition(PlFilter &$uf)
    {
        if ($this->grade == UserFilter::DISPLAY) {
            $sub = $uf->addDisplayFilter();
            return XDB::format('pd' . $sub . '.promo ' . $this->comparison . ' {?}', $this->promo);
        } else {
            $sub = $uf->addEducationFilter(true, $this->grade);
            $field = 'pe' . $sub . '.' . UserFilter::promoYear($this->grade);
            return $field . ' IS NOT NULL AND ' . $field . ' ' . $this->comparison . ' ' . XDB::format('{?}', $this->promo);
        }
    }
}
// }}}

// {{{ class UFC_SchoolId
/** Filters users based on their shoold identifier
 * @param type Parameter type (Xorg, AX, School)
 * @param value School id value
 */
class UFC_SchooldId implements UserFilterCondition
{
    const AX     = 'ax';
    const Xorg   = 'xorg';
    const School = 'school';

    private $type;
    private $id;

    static public function assertType($type)
    {
        if ($type != self::AX && $type != self::Xorg && $type != self::School) {
            Platal::page()->killError("Type de matricule invalide: $type");
        }
    }

    public function __construct($type, $id)
    {
        $this->type = $type;
        $this->id   = $id;
        self::assertType($type);
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        $id = $this->id;
        $type = $this->type;
        if ($type == self::School) {
            $type = self::Xorg;
            $id   = Profile::getXorgId($id);
        }
        return XDB::format('p.' . $type . '_id = {?}', $id);
    }
}
// }}}

// {{{ class UFC_EducationSchool
/** Filters users by formation
 * @param $val The formation to search (either ID or array of IDs)
 */
class UFC_EducationSchool implements UserFilterCondition
{
    private $val;

    public function __construct($val)
    {
        if (!is_array($val)) {
            $val = array($val);
        }
        $this->val = $val;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addEducationFilter();
        return XDB::format('pe' . $sub . '.eduid IN {?}', $this->val);
    }
}
// }}}

// {{{ class UFC_EducationDegree
class UFC_EducationDegree implements UserFilterCondition
{
    private $diploma;

    public function __construct($diploma)
    {
        if (! is_array($diploma)) {
            $diploma = array($diploma);
        }
        $this->diploma = $diploma;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addEducationFilter();
        return XDB::format('pee' . $sub . '.degreeid IN {?}', $this->val);
    }
}
// }}}

// {{{ class UFC_EducationField
class UFC_EducationField implements UserFilterCondition
{
    private $val;

    public function __construct($val)
    {
        if (!is_array($val)) {
            $val = array($val);
        }
        $this->val = $val;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addEducationFilter();
        return XDB::format('pee' . $sub . '.fieldid IN {?}', $this->val);
    }
}
// }}}

// {{{ class UFC_Name
/** Filters users based on name
 * @param $type Type of name field on which filtering is done (firstname, lastname...)
 * @param $text Text on which to filter
 * @param $mode Flag indicating search type (prefix, suffix, with particule...)
 */
class UFC_Name implements UserFilterCondition
{
    const PREFIX   = XDB::WILDCARD_PREFIX; // 0x001
    const SUFFIX   = XDB::WILDCARD_SUFFIX; // 0x002
    const CONTAINS = XDB::WILDCARD_CONTAINS; // 0x003
    const PARTICLE = 0x007; // self::CONTAINS | 0x004
    const VARIANTS = 0x008;

    private $type;
    private $text;
    private $mode;

    public function __construct($type, $text, $mode)
    {
        $this->type = $type;
        $this->text = $text;
        $this->mode = $mode;
    }

    private function buildNameQuery($type, $variant, $where, UserFilter &$uf)
    {
        $sub = $uf->addNameFilter($type, $variant);
        return str_replace('$ME', 'pn' . $sub, $where);
    }

    public function buildCondition(PlFilter &$uf)
    {
        $left = '$ME.name';
        if (($this->mode & self::PARTICLE) == self::PARTICLE) {
            $left = 'CONCAT($ME.particle, \' \', $ME.name)';
        }
        $right = XDB::formatWildcards($this->mode & self::CONTAINS, $this->text);

        $cond = $left . $right;
        $conds = array($this->buildNameQuery($this->type, null, $cond, $uf));
        if (($this->mode & self::VARIANTS) != 0 && isset(Profile::$name_variants[$this->type])) {
            foreach (Profile::$name_variants[$this->type] as $var) {
                $conds[] = $this->buildNameQuery($this->type, $var, $cond, $uf);
            }
        }
        return implode(' OR ', $conds);
    }
}
// }}}

// {{{ class UFC_NameTokens
/** Selects users based on tokens in their name (for quicksearch)
 * @param $tokens An array of tokens to search
 * @param $flags Flags the tokens must have (e.g 'public' for public search)
 * @param $soundex (bool) Whether those tokens are fulltext or soundex
 */
class UFC_NameTokens implements UserFilterCondition
{
    /* Flags */
    const FLAG_PUBLIC = 'public';

    private $tokens;
    private $flags;
    private $soundex;
    private $exact;

    public function __construct($tokens, $flags = array(), $soundex = false, $exact = false)
    {
        $this->tokens = $tokens;
        if (is_array($flags)) {
            $this->flags = $flags;
        } else {
            $this->flags = array($flags);
        }
        $this->soundex = $soundex;
        $this->exact = $exact;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addNameTokensFilter(!($this->exact || $this->soundex));
        $conds = array();
        if ($this->soundex) {
            $conds[] = XDB::format($sub . '.soundex IN {?}', $this->tokens);
        } else if ($this->exact) {
            $conds[] = XDB::format($sub . '.token IN {?}', $this->tokens);
        } else {
            $tokconds = array();
            foreach ($this->tokens as $token) {
                $tokconds[] = $sub . '.token ' . XDB::formatWildcards(XDB::WILDCARD_PREFIX, $token);
            }
            $conds[] = implode(' OR ', $tokconds);
        }

        if ($this->flags != null) {
            $conds[] = XDB::format($sub . '.flags IN {?}', $this->flags);
        }

        return implode(' AND ', $conds);
    }
}
// }}}

// {{{ class UFC_Nationality
class UFC_Nationality implements UserFilterCondition
{
    private $val;

    public function __construct($val)
    {
        if (!is_array($val)) {
            $val = array($val);
        }
        $this->val = $val;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        $nat = XDB::formatArray($this->val);
        $conds = array(
            'p.nationality1 IN ' . $nat,
            'p.nationality2 IN ' . $nat,
            'p.nationality3 IN ' . $nat,
        );
        return implode(' OR ', $conds);
    }
}
// }}}

// {{{ class UFC_Dead
/** Filters users based on death date
 * @param $comparison Comparison operator
 * @param $date Date to which death date should be compared
 */
class UFC_Dead implements UserFilterCondition
{
    private $comparison;
    private $date;

    public function __construct($comparison = null, $date = null)
    {
        $this->comparison = $comparison;
        $this->date = $date;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        $str = 'p.deathdate IS NOT NULL';
        if (!is_null($this->comparison)) {
            $str .= ' AND p.deathdate ' . $this->comparison . ' ' . XDB::format('{?}', date('Y-m-d', $this->date));
        }
        return $str;
    }
}
// }}}

// {{{ class UFC_Registered
/** Filters users based on registration state
 * @param $active Whether we want to use only "active" users (i.e with a valid redirection)
 * @param $comparison Comparison operator
 * @param $date Date to which users registration date should be compared
 */
class UFC_Registered implements UserFilterCondition
{
    private $active;
    private $comparison;
    private $date;

    public function __construct($active = false, $comparison = null, $date = null)
    {
        $this->active = $active;
        $this->comparison = $comparison;
        $this->date = $date;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireAccounts();
        if ($this->active) {
            $date = 'a.uid IS NOT NULL AND a.state = \'active\'';
        } else {
            $date = 'a.uid IS NOT NULL AND a.state != \'pending\'';
        }
        if (!is_null($this->comparison)) {
            $date .= ' AND a.registration_date ' . $this->comparison . ' ' . XDB::format('{?}', date('Y-m-d', $this->date));
        }
        return $date;
    }
}
// }}}

// {{{ class UFC_ProfileUpdated
/** Filters users based on profile update date
 * @param $comparison Comparison operator
 * @param $date Date to which profile update date must be compared
 */
class UFC_ProfileUpdated implements UserFilterCondition
{
    private $comparison;
    private $date;

    public function __construct($comparison = null, $date = null)
    {
        $this->comparison = $comparison;
        $this->date = $date;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        return 'p.last_change ' . $this->comparison . XDB::format(' {?}', date('Y-m-d H:i:s', $this->date));
    }
}
// }}}

// {{{ class UFC_Birthday
/** Filters users based on next birthday date
 * @param $comparison Comparison operator
 * @param $date Date to which users next birthday date should be compared
 */
class UFC_Birthday implements UserFilterCondition
{
    private $comparison;
    private $date;

    public function __construct($comparison = null, $date = null)
    {
        $this->comparison = $comparison;
        $this->date = $date;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        return 'p.next_birthday ' . $this->comparison . XDB::format(' {?}', date('Y-m-d', $this->date));
    }
}
// }}}

// {{{ class UFC_Sex
/** Filters users based on sex
 * @parm $sex One of User::GENDER_MALE or User::GENDER_FEMALE, for selecting users
 */
class UFC_Sex implements UserFilterCondition
{
    private $sex;
    public function __construct($sex)
    {
        $this->sex = $sex;
    }

    public function buildCondition(PlFilter &$uf)
    {
        if ($this->sex != User::GENDER_MALE && $this->sex != User::GENDER_FEMALE) {
            return self::COND_FALSE;
        } else {
            $uf->requireProfiles();
            return XDB::format('p.sex = {?}', $this->sex == User::GENDER_FEMALE ? 'female' : 'male');
        }
    }
}
// }}}

// {{{ class UFC_Group
/** Filters users based on group membership
 * @param $group Group whose members we are selecting
 * @param $anim Whether to restrict selection to animators of that group
 */
class UFC_Group implements UserFilterCondition
{
    private $group;
    private $anim;
    public function __construct($group, $anim = false)
    {
        $this->group = $group;
        $this->anim = $anim;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addGroupFilter($this->group);
        $where = 'gpm' . $sub . '.perms IS NOT NULL';
        if ($this->anim) {
            $where .= ' AND gpm' . $sub . '.perms = \'admin\'';
        }
        return $where;
    }
}
// }}}

// {{{ class UFC_Binet
/** Selects users based on their belonging to a given (list of) binet
 * @param $binet either a binet_id or an array of binet_ids
 */
class UFC_Binet implements UserFilterCondition
{
    private $val;

    public function __construct($val)
    {
        if (!is_array($val)) {
            $val = array($val);
        }
        $this->val = $val;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addBinetsFilter();
        return XDB::format($sub . '.binet_id IN {?}', $this->val);
    }
}
// }}}

// {{{ class UFC_Section
/** Selects users based on section
 * @param $section ID of the section
 */
class UFC_Section implements UserFilterCondition
{
    private $section;

    public function __construct($section)
    {
        $this->section = $section;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $uf->requireProfiles();
        return 'p.section = ' . XDB::format('{?}', $this->section);
    }
}
// }}}

// {{{ class UFC_Email
/** Filters users based on an email or a list of emails
 * @param $emails List of emails whose owner must be selected
 */
class UFC_Email implements UserFilterCondition
{
    private $emails;
    public function __construct()
    {
        $this->emails = func_get_args();
    }

    public function buildCondition(PlFilter &$uf)
    {
        $foreign = array();
        $virtual = array();
        $aliases = array();
        $cond = array();

        if (count($this->emails) == 0) {
            return PlFilterCondition::COND_TRUE;
        }

        foreach ($this->emails as $entry) {
            if (User::isForeignEmailAddress($entry)) {
                $foreign[] = $entry;
            } else if (User::isVirtualEmailAddress($entry)) {
                $virtual[] = $entry;
            } else {
                @list($user, $domain) = explode('@', $entry);
                $aliases[] = $user;
            }
        }

        if (count($foreign) > 0) {
            $sub = $uf->addEmailRedirectFilter($foreign);
            $cond[] = XDB::format('e' . $sub . '.email IS NOT NULL OR a.email IN {?}', $foreign);
        }
        if (count($virtual) > 0) {
            $sub = $uf->addVirtualEmailFilter($virtual);
            $cond[] = 'vr' . $sub . '.redirect IS NOT NULL';
        }
        if (count($aliases) > 0) {
            $sub = $uf->addAliasFilter($aliases);
            $cond[] = 'al' . $sub . '.alias IS NOT NULL';
        }
        return '(' . implode(') OR (', $cond) . ')';
    }
}
// }}}

// {{{ class UFC_Address
abstract class UFC_Address implements UserFilterCondition
{
    /** Valid address type ('hq' is reserved for company addresses)
     */
    const TYPE_HOME = 1;
    const TYPE_PRO  = 2;
    const TYPE_ANY  = 3;

    /** Text for these types
     */
    protected static $typetexts = array(
        self::TYPE_HOME => 'home',
        self::TYPE_PRO  => 'pro',
    );

    protected $type;

    /** Flags for addresses
     */
    const FLAG_CURRENT = 0x0001;
    const FLAG_TEMP    = 0x0002;
    const FLAG_SECOND  = 0x0004;
    const FLAG_MAIL    = 0x0008;
    const FLAG_CEDEX   = 0x0010;

    // Binary OR of those flags
    const FLAG_ANY     = 0x001F;

    /** Text of these flags
     */
    protected static $flagtexts = array(
        self::FLAG_CURRENT => 'current',
        self::FLAG_TEMP    => 'temporary',
        self::FLAG_SECOND  => 'secondary',
        self::FLAG_MAIL    => 'mail',
        self::FLAG_CEDEX   => 'cedex',
    );

    protected $flags;

    public function __construct($type = null, $flags = null)
    {
        $this->type  = $type;
        $this->flags = $flags;
    }

    protected function initConds($sub)
    {
        $conds = array();
        $types = array();
        foreach (self::$typetexts as $flag => $type) {
            if ($flag & $this->type) {
                $types[] = $type;
            }
        }
        if (count($types)) {
            $conds[] = XDB::foramt($sub . '.type IN {?}', $types);
        }

        if ($this->flags != self::FLAG_ANY) {
            foreach(self::$flagtexts as $flag => $text) {
                if ($flag & $this->flags) {
                    $conds[] = 'FIND_IN_SET(' . XDB::format('{?}', $text) . ', ' . $sub . '.flags)';
                }
            }
        }
        return $conds;
    }

}
// }}}

// {{{ class UFC_AddressText
/** Select users based on their address, using full text search
 * @param $text Text for filter in fulltext search
 * @param $textSearchMode Mode for search (one of XDB::WILDCARD_*)
 * @param $type Filter on address type
 * @param $flags Filter on address flags
 * @param $country Filter on address country
 * @param $locality Filter on address locality
 */
class UFC_AddressText extends UFC_Address
{

    private $text;
    private $textSearchMode;

    public function __construct($text = null, $textSearchMode = XDB::WILDCARD_CONTAINS,
        $type = null, $flags = self::FLAG_ANY, $country = null, $locality = null)
    {
        parent::__construct($type, $flags);
        $this->text           = $text;
        $this->textSearchMode = $textSearchMode;
        $this->country        = $country;
        $this->locality       = $locality;
    }

    private function mkMatch($txt)
    {
        return XDB::formatWildcards($this->textSearchMode, $txt);
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addAddressFilter();
        $conds = $this->initConds($sub);
        if ($this->text != null) {
            $conds[] = $sub . '.text' . $this->mkMatch($this->text);
        }

        if ($this->country != null) {
            $subc = $uf->addAddressCountryFilter();
            $subconds = array();
            $subconds[] = $subc . '.country' . $this->mkMatch($this->country);
            $subconds[] = $subc . '.countryFR' . $this->mkMatch($this->country);
            $conds[] = implode(' OR ', $subconds);
        }

        if ($this->locality != null) {
            $subl = $uf->addAddressLocalityFilter();
            $conds[] = $subl . '.name' . $this->mkMatch($this->locality);
        }

        return implode(' AND ', $conds);
    }
}
// }}}

// {{{ class UFC_AddressField
/** Filters users based on their address,
 * @param $val Either a code for one of the fields, or an array of such codes
 * @param $fieldtype The type of field to look for
 * @param $type Filter on address type
 * @param $flags Filter on address flags
 */
class UFC_AddressField extends UFC_Address
{
    const FIELD_COUNTRY    = 1;
    const FIELD_ADMAREA    = 2;
    const FIELD_SUBADMAREA = 3;
    const FIELD_LOCALITY   = 4;
    const FIELD_ZIPCODE    = 5;

    /** Data of the filter
     */
    private $val;
    private $fieldtype;

    public function __construct($val, $fieldtype, $type = null, $flags = self::FLAG_ANY)
    {
        parent::__construct($type, $flags);

        if (!is_array($val)) {
            $val = array($val);
        }
        $this->val       = $val;
        $this->fieldtype = $fieldtype;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addAddressFilter();
        $conds = $this->initConds($sub);

        switch ($this->fieldtype) {
        case self::FIELD_COUNTRY:
            $field = 'countryId';
            break;
        case self::FIELD_ADMAREA:
            $field = 'administrativeAreaId';
            break;
        case self::FIELD_SUBADMAREA:
            $field = 'subAdministrativeAreaId';
            break;
        case self::FIELD_LOCALITY:
            $field = 'localityId';
            break;
        case self::FIELD_ZIPCODE:
            $field = 'postalCode';
            break;
        default:
            Platal::page()->killError('Invalid address field type: ' . $this->fieldtype);
        }
        $conds[] = XDB::format($sub . '.' . $field . ' IN {?}', $this->val);

        return implode(' AND ', $conds);
    }
}
// }}}

// {{{ class UFC_Corps
/** Filters users based on the corps they belong to
 * @param $corps Corps we are looking for (abbreviation)
 * @param $type Whether we search for original or current corps
 */
class UFC_Corps implements UserFilterCondition
{
    const CURRENT   = 1;
    const ORIGIN    = 2;

    private $corps;
    private $type;

    public function __construct($corps, $type = self::CURRENT)
    {
        $this->corps = $corps;
        $this->type  = $type;
    }

    public function buildCondition(PlFilter &$uf)
    {
        /** Tables shortcuts:
         * pc for profile_corps,
         * pceo for profile_corps_enum - orginal
         * pcec for profile_corps_enum - current
         */
        $sub = $uf->addCorpsFilter($this->type);
        $cond = $sub . '.abbreviation = ' . $corps;
        return $cond;
    }
}
// }}}

// {{{ class UFC_Corps_Rank
/** Filters users based on their rank in the corps
 * @param $rank Rank we are looking for (abbreviation)
 */
class UFC_Corps_Rank implements UserFilterCondition
{
    private $rank;
    public function __construct($rank)
    {
        $this->rank = $rank;
    }

    public function buildCondition(PlFilter &$uf)
    {
        /** Tables shortcuts:
         * pcr for profile_corps_rank
         */
        $sub = $uf->addCorpsRankFilter();
        $cond = $sub . '.abbreviation = ' . $rank;
        return $cond;
    }
}
// }}}

// {{{ class UFC_Job_Company
/** Filters users based on the company they belong to
 * @param $type The field being searched (self::JOBID, self::JOBNAME or self::JOBACRONYM)
 * @param $value The searched value
 */
class UFC_Job_Company implements UserFilterCondition
{
    const JOBID = 'id';
    const JOBNAME = 'name';
    const JOBACRONYM = 'acronym';

    private $type;
    private $value;

    public function __construct($type, $value)
    {
        $this->assertType($type);
        $this->type = $type;
        $this->value = $value;
    }

    private function assertType($type)
    {
        if ($type != self::JOBID && $type != self::JOBNAME && $type != self::JOBACRONYM) {
            Platal::page()->killError("Type de recherche non valide.");
        }
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addJobCompanyFilter();
        $cond  = $sub . '.' . $this->type . ' = ' . XDB::format('{?}', $this->value);
        return $cond;
    }
}
// }}}

// {{{ class UFC_Job_Sectorization
/** Filters users based on the ((sub)sub)sector they work in
 * @param $val The ID of the sector, or an array of such IDs
 * @param $type The kind of search (subsubsector/subsector/sector)
 */
class UFC_Job_Sectorization implements UserFilterCondition
{
    private $val;
    private $type;

    public function __construct($val, $type = UserFilter::JOB_SECTOR)
    {
        self::assertType($type);
        if (!is_array($val)) {
            $val = array($val);
        }
        $this->val = $val;
        $this->type = $type;
    }

    private static function assertType($type)
    {
        if ($type != UserFilter::JOB_SECTOR && $type != UserFilter::JOB_SUBSECTOR && $type != UserFilter::JOB_SUBSUBSECTOR) {
            Platal::page()->killError("Type de secteur non valide.");
        }
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addJobSectorizationFilter($this->type);
        return $sub . '.id = ' . XDB::format('{?}', $this->val);
    }
}
// }}}

// {{{ class UFC_Job_Description
/** Filters users based on their job description
 * @param $description The text being searched for
 * @param $fields The fields to search for (user-defined, ((sub|)sub|)sector)
 */
class UFC_Job_Description implements UserFilterCondition
{

    private $description;
    private $fields;

    public function __construct($description, $fields)
    {
        $this->fields = $fields;
        $this->description = $description;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $conds = array();
        if ($this->fields & UserFilter::JOB_USERDEFINED) {
            $sub = $uf->addJobFilter();
            $conds[] = $sub . '.description ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->description);
        }
        if ($this->fields & UserFilter::JOB_CV) {
            $uf->requireProfiles();
            $conds[] = 'p.cv ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->description);
        }
        if ($this->fields & UserFilter::JOB_SECTOR) {
            $sub = $uf->addJobSectorizationFilter(UserFilter::JOB_SECTOR);
            $conds[] = $sub . '.name ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->description);
        }
        if ($this->fields & UserFilter::JOB_SUBSECTOR) {
            $sub = $uf->addJobSectorizationFilter(UserFilter::JOB_SUBSECTOR);
            $conds[] = $sub . '.name ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->description);
        }
        if ($this->fields & UserFilter::JOB_SUBSUBSECTOR) {
            $sub = $uf->addJobSectorizationFilter(UserFilter::JOB_SUBSUBSECTOR);
            $conds[] = $sub . '.name ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->description);
            $sub = $uf->addJobSectorizationFilter(UserFilter::JOB_ALTERNATES);
            $conds[] = $sub . '.name ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->description);
        }
        return implode(' OR ', $conds);
    }
}
// }}}

// {{{ class UFC_Networking
/** Filters users based on network identity (IRC, ...)
 * @param $type Type of network (-1 for any)
 * @param $value Value to search
 */
class UFC_Networking implements UserFilterCondition
{
    private $type;
    private $value;

    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addNetworkingFilter();
        $conds = array();
        $conds[] = $sub . '.address ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->value);
        if ($this->type != -1) {
            $conds[] = $sub . '.network_type = ' . XDB::format('{?}', $this->type);
        }
        return implode(' AND ', $conds);
    }
}
// }}}

// {{{ class UFC_Phone
/** Filters users based on their phone number
 * @param $num_type Type of number (pro/user/home)
 * @param $phone_type Type of phone (fixed/mobile/fax)
 * @param $number Phone number
 */
class UFC_Phone implements UserFilterCondition
{
    const NUM_PRO   = 'pro';
    const NUM_USER  = 'user';
    const NUM_HOME  = 'address';
    const NUM_ANY   = 'any';

    const PHONE_FIXED   = 'fixed';
    const PHONE_MOBILE  = 'mobile';
    const PHONE_FAX     = 'fax';
    const PHONE_ANY     = 'any';

    private $num_type;
    private $phone_type;
    private $number;

    public function __construct($number, $num_type = self::NUM_ANY, $phone_type = self::PHONE_ANY)
    {
        require_once('profil.func.inc.php');
        $this->number = $number;
        $this->num_type = $num_type;
        $this->phone_type = format_phone_number($phone_type);
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addPhoneFilter();
        $conds = array();
        $conds[] = $sub . '.search_tel = ' . XDB::format('{?}', $this->number);
        if ($this->num_type != self::NUM_ANY) {
            $conds[] = $sub . '.link_type = ' . XDB::format('{?}', $this->num_type);
        }
        if ($this->phone_type != self::PHONE_ANY) {
            $conds[] = $sub . '.tel_type = ' . XDB::format('{?}', $this->phone_type);
        }
        return implode(' AND ', $conds);
    }
}
// }}}

// {{{ class UFC_Medal
/** Filters users based on their medals
 * @param $medal ID of the medal
 * @param $grade Grade of the medal (null for 'any')
 */
class UFC_Medal implements UserFilterCondition
{
    private $medal;
    private $grade;

    public function __construct($medal, $grade = null)
    {
        $this->medal = $medal;
        $this->grade = $grade;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $conds = array();
        $sub = $uf->addMedalFilter();
        $conds[] = $sub . '.mid = ' . XDB::format('{?}', $this->medal);
        if ($this->grade != null) {
            $conds[] = $sub . '.gid = ' . XDB::format('{?}', $this->grade);
        }
        return implode(' AND ', $conds);
    }
}
// }}}

// {{{ class UFC_Mentor_Expertise
/** Filters users by mentoring expertise
 * @param $expertise Domain of expertise
 */
class UFC_Mentor_Expertise implements UserFilterCondition
{
    private $expertise;

    public function __construct($expertise)
    {
        $this->expertise = $expertise;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addMentorFilter(UserFilter::MENTOR_EXPERTISE);
        return $sub . '.expertise ' . XDB::formatWildcards(XDB::WILDCARD_CONTAINS, $this->expertise);
    }
}
// }}}

// {{{ class UFC_Mentor_Country
/** Filters users by mentoring country
 * @param $country Two-letters code of country being searched
 */
class UFC_Mentor_Country implements UserFilterCondition
{
    private $country;

    public function __construct($country)
    {
        $this->country = $country;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addMentorFilter(UserFilter::MENTOR_COUNTRY);
        return $sub . '.country = ' . XDB::format('{?}', $this->country);
    }
}
// }}}

// {{{ class UFC_Mentor_Sectorization
/** Filters users based on mentoring (sub|)sector
 * @param $sector ID of (sub)sector
 * @param $type Whether we are looking for a sector or a subsector
 */
class UFC_Mentor_Sectorization implements UserFilterCondition
{
    const SECTOR    = 1;
    const SUBSECTOR = 2;
    private $sector;
    private $type;

    public function __construct($sector, $type = self::SECTOR)
    {
        $this->sector = $sector;
        $this->type = $type;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addMentorFilter(UserFilter::MENTOR_SECTOR);
        if ($this->type == self::SECTOR) {
            $field = 'sectorid';
        } else {
            $field = 'subsectorid';
        }
        return $sub . '.' . $field . ' = ' . XDB::format('{?}', $this->sector);
    }
}
// }}}

// {{{ class UFC_UserRelated
/** Filters users based on a relation toward a user
 * @param $user User to which searched users are related
 */
abstract class UFC_UserRelated implements UserFilterCondition
{
    protected $user;
    public function __construct(PlUser &$user)
    {
        $this->user =& $user;
    }
}
// }}}

// {{{ class UFC_Contact
/** Filters users who belong to selected user's contacts
 */
class UFC_Contact extends UFC_UserRelated
{
    public function buildCondition(PlFilter &$uf)
    {
        $sub = $uf->addContactFilter($this->user->id());
        return 'c' . $sub . '.contact IS NOT NULL';
    }
}
// }}}

// {{{ class UFC_WatchRegistration
/** Filters users being watched by selected user
 */
class UFC_WatchRegistration extends UFC_UserRelated
{
    public function buildCondition(PlFilter &$uf)
    {
        if (!$this->user->watchType('registration')) {
            return PlFilterCondition::COND_FALSE;
        }
        $uids = $this->user->watchUsers();
        if (count($uids) == 0) {
            return PlFilterCondition::COND_FALSE;
        } else {
            return XDB::format('$UID IN {?}', $uids);
        }
    }
}
// }}}

// {{{ class UFC_WatchPromo
/** Filters users belonging to a promo watched by selected user
 * @param $user Selected user (the one watching promo)
 * @param $grade Formation the user is watching
 */
class UFC_WatchPromo extends UFC_UserRelated
{
    private $grade;
    public function __construct(PlUser &$user, $grade = UserFilter::GRADE_ING)
    {
        parent::__construct($user);
        $this->grade = $grade;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $promos = $this->user->watchPromos();
        if (count($promos) == 0) {
            return PlFilterCondition::COND_FALSE;
        } else {
            $sube = $uf->addEducationFilter(true, $this->grade);
            $field = 'pe' . $sube . '.' . UserFilter::promoYear($this->grade);
            return XDB::format($field . ' IN {?}', $promos);
        }
    }
}
// }}}

// {{{ class UFC_WatchContact
/** Filters users watched by selected user
 */
class UFC_WatchContact extends UFC_Contact
{
    public function buildCondition(PlFilter &$uf)
    {
        if (!$this->user->watchContacts()) {
            return PlFilterCondition::COND_FALSE;
        }
        return parent::buildCondition($uf);
    }
}
// }}}

// {{{ class UFC_MarketingHash
/** Filters users using the hash generated
 * to send marketing emails to him.
 */
class UFC_MarketingHash implements UserFilterCondition
{
    private $hash;

    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function buildCondition(PlFilter &$uf)
    {
        $table = $uf->addMarketingHash();
        return XDB::format('rm.hash = {?}', $this->hash);
    }
}
// }}}

/******************
 * ORDERS
 ******************/

// {{{ class UserFilterOrder
/** Base class for ordering results of a query.
 * Parameters for the ordering must be given to the constructor ($desc for a
 *     descending order).
 * The getSortTokens function is used to get actual ordering part of the query.
 */
abstract class UserFilterOrder extends PlFilterOrder
{
    /** This function must return the tokens to use for ordering
     * @param &$uf The UserFilter whose results must be ordered
     * @return The name of the field to use for ordering results
     */
//    abstract protected function getSortTokens(UserFilter &$uf);
}
// }}}

// {{{ class UFO_Promo
/** Orders users by promotion
 * @param $grade Formation whose promotion users should be sorted by (restricts results to users of that formation)
 * @param $desc Whether sort is descending
 */
class UFO_Promo extends UserFilterOrder
{
    private $grade;

    public function __construct($grade = null, $desc = false)
    {
        parent::__construct($desc);
        $this->grade = $grade;
    }

    protected function getSortTokens(PlFilter &$uf)
    {
        if (UserFilter::isGrade($this->grade)) {
            $sub = $uf->addEducationFilter($this->grade);
            return 'pe' . $sub . '.' . UserFilter::promoYear($this->grade);
        } else {
            $sub = $uf->addDisplayFilter();
            return 'pd' . $sub . '.promo';
        }
    }
}
// }}}

// {{{ class UFO_Name
/** Sorts users by name
 * @param $type Type of name on which to sort (firstname...)
 * @param $variant Variant of that name to use (marital, ordinary...)
 * @param $particle Set to true if particles should be included in the sorting order
 * @param $desc If sort order should be descending
 */
class UFO_Name extends UserFilterOrder
{
    private $type;
    private $variant;
    private $particle;

    public function __construct($type, $variant = null, $particle = false, $desc = false)
    {
        parent::__construct($desc);
        $this->type = $type;
        $this->variant = $variant;
        $this->particle = $particle;
    }

    protected function getSortTokens(PlFilter &$uf)
    {
        if (Profile::isDisplayName($this->type)) {
            $sub = $uf->addDisplayFilter();
            return 'pd' . $sub . '.' . $this->type;
        } else {
            $sub = $uf->addNameFilter($this->type, $this->variant);
            if ($this->particle) {
                return 'CONCAT(pn' . $sub . '.particle, \' \', pn' . $sub . '.name)';
            } else {
                return 'pn' . $sub . '.name';
            }
        }
    }
}
// }}}

// {{{ class UFO_Score
class UFO_Score extends UserFilterOrder
{
    protected function getSortTokens(PlFilter &$uf)
    {
        $sub = $uf->addNameTokensFilter();
        return 'SUM(' . $sub . '.score)';
    }
}
// }}}

// {{{ class UFO_Registration
/** Sorts users based on registration date
 */
class UFO_Registration extends UserFilterOrder
{
    protected function getSortTokens(PlFilter &$uf)
    {
        return 'a.registration_date';
    }
}
// }}}

// {{{ class UFO_Birthday
/** Sorts users based on next birthday date
 */
class UFO_Birthday extends UserFilterOrder
{
    protected function getSortTokens(PlFilter &$uf)
    {
        return 'p.next_birthday';
    }
}
// }}}

// {{{ class UFO_ProfileUpdate
/** Sorts users based on last profile update
 */
class UFO_ProfileUpdate extends UserFilterOrder
{
    protected function getSortTokens(PlFilter &$uf)
    {
        return 'p.last_change';
    }
}
// }}}

// {{{ class UFO_Death
/** Sorts users based on death date
 */
class UFO_Death extends UserFilterOrder
{
    protected function getSortTokens(PlFilter &$uf)
    {
        return 'p.deathdate';
    }
}
// }}}


/***********************************
  *********************************
          USER FILTER CLASS
  *********************************
 ***********************************/

// {{{ class UserFilter
/** This class provides a convenient and centralized way of filtering users.
 *
 * Usage:
 * $uf = new UserFilter(new UFC_Blah($x, $y), new UFO_Coin($z, $t));
 *
 * Resulting UserFilter can be used to:
 * - get a list of User objects matching the filter
 * - get a list of UIDs matching the filter
 * - get the number of users matching the filter
 * - check whether a given User matches the filter
 * - filter a list of User objects depending on whether they match the filter
 *
 * Usage for UFC and UFO objects:
 * A UserFilter will call all private functions named XXXJoins.
 * These functions must return an array containing the list of join
 * required by the various UFC and UFO associated to the UserFilter.
 * Entries in those returned array are of the following form:
 *   'join_tablealias' => array('join_type', 'joined_table', 'join_criter')
 * which will be translated into :
 *   join_type JOIN joined_table AS join_tablealias ON (join_criter)
 * in the final query.
 *
 * In the join_criter text, $ME is replaced with 'join_tablealias', $PID with
 * profile.pid, and $UID with accounts.uid.
 *
 * For each kind of "JOIN" needed, a function named addXXXFilter() should be defined;
 * its parameter will be used to set various private vars of the UserFilter describing
 * the required joins ; such a function shall return the "join_tablealias" to use
 * when referring to the joined table.
 *
 * For example, if data from profile_job must be available to filter results,
 * the UFC object will call $uf-addJobFilter(), which will set the 'with_pj' var and 
 * return 'pj', the short name to use when referring to profile_job; when building
 * the query, calling the jobJoins function will return an array containing a single
 * row:
 *   'pj' => array('left', 'profile_job', '$ME.pid = $UID');
 *
 * The 'register_optional' function can be used to generate unique table aliases when
 * the same table has to be joined several times with different aliases.
 */
class UserFilter extends PlFilter
{
    protected $joinMethods = array();

    protected $joinMetas = array(
                                '$PID' => 'p.pid',
                                '$UID' => 'a.uid',
                                );

    private $root;
    private $sort = array();
    private $query = null;
    private $orderby = null;

    private $lastcount = null;

    public function __construct($cond = null, $sort = null)
    {
        if (empty($this->joinMethods)) {
            $class = new ReflectionClass('UserFilter');
            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if (substr($name, -5) == 'Joins' && $name != 'buildJoins') {
                    $this->joinMethods[] = $name;
                }
            }
        }
        if (!is_null($cond)) {
            if ($cond instanceof PlFilterCondition) {
                $this->setCondition($cond);
            }
        }
        if (!is_null($sort)) {
            if ($sort instanceof UserFilterOrder) {
                $this->addSort($sort);
            } else if (is_array($sort)) {
                foreach ($sort as $s) {
                    $this->addSort($s);
                }
            }
        }
    }

    private function buildQuery()
    {
        if (is_null($this->orderby)) {
            $orders = array();
            foreach ($this->sort as $sort) {
                $orders = array_merge($orders, $sort->buildSort($this));
            }
            if (count($orders) == 0) {
                $this->orderby = '';
            } else {
                $this->orderby = 'ORDER BY  ' . implode(', ', $orders);
            }
        }
        if (is_null($this->query)) {
            $where = $this->root->buildCondition($this);
            if ($this->with_forced_sn) {
                $this->requireProfiles();
                $from = 'search_name AS sn';
            } else if ($this->with_accounts) {
                $from = 'accounts AS a';
            } else {
                $this->requireProfiles();
                $from = 'profiles AS p';
            }
            $joins = $this->buildJoins();
            $this->query = 'FROM  ' . $from . '
                               ' . $joins . '
                           WHERE  (' . $where . ')';
        }
    }

    private function getUIDList($uids = null, PlLimit &$limit)
    {
        $this->requireAccounts();
        $this->buildQuery();
        $lim = $limit->getSql();
        $cond = '';
        if (!is_null($uids)) {
            $cond = XDB::format(' AND a.uid IN {?}', $uids);
        }
        $fetched = XDB::fetchColumn('SELECT SQL_CALC_FOUND_ROWS  a.uid
                                    ' . $this->query . $cond . '
                                   GROUP BY  a.uid
                                    ' . $this->orderby . '
                                    ' . $lim);
        $this->lastcount = (int)XDB::fetchOneCell('SELECT FOUND_ROWS()');
        return $fetched;
    }

    private function getPIDList($pids = null, PlLimit &$limit)
    {
        $this->requireProfiles();
        $this->buildQuery();
        $lim = $limit->getSql();
        $cond = '';
        if (!is_null($pids)) {
            $cond = XDB::format(' AND p.pid IN {?}', $pids);
        }
        $fetched = XDB::fetchColumn('SELECT  SQL_CALC_FOUND_ROWS  p.pid
                                    ' . $this->query . $cond . '
                                   GROUP BY  p.pid
                                    ' . $this->orderby . '
                                    ' . $lim);
        $this->lastcount = (int)XDB::fetchOneCell('SELECT FOUND_ROWS()');
        return $fetched;
    }

    private static function defaultLimit($limit) {
        if ($limit == null) {
            return new PlLimit();
        } else {
            return $limit;
        }
    }

    /** Check that the user match the given rule.
     */
    public function checkUser(PlUser &$user)
    {
        $this->requireAccounts();
        $this->buildQuery();
        $count = (int)XDB::fetchOneCell('SELECT  COUNT(*)
                                        ' . $this->query . XDB::format(' AND a.uid = {?}', $user->id()));
        return $count == 1;
    }

    /** Check that the profile match the given rule.
     */
    public function checkProfile(Profile &$profile)
    {
        $this->requireProfiles();
        $this->buildQuery();
        $count = (int)XDB::fetchOneCell('SELECT  COUNT(*)
                                        ' . $this->query . XDB::format(' AND p.pid = {?}', $profile->id()));
        return $count == 1;
    }

    /** Default filter is on users
     */
    public function filter(array $users, $limit = null)
    {
        return $this->filterUsers($users, self::defaultLimit($limit));
    }

    /** Filter a list of users to extract the users matching the rule.
     */
    public function filterUsers(array $users, $limit = null)
    {
        $limit = self::defaultLimit($limit);
        $this->requireAccounts();
        $this->buildQuery();
        $table = array();
        $uids  = array();
        foreach ($users as $user) {
            if ($user instanceof PlUser) {
                $uid = $user->id();
            } else {
                $uid = $user;
            }
            $uids[] = $uid;
            $table[$uid] = $user;
        }
        $fetched = $this->getUIDList($uids, $limit);
        $output = array();
        foreach ($fetched as $uid) {
            $output[] = $table[$uid];
        }
        return $output;
    }

    /** Filter a list of profiles to extract the users matching the rule.
     */
    public function filterProfiles(array $profiles, $limit = null)
    {
        $limit = self::defaultLimit($limit);
        $this->requireProfiles();
        $this->buildQuery();
        $table = array();
        $pids  = array();
        foreach ($profiles as $profile) {
            if ($profile instanceof Profile) {
                $pid = $profile->id();
            } else {
                $pid = $profile;
            }
            $pids[] = $pid;
            $table[$pid] = $profile;
        }
        $fetched = $this->getPIDList($pids, $limit);
        $output = array();
        foreach ($fetched as $pid) {
            $output[] = $table[$pid];
        }
        return $output;
    }

    public function getUIDs($limit = null)
    {
        $limit = self::defaultLimit($limit);
        return $this->getUIDList(null, $limit);
    }

    public function getUID($pos = 0)
    {
        $uids =$this->getUIDList(null, new PlFilter(1, $pos));
        if (count($uids) == 0) {
            return null;
        } else {
            return $uids[0];
        }
    }

    public function getPIDs($limit = null)
    {
        $limit = self::defaultLimit($limit);
        return $this->getPIDList(null, $limit);
    }

    public function getPID($pos = 0)
    {
        $pids =$this->getPIDList(null, new PlFilter(1, $pos));
        if (count($pids) == 0) {
            return null;
        } else {
            return $pids[0];
        }
    }

    public function getUsers($limit = null)
    {
        return User::getBulkUsersWithUIDs($this->getUIDs($limit));
    }

    public function getUser($pos = 0)
    {
        $uid = $this->getUID($pos);
        if ($uid == null) {
            return null;
        } else {
            return User::getWithUID($uid);
        }
    }

    public function iterUsers($limit = null)
    {
        return User::iterOverUIDs($this->getUIDs($limit));
    }

    public function getProfiles($limit = null, $fields = 0x0000, $visibility = null)
    {
        return Profile::getBulkProfilesWithPIDs($this->getPIDs($limit), $fields, $visibility);
    }

    public function getProfile($pos = 0, $fields = 0x0000, $visibility = null)
    {
        $pid = $this->getPID($pos);
        if ($pid == null) {
            return null;
        } else {
            return Profile::get($pid, $fields, $visibility);
        }
    }

    public function iterProfiles($limit = null, $fields = 0x0000, $visibility = null)
    {
        return Profile::iterOverPIDs($this->getPIDs($limit), true, $fields, $visibility);
    }

    public function get($limit = null)
    {
        return $this->getUsers($limit);
    }

    public function getTotalCount()
    {
        if (is_null($this->lastcount)) {
            $this->buildQuery();
            if ($this->with_accounts) {
                $field = 'a.uid';
            } else {
                $field = 'p.pid';
            }
            return (int)XDB::fetchOneCell('SELECT  COUNT(DISTINCT ' . $field . ')
                                          ' . $this->query);
        } else {
            return $this->lastcount;
        }
    }

    public function setCondition(PlFilterCondition &$cond)
    {
        $this->root =& $cond;
        $this->query = null;
    }

    public function addSort(PlFilterOrder &$sort)
    {
        $this->sort[] = $sort;
        $this->orderby = null;
    }

    static public function getLegacy($promo_min, $promo_max)
    {
        if ($promo_min != 0) {
            $min = new UFC_Promo('>=', self::GRADE_ING, intval($promo_min));
        } else {
            $min = new PFC_True();
        }
        if ($promo_max != 0) {
            $max = new UFC_Promo('<=', self::GRADE_ING, intval($promo_max));
        } else {
            $max = new PFC_True();
        }
        return new UserFilter(new PFC_And($min, $max));
    }

    static public function sortByName()
    {
        return array(new UFO_Name(Profile::LASTNAME), new UFO_Name(Profile::FIRSTNAME));
    }

    static public function sortByPromo()
    {
        return array(new UFO_Promo(), new UFO_Name(Profile::LASTNAME), new UFO_Name(Profile::FIRSTNAME));
    }

    static private function getDBSuffix($string)
    {
        if (is_array($string)) {
            if (count($string) == 1) {
                return self::getDBSuffix(array_pop($string));
            }
            return md5(implode('|', $string));
        } else {
            return preg_replace('/[^a-z0-9]/i', '', $string);
        }
    }


    /** Stores a new (and unique) table alias in the &$table table
     * @param   &$table Array in which the table alias must be stored
     * @param   $val    Value which will then be used to build the join
     * @return          Name of the newly created alias
     */
    private $option = 0;
    private function register_optional(array &$table, $val)
    {
        if (is_null($val)) {
            $sub   = $this->option++;
            $index = null;
        } else {
            $sub   = self::getDBSuffix($val);
            $index = $val;
        }
        $sub = '_' . $sub;
        $table[$sub] = $index;
        return $sub;
    }

    /** PROFILE VS ACCOUNT
     */
    private $with_profiles  = false;
    private $with_accounts  = false;
    private $with_forced_sn = false;
    public function requireAccounts()
    {
        $this->with_accounts = true;
    }

    public function requireProfiles()
    {
        $this->with_profiles = true;
    }

    /** Forces the "FROM" to use search_name instead of accounts or profiles */
    public function forceSearchName()
    {
        $this->with_forced_sn = true;
    }

    protected function accountJoins()
    {
        $joins = array();
        /** Quick search is much more efficient with sn first and PID second */
        if ($this->with_forced_sn) {
            $joins['p'] = PlSqlJoin::left('profiles', '$PID = sn.pid');
            if ($this->with_accounts) {
                $joins['ap'] = PlSqlJoin::left('account_profiles', '$ME.pid = $PID');
                $joins['a'] = PlSqlJoin::left('accounts', '$UID = ap.uid');
            }
        } else if ($this->with_profiles && $this->with_accounts) {
            $joins['ap'] = PlSqlJoin::left('account_profiles', '$ME.uid = $UID AND FIND_IN_SET(\'owner\', ap.perms)');
            $joins['p'] = PlSqlJoin::left('profiles', '$PID = ap.pid');
        }
        return $joins;
    }

    /** DISPLAY
     */
    const DISPLAY = 'display';
    private $pd = false;
    public function addDisplayFilter()
    {
        $this->requireProfiles();
        $this->pd = true;
        return '';
    }

    protected function displayJoins()
    {
        if ($this->pd) {
            return array('pd' => PlSqlJoin::left('profile_display', '$ME.pid = $PID'));
        } else {
            return array();
        }
    }

    /** LOGGER
     */

    private $with_logger = false;
    public function addLoggerFilter()
    {
        $this->with_logger = true;
        $this->requireAccounts();
        return 'ls';
    }
    protected function loggerJoins()
    {
        $joins = array();
        if ($this->with_logger) {
            $joins['ls'] = PlSqlJoin::left('log_sessions', '$ME.uid = $UID');
        }
        return $joins;
    }

    /** NAMES
     */

    static public function assertName($name)
    {
        if (!DirEnum::getID(DirEnum::NAMETYPES, $name)) {
            Platal::page()->kill('Invalid name type: ' . $name);
        }
    }

    private $pn  = array();
    public function addNameFilter($type, $variant = null)
    {
        $this->requireProfiles();
        if (!is_null($variant)) {
            $ft  = $type . '_' . $variant;
        } else {
            $ft = $type;
        }
        $sub = '_' . $ft;
        self::assertName($ft);

        if (!is_null($variant) && $variant == 'other') {
            $sub .= $this->option++;
        }
        $this->pn[$sub] = DirEnum::getID(DirEnum::NAMETYPES, $ft);
        return $sub;
    }

    protected function nameJoins()
    {
        $joins = array();
        foreach ($this->pn as $sub => $type) {
            $joins['pn' . $sub] = PlSqlJoin::left('profile_name', '$ME.pid = $PID AND $ME.typeid = {?}', $type);
        }
        return $joins;
    }

    /** NAMETOKENS
     */
    private $with_sn = false;
    // Set $doingQuickSearch to true if you wish to optimize the query
    public function addNameTokensFilter($doingQuickSearch = false)
    {
        $this->requireProfiles();
        $this->with_forced_sn = ($this->with_forced_sn || $doingQuickSearch);
        $this->with_sn = true;
        return 'sn';
    }

    protected function nameTokensJoins()
    {
        /* We don't return joins, since with_sn forces the SELECT to run on search_name first */
        if ($this->with_sn && !$this->with_forced_sn) {
            return array(
                'sn' => PlSqlJoin::left('search_name', '$ME.pid = $PID')
            );
        } else {
            return array();
        }
    }

    /** NATIONALITY
     */

    private $with_nat = false;
    public function addNationalityFilter()
    {
        $this->with_nat = true;
        return 'ngc';
    }

    protected function nationalityJoins()
    {
        $joins = array();
        if ($this->with_nat) {
            $joins['ngc'] = PlSqlJoin::left('geoloc_countries', '$ME.iso_3166_1_a2 = p.nationality1 OR $ME.iso_3166_1_a2 = p.nationality2 OR $ME.iso_3166_1_a2 = p.nationality3');
        }
        return $joins;
    }

    /** EDUCATION
     */
    const GRADE_ING = 'Ing.';
    const GRADE_PHD = 'PhD';
    const GRADE_MST = 'M%';
    static public function isGrade($grade)
    {
        return $grade == self::GRADE_ING || $grade == self::GRADE_PHD || $grade == self::GRADE_MST;
    }

    static public function assertGrade($grade)
    {
        if (!self::isGrade($grade)) {
            Platal::page()->killError("Diplôme non valide: $grade");
        }
    }

    static public function promoYear($grade)
    {
        // XXX: Definition of promotion for phds and masters might change in near future.
        return ($grade == UserFilter::GRADE_ING) ? 'entry_year' : 'grad_year';
    }

    private $pepe     = array();
    private $with_pee = false;
    public function addEducationFilter($x = false, $grade = null)
    {
        $this->requireProfiles();
        if (!$x) {
            $index = $this->option;
            $sub   = $this->option++;
        } else {
            self::assertGrade($grade);
            $index = $grade;
            $sub   = $grade[0];
            $this->with_pee = true;
        }
        $sub = '_' . $sub;
        $this->pepe[$index] = $sub;
        return $sub;
    }

    protected function educationJoins()
    {
        $joins = array();
        if ($this->with_pee) {
            $joins['pee'] = PlSqlJoin::inner('profile_education_enum', 'pee.abbreviation = \'X\'');
        }
        foreach ($this->pepe as $grade => $sub) {
            if ($this->isGrade($grade)) {
                $joins['pe' . $sub] = PlSqlJoin::left('profile_education', '$ME.eduid = pee.id AND $ME.pid = $PID');
                $joins['pede' . $sub] = PlSqlJoin::inner('profile_education_degree_enum', '$ME.id = pe' . $sub . '.degreeid AND $ME.abbreviation LIKE {?}', $grade);
            } else {
                $joins['pe' . $sub] = PlSqlJoin::left('profile_education', '$ME.pid = $PID');
                $joins['pee' . $sub] = PlSqlJoin::inner('profile_education_enum', '$ME.id = pe' . $sub . '.eduid');
                $joins['pede' . $sub] = PlSqlJoin::inner('profile_education_degree_enum', '$ME.id = pe' . $sub . '.degreeid');
            }
        }
        return $joins;
    }


    /** GROUPS
     */
    private $gpm = array();
    public function addGroupFilter($group = null)
    {
        $this->requireAccounts();
        if (!is_null($group)) {
            if (is_int($group) || ctype_digit($group)) {
                $index = $sub = $group;
            } else {
                $index = $group;
                $sub   = self::getDBSuffix($group);
            }
        } else {
            $sub = 'group_' . $this->option++;
            $index = null;
        }
        $sub = '_' . $sub;
        $this->gpm[$sub] = $index;
        return $sub;
    }

    protected function groupJoins()
    {
        $joins = array();
        foreach ($this->gpm as $sub => $key) {
            if (is_null($key)) {
                $joins['gpa' . $sub] = PlSqlJoin::inner('groups');
                $joins['gpm' . $sub] = PlSqlJoin::left('group_members', '$ME.uid = $UID AND $ME.asso_id = gpa' . $sub . '.id');
            } else if (is_int($key) || ctype_digit($key)) {
                $joins['gpm' . $sub] = PlSqlJoin::left('group_members', '$ME.uid = $UID AND $ME.asso_id = ' . $key);
            } else {
                $joins['gpa' . $sub] = PlSqlJoin::inner('groups', '$ME.diminutif = {?}', $key);
                $joins['gpm' . $sub] = PlSqlJoin::left('group_members', '$ME.uid = $UID AND $ME.asso_id = gpa' . $sub . '.id');
            }
        }
        return $joins;
    }

    /** BINETS
     */

    private $with_bi = false;
    private $with_bd = false;
    public function addBinetsFilter($with_enum = false)
    {
        $this->requireProfiles();
        $this->with_bi = true;
        if ($with_enum) {
            $this->with_bd = true;
            return 'bd';
        } else {
            return 'bi';
        }
    }

    protected function binetsJoins()
    {
        $joins = array();
        if ($this->with_bi) {
            $joins['bi'] = PlSqlJoin::left('profile_binets', '$ME.pid = $PID');
        }
        if ($this->with_bd) {
            $joins['bd'] = PlSqlJoin::left('profile_binet_enum', '$ME.id = bi.binet_id');
        }
        return $joins;
    }

    /** EMAILS
     */
    private $e = array();
    public function addEmailRedirectFilter($email = null)
    {
        $this->requireAccounts();
        return $this->register_optional($this->e, $email);
    }

    private $ve = array();
    public function addVirtualEmailFilter($email = null)
    {
        $this->addAliasFilter(self::ALIAS_FORLIFE);
        return $this->register_optional($this->ve, $email);
    }

    const ALIAS_BEST    = 'bestalias';
    const ALIAS_FORLIFE = 'forlife';
    private $al = array();
    public function addAliasFilter($alias = null)
    {
        $this->requireAccounts();
        return $this->register_optional($this->al, $alias);
    }

    protected function emailJoins()
    {
        global $globals;
        $joins = array();
        foreach ($this->e as $sub=>$key) {
            if (is_null($key)) {
                $joins['e' . $sub] = PlSqlJoin::left('emails', '$ME.uid = $UID AND $ME.flags != \'filter\'');
            } else {
                if (!is_array($key)) {
                    $key = array($key);
                }
                $joins['e' . $sub] = PlSqlJoin::left('emails', '$ME.uid = $UID AND $ME.flags != \'filter\' 
                                                               AND $ME.email IN {?}' . $key);
            }
        }
        foreach ($this->al as $sub=>$key) {
            if (is_null($key)) {
                $joins['al' . $sub] = PlSqlJoin::left('aliases', '$ME.uid = $UID AND $ME.type IN (\'alias\', \'a_vie\')');
            } else if ($key == self::ALIAS_BEST) {
                $joins['al' . $sub] = PlSqlJoin::left('aliases', '$ME.uid = $UID AND $ME.type IN (\'alias\', \'a_vie\') AND  FIND_IN_SET(\'bestalias\', $ME.flags)');
            } else if ($key == self::ALIAS_FORLIFE) {
                $joins['al' . $sub] = PlSqlJoin::left('aliases', '$ME.uid = $UID AND $ME.type = \'a_vie\'');
            } else {
                if (!is_array($key)) {
                    $key = array($key);
                }
                $joins['al' . $sub] = PlSqlJoin::left('aliases', '$ME.uid = $UID AND $ME.type IN (\'alias\', \'a_vie\') 
                                                                  AND $ME.alias IN {?}', $key);
            }
        }
        foreach ($this->ve as $sub=>$key) {
            if (is_null($key)) {
                $joins['v' . $sub] = PlSqlJoin::left('virtual', '$ME.type = \'user\'');
            } else {
                if (!is_array($key)) {
                    $key = array($key);
                }
                $joins['v' . $sub] = PlSqlJoin::left('virtual', '$ME.type = \'user\' AND $ME.alias IN {?}', $key);
            }
            $joins['vr' . $sub] = PlSqlJoin::left('virtual_redirect',
                                                  '$ME.vid = v' . $sub . '.vid
                                                   AND ($ME.redirect IN (CONCAT(al_forlife.alias, \'@\', {?}),
                                                                         CONCAT(al_forlife.alias, \'@\', {?}),
                                                                         a.email))',
                                                  $globals->mail->domain, $globals->mail->domain2);
        }
        return $joins;
    }


    /** ADDRESSES
     */
    private $with_pa = false;
    public function addAddressFilter()
    {
        $this->requireProfiles();
        $this->with_pa = true;
        return 'pa';
    }

    private $with_pac = false;
    public function addAddressCountryFilter()
    {
        $this->requireProfiles();
        $this->addAddressFilter();
        $this->with_pac = true;
        return 'gc';
    }

    private $with_pal = false;
    public function addAddressLocalityFilter()
    {
        $this->requireProfiles();
        $this->addAddressFilter();
        $this->with_pal = true;
        return 'gl';
    }

    protected function addressJoins()
    {
        $joins = array();
        if ($this->with_pa) {
            $joins['pa'] = PlSqlJoin::left('profile_addresses', '$ME.pid = $PID');
        }
        if ($this->with_pac) {
            $joins['gc'] = PlSqlJoin::left('geoloc_countries', '$ME.iso_3166_1_a2 = pa.countryID');
        }
        if ($this->with_pal) {
            $joins['gl'] = PlSqlJoin::left('geoloc_localities', '$ME.id = pa.localityID');
        }
        return $joins;
    }


    /** CORPS
     */

    private $pc = false;
    private $pce = array();
    private $pcr = false;
    public function addCorpsFilter($type)
    {
        $this->requireProfiles();
        $this->pc = true;
        if ($type == UFC_Corps::CURRENT) {
            $pce['pcec'] = 'current_corpsid';
            return 'pcec';
        } else if ($type == UFC_Corps::ORIGIN) {
            $pce['pceo'] = 'original_corpsid';
            return 'pceo';
        }
    }

    public function addCorpsRankFilter()
    {
        $this->requireProfiles();
        $this->pc = true;
        $this->pcr = true;
        return 'pcr';
    }

    protected function corpsJoins()
    {
        $joins = array();
        if ($this->pc) {
            $joins['pc'] = PlSqlJoin::left('profile_corps', '$ME.pid = $PID');
        }
        if ($this->pcr) {
            $joins['pcr'] = PlSqlJoin::left('profile_corps_rank_enum', '$ME.id = pc.rankid');
        }
        foreach($this->pce as $sub => $field) {
            $joins[$sub] = PlSqlJoin::left('profile_corps_enum', '$ME.id = pc.' . $field);
        }
        return $joins;
    }

    /** JOBS
     */

    const JOB_SECTOR        = 0x0001;
    const JOB_SUBSECTOR     = 0x0002;
    const JOB_SUBSUBSECTOR  = 0x0004;
    const JOB_ALTERNATES    = 0x0008;
    const JOB_USERDEFINED   = 0x0010;
    const JOB_CV            = 0x0020;

    const JOB_SECTORIZATION = 0x000F;
    const JOB_ANY           = 0x003F;

    /** Joins :
     * pj => profile_job
     * pje => profile_job_enum
     * pjse => profile_job_sector_enum
     * pjsse => profile_job_subsector_enum
     * pjssse => profile_job_subsubsector_enum
     * pja => profile_job_alternates
     */
    private $with_pj = false;
    private $with_pje = false;
    private $with_pjse = false;
    private $with_pjsse = false;
    private $with_pjssse = false;
    private $with_pja = false;

    public function addJobFilter()
    {
        $this->requireProfiles();
        $this->with_pj = true;
        return 'pj';
    }

    public function addJobCompanyFilter()
    {
        $this->addJobFilter();
        $this->with_pje = true;
        return 'pje';
    }

    public function addJobSectorizationFilter($type)
    {
        $this->addJobFilter();
        if ($type == self::JOB_SECTOR) {
            $this->with_pjse = true;
            return 'pjse';
        } else if ($type == self::JOB_SUBSECTOR) {
            $this->with_pjsse = true;
            return 'pjsse';
        } else if ($type == self::JOB_SUBSUBSECTOR) {
            $this->with_pjssse = true;
            return 'pjssse';
        } else if ($type == self::JOB_ALTERNATES) {
            $this->with_pja = true;
            return 'pja';
        }
    }

    protected function jobJoins()
    {
        $joins = array();
        if ($this->with_pj) {
            $joins['pj'] = PlSqlJoin::left('profile_job', '$ME.pid = $PID');
        }
        if ($this->with_pje) {
            $joins['pje'] = PlSqlJoin::left('profile_job_enum', '$ME.id = pj.jobid');
        }
        if ($this->with_pjse) {
            $joins['pjse'] = PlSqlJoin::left('profile_job_sector_enum', '$ME.id = pj.sectorid');
        }
        if ($this->with_pjsse) {
            $joins['pjsse'] = PlSqlJoin::left('profile_job_subsector_enum', '$ME.id = pj.subsectorid');
        }
        if ($this->with_pjssse) {
            $joins['pjssse'] = PlSqlJoin::left('profile_job_subsubsector_enum', '$ME.id = pj.subsubsectorid');
        }
        if ($this->with_pja) {
            $joins['pja'] = PlSqlJoin::left('profile_job_alternates', '$ME.subsubsectorid = pj.subsubsectorid');
        }
        return $joins;
    }

    /** NETWORKING
     */

    private $with_pnw = false;
    public function addNetworkingFilter()
    {
        $this->requireAccounts();
        $this->with_pnw = true;
        return 'pnw';
    }

    protected function networkingJoins()
    {
        $joins = array();
        if ($this->with_pnw) {
            $joins['pnw'] = PlSqlJoin::left('profile_networking', '$ME.pid = $PID');
        }
        return $joins;
    }

    /** PHONE
     */

    private $with_ptel = false;

    public function addPhoneFilter()
    {
        $this->requireAccounts();
        $this->with_ptel = true;
        return 'ptel';
    }

    protected function phoneJoins()
    {
        $joins = array();
        if ($this->with_ptel) {
            $joins['ptel'] = PlSqlJoin::left('profile_phones', '$ME.pid = $PID');
        }
        return $joins;
    }

    /** MEDALS
     */

    private $with_pmed = false;
    public function addMedalFilter()
    {
        $this->requireProfiles();
        $this->with_pmed = true;
        return 'pmed';
    }

    protected function medalJoins()
    {
        $joins = array();
        if ($this->with_pmed) {
            $joins['pmed'] = PlSqlJoin::left('profile_medals', '$ME.pid = $PID');
        }
        return $joins;
    }

    /** MENTORING
     */

    private $pms = array();
    const MENTOR_EXPERTISE  = 1;
    const MENTOR_COUNTRY    = 2;
    const MENTOR_SECTOR     = 3;

    public function addMentorFilter($type)
    {
        $this->requireAccounts();
        switch($type) {
        case self::MENTOR_EXPERTISE:
            $this->pms['pme'] = 'profile_mentor';
            return 'pme';
        case self::MENTOR_COUNTRY:
            $this->pms['pmc'] = 'profile_mentor_country';
            return 'pmc';
        case self::MENTOR_SECTOR:
            $this->pms['pms'] =  'profile_mentor_sector';
            return 'pms';
        default:
            Platal::page()->killError("Undefined mentor filter.");
        }
    }

    protected function mentorJoins()
    {
        $joins = array();
        foreach ($this->pms as $sub => $tab) {
            $joins[$sub] = PlSqlJoin::left($tab, '$ME.pid = $PID');
        }
        return $joins;
    }

    /** CONTACTS
     */
    private $cts = array();
    public function addContactFilter($uid = null)
    {
        $this->requireProfiles();
        return $this->register_optional($this->cts, is_null($uid) ? null : 'user_' . $uid);
    }

    protected function contactJoins()
    {
        $joins = array();
        foreach ($this->cts as $sub=>$key) {
            if (is_null($key)) {
                $joins['c' . $sub] = PlSqlJoin::left('contacts', '$ME.contact = $PID');
            } else {
                $joins['c' . $sub] = PlSqlJoin::left('contacts', '$ME.uid = {?} AND $ME.contact = $PID', substr($key, 5));
            }
        }
        return $joins;
    }


    /** CARNET
     */
    private $wn = array();
    public function addWatchRegistrationFilter($uid = null)
    {
        $this->requireAccounts();
        return $this->register_optional($this->wn, is_null($uid) ? null : 'user_' . $uid);
    }

    private $wp = array();
    public function addWatchPromoFilter($uid = null)
    {
        $this->requireAccounts();
        return $this->register_optional($this->wp, is_null($uid) ? null : 'user_' . $uid);
    }

    private $w = array();
    public function addWatchFilter($uid = null)
    {
        $this->requireAccounts();
        return $this->register_optional($this->w, is_null($uid) ? null : 'user_' . $uid);
    }

    protected function watchJoins()
    {
        $joins = array();
        foreach ($this->w as $sub=>$key) {
            if (is_null($key)) {
                $joins['w' . $sub] = PlSqlJoin::left('watch');
            } else {
                $joins['w' . $sub] = PlSqlJoin::left('watch', '$ME.uid = {?}', substr($key, 5));
            }
        }
        foreach ($this->wn as $sub=>$key) {
            if (is_null($key)) {
                $joins['wn' . $sub] = PlSqlJoin::left('watch_nonins', '$ME.ni_id = $UID');
            } else {
                $joins['wn' . $sub] = PlSqlJoin::left('watch_nonins', '$ME.uid = {?} AND $ME.ni_id = $UID', substr($key, 5));
            }
        }
        foreach ($this->wn as $sub=>$key) {
            if (is_null($key)) {
                $joins['wn' . $sub] = PlSqlJoin::left('watch_nonins', '$ME.ni_id = $UID');
            } else {
                $joins['wn' . $sub] = PlSqlJoin::left('watch_nonins', '$ME.uid = {?} AND $ME.ni_id = $UID', substr($key, 5));
            }
        }
        foreach ($this->wp as $sub=>$key) {
            if (is_null($key)) {
                $joins['wp' . $sub] = PlSqlJoin::left('watch_promo');
            } else {
                $joins['wp' . $sub] = PlSqlJoin::left('watch_promo', '$ME.uid = {?}', substr($key, 5));
            }
        }
        return $joins;
    }


    /** MARKETING
     */
    private $with_rm;
    public function addMarketingHash()
    {
        $this->requireAccounts();
        $this->with_rm = true;
    }

    protected function marketingJoins()
    {
        if ($this->with_rm) {
            return array('rm' => PlSqlJoin::left('register_marketing', '$ME.uid = $UID'));
        } else {
            return array();
        }
    }
}
// }}}

// {{{ class ProfileFilter
class ProfileFilter extends UserFilter
{
    public function get($limit = null)
    {
        return $this->getProfiles($limit);
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
