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

class Profile
{
    static private $v_values = array('public'  => array('public'),
                                     'ax'      => array('ax', 'public'),
                                     'private' => array('private', 'ax', 'public'));

    const VISIBILITY_PUBLIC  = 'public';
    const VISIBILITY_AX      = 'ax';
    const VISIBILITY_PRIVATE = 'private';

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

    static public $name_variants = array(
            self::LASTNAME => array(self::VN_MARITAL, self::VN_ORDINARY),
            self::FIRSTNAME => array(self::VN_ORDINARY, self::VN_INI, self::VN_OTHER)
        );

    const ADDRESS_MAIN       = 0x000001;
    const ADDRESS_PERSO      = 0x000002;
    const ADDRESS_PRO        = 0x000004;
    const ADDRESS_ALL        = 0x000006;
    const ADDRESS_POSTAL     = 0x000008;

    const EDUCATION_MAIN     = 0x000010;
    const EDUCATION_EXTRA    = 0x000020;
    const EDUCATION_ALL      = 0x000040;
    const EDUCATION_FINISHED = 0x000080;
    const EDUCATION_CURRENT  = 0x000100;

    const JOBS_MAIN          = 0x001000;
    const JOBS_ALL           = 0x002000;
    const JOBS_FINISHED      = 0x004000;
    const JOBS_CURRENT       = 0x008000;

    const NETWORKING_ALL     = 0x000000;
    const NETWORKING_WEB     = 0x010000;
    const NETWORKING_IM      = 0x020000;
    const NETWORKING_SOCIAL  = 0x040000;

    const FETCH_ADDRESSES  = 0x00001;
    const FETCH_CORPS      = 0x00002;
    const FETCH_EDU        = 0x00004;
    const FETCH_JOBS       = 0x00008;
    const FETCH_MEDALS     = 0x00010;
    const FETCH_NETWORKING = 0x00020;
    const FETCH_PHONES     = 0x00040;
    const FETCH_PHOTO      = 0x00080;

    const FETCH_ALL        = 0x000FF;

    private $pid;
    private $hrpid;
    private $data = array();

    private $visibility = null;

    private function __construct(array $data)
    {
        $this->data = $data;
        $this->pid = $this->data['pid'];
        $this->hrpid = $this->data['hrpid'];
        if (!S::logged()) {
            $this->setVisibilityLevel(self::VISIBILITY_PUBLIC);
        }
    }

    static private $contexts = array();

    /** Returns the best visibility context toward $visibility
     * @param $visibility A wished visibility level
     * @return An array of compatible visibilities
     *
     * if $visibility is null, the best visibility is returned
     */
    static public function getVisibilityContext($visibility = null)
    {
        if (array_key_exists($visibility, self::$contexts)) {
            return self::$contexts[$visibility];
        }

        $asked_vis = $visibility;

        if (S::logged()) {
            $minvis = self::VISIBILITY_PRIVATE;
        } else {
            $minvis = self::VISIBILITY_PUBLIC;
        }
        if ($visibility == null) {
            $visibility = $minvis;
        }

        if ($minvis == self::VISIBILITY_PUBLIC) {
            $visibility = self::VISIBILITY_PUBLIC;
        }

        $visibility = self::$v_values[$visibility];
        self::$contexts[$asked_vis] = $visibility;

        return $visibility;
    }

    public function id()
    {
        return $this->pid;
    }

    public function hrid()
    {
        return $this->hrpid;
    }

    public function promo()
    {
        return $this->promo;
    }

