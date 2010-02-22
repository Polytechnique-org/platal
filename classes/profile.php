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
                                 $this->first_name, $this->last_name,
                                 $this->full_name, $this->short_name,
                                 $this->promo), $format);
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


    /* Photo
     */
    public function getPhoto($fallback = true)
    {
        /* TODO: migrate photo table to profile_photo, change uid to pid
         */
        $cond = '';
        if ($this->visibility) {
            $cond = ' AND pub IN ' . XDB::formatArray($this->visibility);
        }
        $res = XDB::query('SELECT  *
                             FROM  profile_photos
                            WHERE  attachmime IN (\'jpeg\', \'png\')
                                   ' . $cond . ' AND  pid = {?}',
                          $this->id());
        if ($res->numRows() > 0) {
            $photo = $res->fetchOneAssoc();
            return PlImage::fromData($photo['attach'], 'image/' . $photo['attachmime'],
                                     $photo['x'], $photo['y']);
        } else if ($fallback) {
            return PlImage::fromFile(dirname(__FILE__).'/../htdocs/images/none.png',
                                     'image/png');
        }
        return null;
    }

    /* Addresses
     */
    public function getAddresses($flags, $limit = null)
    {
        $where = XDB::format('pa.pid = {?}', $this->id());
        if ($flags & self::ADDRESS_MAIN) {
            $where .= ' AND FIND_IN_SET(\'current\', pa.flags)';
        }
        if ($flags & self::ADDRESS_POSTAL) {
            $where .= ' AND FIND_IN_SET(\'mail\', pa.flags)';
        }
        if ($this->visibility) {
            $where .= ' AND pa.pub IN ' . XDB::formatArray($this->visibility);
        }
        $type = array();
        if ($flags & self::ADDRESS_PRO) {
            $type[] = 'job';
        }
        if ($flags & self::ADDRESS_PERSO) {
            $type[] = 'home';
        }
        if (count($type) > 0) {
            $where .= ' AND pa.type IN ' . XDB::formatArray($type);
        }
        $limit = is_null($limit) ? '' : XDB::format('LIMIT {?}', (int)$limit);
        return XDB::iterator('SELECT  pa.text, pa.postalCode, pa.type, pa.latitude, pa.longitude,
                                      gl.name AS locality, gas.name AS subAdministrativeArea,
                                      ga.name AS administrativeArea, gc.countryFR AS country,
                                      ppfix.display_tel AS fixed_tel, ppfax.display_tel AS fax_tel,
                                      FIND_IN_SET(\'current\', pa.flags) AS current,
                                      FIND_IN_SET(\'temporary\', pa.flags) AS temporary,
                                      FIND_IN_SET(\'secondary\', pa.flags) AS secondary,
                                      FIND_IN_SET(\'mail\', pa.flags) AS mail, pa.type
                                FROM  profile_addresses AS pa
                           LEFT JOIN  geoloc_localities AS gl ON (gl.id = pa.localityId)
                           LEFT JOIN  geoloc_administrativeareas AS ga ON (ga.id = pa.administrativeAreaId)
                           LEFT JOIN  geoloc_administrativeareas AS gas ON (gas.id = pa.subAdministrativeAreaId)
                           LEFT JOIN  geoloc_countries AS gc ON (gc.iso_3166_1_a2 = pa.countryId)
                           LEFT JOIN  profile_phones AS ppfix ON (ppfix.link_type = \'address\' AND ppfix.pid = pa.pid AND ppfix.link_id = pa.id AND ppfix.tel_type = \'fixed\')
                           LEFT JOIN  profile_phones AS ppfax ON (ppfax.link_type = \'address\' AND ppfax.pid = pa.pid AND ppfax.link_id = pa.id AND ppfax.tel_type = \'fax\')
                               WHERE  ' . $where . '
                            ORDER BY  pa.id
                                      ' . $limit);
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

    private static function fetchProfileData(array $pids, $respect_order = true)
    {
        if (count($pids) == 0) {
            return array();
        }

        if ($respect_order) {
            $order = 'ORDER BY  ' . XDB::formatCustomOrder('p.pid', $pids);
        } else {
            $order = '';
        }

        return XDB::Iterator('SELECT  p.*, p.sex = \'female\' AS sex, pe.entry_year, pe.grad_year,
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
    public static function get($login)
    {
        if (is_array($login)) {
            return new Profile($login);
        }
        $pid = self::getPID($login);
        if (!is_null($pid)) {
            $it = self::iterOverPIDs(array($pid), false);
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

    public static function iterOverUIDs($uids, $respect_order = true)
    {
        return self::iterOverPIDs(self::getPIDsFromUIDs($uids), $respect_order);
    }

    public static function iterOverPIDs($pids, $respect_order = true)
    {
        return new ProfileIterator(self::fetchProfileData($pids, $respect_order));
    }

    /** Return profiles for the list of pids.
     */
    public static function getBulkProfilesWithPIDs(array $pids)
    {
        if (count($pids) == 0) {
            return array();
        }
        $it = self::iterOverPIDs($pids);
        $profiles = array();
        while ($p = $it->next()) {
            $profiles[$p->id()] = $p;
        }
        return $profiles;
    }

    /** Return profiles for uids.
     */
    public static function getBulkProfilesWithUIDS(array $uids)
    {
        if (count($uids) == 0) {
            return array();
        }
        return self::getBulkProfilesWithPIDs(self::getPIDsFromUIDs($uids));
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
                            WHERE  uid = {?}',
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
                XDB::execute('REPLACE INTO  search_name (token, uid, soundex, score, flags)
                                    VALUES  ({?}, {?}, {?}, {?}, {?})',
                             $token, $uid, soundex_fr($token), $score, $key['public']);
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
        if (!preg_match('/^[0-9]{6}$/', $xorgId)) {
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

/** Iterator over a set of Profiles
 * @param an XDB::Iterator obtained from a Profile::fetchProfileData
 */
class ProfileIterator implements PlIterator
{
    private $dbiter;

    public function __construct($dbiter)
    {
        $this->dbiter = $dbiter;
    }

    public function next()
    {
        $data = $this->dbiter->next();
        if ($data == null) {
            return null;
        } else {
            return Profile::get($data);
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