    public function yearpromo()
    {
        return intval(substr($this->promo, 1, 4));
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

    public function firstName()
    {
        return $this->firstname;
    }

    public function firstNames()
    {
        return $this->nameVariants(self::FIRSTNAME);
    }

    public function lastName()
    {
        return $this->lastname;
    }

    public function lastNames()
    {
        return $this->nameVariants(self::LASTNAME);
    }

    public function isFemale()
    {
        return $this->sex == PlUser::GENDER_FEMALE;
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
        if ($this->nationality1) {
            $nats[] = $this->nationality1;
        }
        if ($this->nationality2) {
            $nats[] = $this->nationality2;
        }
        if ($this->nationality3) {
            $nats[] = $this->nationality3;
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

    /** Sets the level of visibility of the profile
     * Sets $this->visibility to a list of valid visibilities.
     * @param one of the self::VISIBILITY_* values
     */
    public function setVisibilityLevel($visibility)
    {
        if ($visibility != self::VISIBILITY_PRIVATE
         && $visibility != self::VISIBILITY_AX
         && $visibility != self::VISIBILITY_PUBLIC) {
            Platal::page()->kill("Visibility invalide: " . $visibility);
        }
        $this->visibility = self::$v_values[$visibility];
        if ($this->mobile && !in_array($this->mobile_pub, $this->visibility)) {
            unset($this->data['mobile']);
        }
    }

    /** Determine whether an item with visibility $visibility can be displayed
     * with the current level of visibility of the profile
     * @param $visibility The level of visibility to be checked
     */
    public function isVisible($visibility)
    {
        return in_array($visibility, $this->visibility);
    }

    public static function getCompatibleVisibilities($visibility)
    {
        return self::$v_values[$visibility];
    }

    /* Photo
     */
    private $photo = null;
    public function setPhoto(ProfilePhoto $photo)
    {
        $this->photo = $photo;
    }

    public function getPhoto($fallback = true)
    {
        if ($this->photo != null) {
            return $this->photo->pic;
        } else if ($fallback) {
            return PlImage::fromFile(dirname(__FILE__).'/../htdocs/images/none.png',
                                     'image/png');
        }
        return null;
    }

    /* Addresses
     */
    private $addresses = null;
    public function setAddresses(ProfileAddresses $addr)
    {
        $this->addresses = $addr;
    }

    public function getAddresses($flags, $limit = null)
    {
        if ($this->addresses == null) {
            return PlIteratorUtils::fromArray(array());
        } else {
            return $this->addresses->get($flags, $limit);
        }
    }

    public function getMainAddress()
    {
        $it = $this->getAddresses(self::ADDRESS_PERSO | self::ADDRESS_MAIN);
        if ($it->total() == 0) {
            return null;
        } else {
            return $it->next();
        }
    }


    /* Educations
     */
    public function getEducations($flags, $limit = null)
    {
        $where = XDB::format('pe.pid = {?}', $this->id());
        if ($flags & self::EDUCATION_MAIN) {
            $where .= ' AND FIND_IN_SET(\'primary\', pe.flags)';
        } else if ($flags & self::EDUCATION_EXTRA) {
            $where .= ' AND NOT FIND_IN_SET(\'primary\', pe.flags)';
        } else if ($flags & self::EDUCATION_FINISHED) {
            $where .= ' AND pe.grad_year <= YEAR(CURDATE())';
        } else if ($flags & self::EDUCATION_CURRENT) {
            $where .= ' AND pe.grad_year > YEAR(CURDATE())';
        }
        $limit = is_null($limit) ? '' : XDB::format('LIMIT {?}', (int)$limit);
        return XDB::iterator('SELECT  pe.entry_year, pe.grad_year, pe.program,
                                      pee.name AS school, pee.abbreviation AS school_short, pee.url AS school_url, gc.countryFR AS country,
                                      pede.degree, pede.abbreviation AS degree_short, pede.level AS degree_level, pefe.field,
                                      FIND_IN_SET(\'primary\', pe.flags) AS prim
                                FROM  profile_education AS pe
                          INNER JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id)
                           LEFT JOIN  geoloc_countries AS gc ON (gc.iso_3166_1_a2 = pee.country)
                          INNER JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id)
                           LEFT JOIN  profile_education_field_enum AS pefe ON (pe.fieldid = pefe.id)
                               WHERE  ' . $where . '
                            ORDER BY  NOT FIND_IN_SET(\'primary\', pe.flags), pe.entry_year, pe.id
                                      ' . $limit);
    }

    public function getExtraEducations($limit = null)
    {
        return $this->getEducations(self::EDUCATION_EXTRA, $limit);
    }


    /** Networking
     */

    public function getNetworking($flags, $limit = null)
    {
        $where = XDB::format('pn.pid = {?}', $this->id());
        if ($flags & self::NETWORKING_WEB) {
            $where .= ' AND pn.network_type = 0'; // XXX hardcoded reference to web site index
        }
        if ($this->visibility) {
            $where .= ' AND pn.pub IN ' . XDB::formatArray($this->visibility);
        }
        $limit = is_null($limit) ? '' : XDB::format('LIMIT {?}', (int)$limit);
        return XDB::iterator('SELECT  pne.name, pne.icon,
                                      IF (LENGTH(pne.link) > 0, REPLACE(pne.link, \'%s\', pn.address),
                                                                pn.address) AS address
                                FROM  profile_networking AS pn
                          INNER JOIN  profile_networking_enum AS pne ON (pn.network_type = pne.network_type)
                               WHERE  ' . $where . '
                            ORDER BY  pn.network_type, pn.nwid
                                      ' . $limit);
    }

    public function getWebSite()
    {
        $site = $this->getNetworking(self::NETWORKING_WEB, 1);
        if ($site->total() != 1) {
            return null;
        }
        $site = $site->next();
        return $site['address'];
    }


    /** Jobs
     */

    public function getJobs($flags, $limit = null)
    {
        $where = XDB::format('pj.pid = {?}', $this->id());
        $cond  = 'TRUE';
        if ($this->visibility) {
            $where .= ' AND pj.pub IN ' . XDB::formatArray($this->visibility);
            $cond  =  'pj.email_pub IN ' . XDB::formatArray($this->visibility);
        }
        $limit = is_null($limit) ? '' : XDB::format('LIMIT {?}', (int)$limit);
        return XDB::iterator('SELECT  pje.name, pje.acronym, pje.url, pje.email, pje.NAF_code,
                                      pj.description, pj.url AS user_site,
                                      IF (' . $cond . ', pj.email, NULL) AS user_email,
                                      pjse.name AS sector, pjsse.name AS subsector,
                                      pjssse.name AS subsubsector
                                FROM  profile_job AS pj
                          INNER JOIN  profile_job_enum AS pje ON (pje.id = pj.jobid)
                           LEFT JOIN  profile_job_sector_enum AS pjse ON (pjse.id = pj.sectorid)
                           LEFT JOIN  profile_job_subsector_enum AS pjsse ON (pjsse.id = pj.subsectorid)
                           LEFT JOIN  profile_job_subsubsector_enum AS pjssse ON (pjssse.id = pj.subsubsectorid)
                               WHERE  ' . $where . '
                            ORDER BY  pj.id
                                      ' . $limit);
    }

    public function getMailJob()
    {
        $job = $this->getJobs(self::JOBS_MAIN, 1);
        if ($job->total() != 1) {
            return null;
        }
        return $job->next();
    }

    /* Binets
     */
    public function getBinets()
    {
        return XDB::fetchColumn('SELECT  binet_id
                                   FROM  profile_binets
                                  WHERE  pid = {?}', $this->id());
    }


    public function owner()
    {
        return User::getSilent($this);
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

    private static function fetchProfileData(array $pids, $respect_order = true, $fields = 0x0000, $visibility = null)
    {
        if (count($pids) == 0) {
            return array();
        }

        if ($respect_order) {
            $order = 'ORDER BY  ' . XDB::formatCustomOrder('p.pid', $pids);
        } else {
            $order = '';
        }


        $it = XDB::Iterator('SELECT  p.*, p.sex = \'female\' AS sex, pe.entry_year, pe.grad_year,
                                     pn_f.name AS firstname, pn_l.name AS lastname, pn_n.name AS nickname,
                                     IF(pn_uf.name IS NULL, pn_f.name, pn_uf.name) AS firstname_ordinary,
                                     IF(pn_ul.name IS NULL, pn_l.name, pn_ul.name) AS lastname_ordinary,
                                     pd.promo AS promo, pd.short_name, pd.directory_name AS full_name,
                                     pd.directory_name, pp.display_tel AS mobile, pp.pub AS mobile_pub,
                                     ph.attach IS NOT NULL AS has_photo, ph.pub AS photo_pub,
                                     p.last_change < DATE_SUB(NOW(), INTERVAL 365 DAY) AS is_old,
                                     ap.uid AS owner_id
                               FROM  profiles AS p
                         INNER JOIN  profile_display AS pd ON (pd.pid = p.pid)
                         INNER JOIN  profile_education AS pe ON (pe.pid = p.pid AND FIND_IN_SET(\'primary\', pe.flags))
                         INNER JOIN  profile_name AS pn_f ON (pn_f.pid = p.pid
                                                              AND pn_f.typeid = ' . self::getNameTypeId('firstname', true) . ')
                         INNER JOIN  profile_name AS pn_l ON (pn_l.pid = p.pid
                                                              AND pn_l.typeid = ' . self::getNameTypeId('lastname', true) . ')
                          LEFT JOIN  profile_name AS pn_uf ON (pn_uf.pid = p.pid
                                                               AND pn_uf.typeid = ' . self::getNameTypeId('firstname_ordinary', true) . ')
                          LEFT JOIN  profile_name AS pn_ul ON (pn_ul.pid = p.pid
                                                               AND pn_ul.typeid = ' . self::getNameTypeId('lastname_ordinary', true) . ')
                          LEFT JOIN  profile_name AS pn_n ON (pn_n.pid = p.pid
                                                              AND pn_n.typeid = ' . self::getNameTypeId('nickname', true) . ')
                          LEFT JOIN  profile_phones AS pp ON (pp.pid = p.pid AND pp.link_type = \'user\' AND tel_type = \'mobile\')
                          LEFT JOIN  profile_photos AS ph ON (ph.pid = p.pid)
                          LEFT JOIN  account_profiles AS ap ON (ap.pid = p.pid AND FIND_IN_SET(\'owner\', ap.perms))
                              WHERE  p.pid IN ' . XDB::formatArray($pids) . '
                           GROUP BY  p.pid
                                  ' . $order);
        return new ProfileDataIterator($it, $pids, $fields, $visibility);
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
        if (is_array($login)) {
            return new Profile($login);
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
                    return $user->profile();
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
    public static function getBulkProfilesWithPIDs(array $pids, $fields = self::FETCH_ADDRESSES, $visibility = null)
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

    public static function getNameTypeId($type, $for_sql = false)
    {
        if (!S::has('name_types')) {
            $table = XDB::fetchAllAssoc('type', 'SELECT  id, type
                                                   FROM  profile_name_enum');
            S::set('name_types', $table);
        } else {
            $table = S::v('name_types');
        }
        if ($for_sql) {
            return XDB::escape($table[$type]);
        } else {
            return $table[$type];
        }
    }

    public static function rebuildSearchTokens($pid)
    {
        XDB::execute('DELETE FROM  search_name
                            WHERE  pid = {?}',
                     $pid);
        $keys = XDB::iterator("SELECT  CONCAT(n.particle, n.name) AS name, e.score,
                                       FIND_IN_SET('public', e.flags) AS public
                                 FROM  profile_name      AS n
                           INNER JOIN  profile_name_enum AS e ON (n.typeid = e.id)
                                WHERE  n.pid = {?}",
                              $pid);

        foreach ($keys as $i => $key) {
            if ($key['name'] == '') {
                continue;
            }
            $toks  = preg_split('/[ \'\-]+/', $key['name']);
            $token = '';
            $first = 5;
            while ($toks) {
                $token = strtolower(replace_accent(array_pop($toks) . $token));
                $score = ($toks ? 0 : 10 + $first) * ($key['score'] / 10);
                XDB::execute('REPLACE INTO  search_name (token, pid, soundex, score, flags)
                                    VALUES  ({?}, {?}, {?}, {?}, {?})',
                             $token, $pid, soundex_fr($token), $score, $key['public']);
                $first = 0;
            }
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
}

class ProfileDataIterator
{
    private $iterator = null;
    private $fields;

    public function __construct(PlIterator $it, array $pids, $fields = 0x0000, $visibility = null)
    {
        require_once 'profilefields.inc.php';
        $visibility = Profile::getVisibilityContext($visibility);
        $this->fields = $fields;

        $subits = array();
        $callbacks = array();

        $subits[0] = $it;
        $callbacks[0] = PlIteratorUtils::arrayValueCallback('pid');
        $cb = PlIteratorUtils::objectPropertyCallback('pid');

        if ($fields & Profile::FETCH_ADDRESSES) {
            $callbacks[Profile::FETCH_ADDRESSES] = $cb;
            $subits[Profile::FETCH_ADDRESSES] = new ProfileFieldIterator('ProfileAddresses', $pids, $visibility);
        }

        if ($fields & Profile::FETCH_CORPS) {
            $callbacks[Profile::FETCH_CORPS] = $cb;
            $subits[Profile::FETCH_CORPS] = new ProfileFieldIterator('ProfileCorps', $pids, $visibility);
        }

        if ($fields & Profile::FETCH_EDU) {
            $callbacks[Profile::FETCH_EDU] = $cb;
            $subits[Profile::FETCH_EDU] = new ProfileFieldIterator('ProfileEducation', $pids, $visibility);
        }

        if ($fields & Profile::FETCH_JOBS) {
            $callbacks[Profile::FETCH_JOBS] = $cb;
            $subits[Profile::FETCH_JOBS] = new ProfileFieldIterator('ProfileJobs', $pids, $visibility);
        }

        if ($fields & Profile::FETCH_MEDALS) {
            $callbacks[Profile::FETCH_MEDALS] = $cb;
            $subits[Profile::FETCH_MEDALS] = new ProfileFieldIterator('ProfileMedals', $pids, $visibility);
        }

        if ($fields & Profile::FETCH_NETWORKING) {
            $callbacks[Profile::FETCH_NETWORKING] = $cb;
            $subits[Profile::FETCH_NETWORKING] = new ProfileFieldIterator('ProfileNetworking', $pids, $visibility);
        }

        if ($fields & Profile::FETCH_PHONES) {
            $callbacks[Profile::FETCH_PHONES] = $cb;
            $subits[Profile::FETCH_PHONES] = new ProfileFieldIterator('ProfilePhones', $pids, $visibility);
        }

        if ($fields & Profile::FETCH_PHOTO) {
            $callbacks[Profile::FETCH_PHOTO] = $cb;
            $subits[Profile::FETCH_PHOTO] = new ProfileFieldIterator('ProfilePhoto', $pids, $visibility);
        }

        $this->iterator = PlIteratorUtils::parallelIterator($subits, $callbacks, 0);
    }

    private function consolidateFields(array $pf)
    {
        if ($this->fields & Profile::FETCH_PHONES) {
            $phones = $pf[Profile::FETCH_PHONES];

            if ($this->fields & Profile::FETCH_ADDRESSES && $pf[Profile::FETCH_ADDRESSES] != null) {
                $pf[Profile::FETCH_ADDRESSES]->addPhones($phones);
            }
            if ($this->fields & Profile::FETCH_JOBS && $pf[Profile::FETCH_JOBS] != null) {
                $pf[Profile::FETCH_JOBS]->addPhones($phones);
            }
        }

        if ($this->fields & Profile::FETCH_ADDRESSES) {
            $addrs = $pf[Profile::FETCH_ADDRESSES];
            if ($this->fields & Profile::FETCH_JOBS && $pf[Profile::FETCH_JOBS] != null) {
                $pf[Profile::FETCH_JOBS]->addAddresses($addrs);
            }
        }

        return $pf;
    }

    private function fillProfile(array $vals)
    {
        $vals = $this->consolidateFields($vals);

        $pf = Profile::get($vals[0]);
        if ($this->fields & Profile::FETCH_ADDRESSES) {
            if ($vals[Profile::FETCH_ADDRESSES] != null) {
                $pf->setAddresses($vals[Profile::FETCH_ADDRESSES]);
            }
        }
        if ($this->fields & Profile::FETCH_CORPS) {
            $pf->setCorps($vals[Profile::FETCH_CORPS]);
        }
        if ($this->fields & Profile::FETCH_EDU) {
            $pf->setEdu($vals[Profile::FETCH_EDU]);
        }
        if ($this->fields & Profile::FETCH_JOBS) {
            $pf->setJobs($vals[Profile::FETCH_JOBS]);
        }
        if ($this->fields & Profile::FETCH_MEDALS) {
            $pf->setMedals($vals[Profile::FETCH_MEDALS]);
        }
        if ($this->fields & Profile::FETCH_NETWORKING) {
            $pf->setNetworking($vals[Profile::FETCH_NETWORKING]);
        }
        if ($this->fields & Profile::FETCH_PHONES) {
            $pf->setPhones($vals[Profile::FETCH_PHONES]);
        }
        if ($this->fields & Profile::FETCH_PHOTO) {
            if ($vals[Profile::FETCH_PHOTO] != null) {
                $pf->setPhoto($vals[Profile::FETCH_PHOTO]);
            }
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

/** Iterator over a set of Profiles
 * @param an XDB::Iterator obtained from a Profile::fetchProfileData
 */
class ProfileIterator implements PlIterator
{
    private $pdi;
    private $dbiter;

    public function __construct(ProfileDataIterator &$pdi)
    {
        $this->pdi = $pdi;
        $this->dbiter = $pdi->iterator();
    }

    public function next()
    {
        $data = $this->dbiter->next();
        if ($data == null) {
            return null;
        } else {
            return $this->pdi->fillProfile(Profile::get($data));
        }
    }

    public function total()
    {
        return $this->dbiter->total();
    }

    public function first()
    {
        return $this->dbiter->first();
    }

    public function last()
    {
        return $this->dbiter->last();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
