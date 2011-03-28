<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

require_once dirname(__FILE__) . '/userfilter/conditions.inc.php';
require_once dirname(__FILE__) . '/userfilter/orders.inc.php';

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
    private $grouper = null;
    private $query = null;
    private $orderby = null;

    // Store the current 'search' visibility.
    private $profile_visibility = null;

    private $lastusercount = null;
    private $lastprofilecount = null;

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
            if ($sort instanceof PlFilterOrder) {
                $this->addSort($sort);
            } else if (is_array($sort)) {
                foreach ($sort as $s) {
                    $this->addSort($s);
                }
            }
        }

        // This will set the visibility to the default correct level.
        $this->profile_visibility = new ProfileVisibility();
    }

    public function getVisibilityLevels()
    {
        return $this->profile_visibility->levels();
    }

    public function getVisibilityLevel()
    {
        return $this->profile_visibility->level();
    }

    public function restrictVisibilityTo($level)
    {
        $this->profile_visibility->setLevel($level);
    }

    public function getVisibilityCondition($field)
    {
        return $field . ' IN ' . XDB::formatArray($this->getVisibilityLevels());
    }

    private function buildQuery()
    {
        // The root condition is built first because some orders need info
        // available only once all UFC have set their conditions (UFO_Score)
        if (is_null($this->query)) {
            $where = $this->root->buildCondition($this);
            $where = str_replace(array_keys($this->joinMetas),
                                 $this->joinMetas,
                                 $where);
        }
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
            $this->orderby = str_replace(array_keys($this->joinMetas),
                                         $this->joinMetas,
                                         $this->orderby);
        }
        if (is_null($this->query)) {
            if ($this->with_accounts) {
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

    public function hasGroups()
    {
        return $this->grouper != null;
    }

    public function getGroups()
    {
        return $this->getUIDGroups();
    }

    public function getUIDGroups()
    {
        $this->requireAccounts();
        $this->buildQuery();
        $token = $this->grouper->getGroupToken($this);

        $groups = XDB::rawFetchAllRow('SELECT ' . $token . ', COUNT(a.uid)
                                      ' . $this->query . '
                                      GROUP BY  ' . $token,
                                      0);
        return $groups;
    }

    public function getPIDGroups()
    {
        $this->requireProfiles();
        $this->buildQuery();
        $token = $this->grouper->getGroupToken($this);

        $groups = XDB::rawFetchAllRow('SELECT ' . $token . ', COUNT(p.pid)
                                      ' . $this->query . '
                                      GROUP BY  ' . $token,
                                      0);
        return $groups;
    }

    private function getUIDList($uids = null, PlLimit $limit)
    {
        $this->requireAccounts();
        $this->buildQuery();
        $lim = $limit->getSql();
        $cond = '';
        if (!empty($uids)) {
            $cond = XDB::format(' AND a.uid IN {?}', $uids);
        }
        $fetched = XDB::rawFetchColumn('SELECT SQL_CALC_FOUND_ROWS  a.uid
                                       ' . $this->query . $cond . '
                                       GROUP BY  a.uid
                                       ' . $this->orderby . '
                                       ' . $lim);
        $this->lastusercount = (int)XDB::fetchOneCell('SELECT FOUND_ROWS()');
        return $fetched;
    }

    private function getPIDList($pids = null, PlLimit $limit)
    {
        $this->requireProfiles();
        $this->buildQuery();
        $lim = $limit->getSql();
        $cond = '';
        if (!is_null($pids)) {
            $cond = XDB::format(' AND p.pid IN {?}', $pids);
        }
        $fetched = XDB::rawFetchColumn('SELECT  SQL_CALC_FOUND_ROWS  p.pid
                                       ' . $this->query . $cond . '
                                       GROUP BY  p.pid
                                       ' . $this->orderby . '
                                       ' . $lim);
        $this->lastprofilecount = (int)XDB::fetchOneCell('SELECT FOUND_ROWS()');
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
    public function checkUser(PlUser $user)
    {
        $this->requireAccounts();
        $this->buildQuery();
        $count = (int)XDB::rawFetchOneCell('SELECT  COUNT(*)
                                           ' . $this->query
                                             . XDB::format(' AND a.uid = {?}', $user->id()));
        return $count == 1;
    }

    /** Check that the profile match the given rule.
     */
    public function checkProfile(Profile $profile)
    {
        $this->requireProfiles();
        $this->buildQuery();
        $count = (int)XDB::rawFetchOneCell('SELECT  COUNT(*)
                                           ' . $this->query
                                             . XDB::format(' AND p.pid = {?}', $profile->id()));
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
        $uids =$this->getUIDList(null, new PlLimit(1, $pos));
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
        $pids =$this->getPIDList(null, new PlLimit(1, $pos));
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

    public function getIds($limit = null)
    {
        return $this->getUIDs();
    }

    public function getTotalCount()
    {
        return $this->getTotalUserCount();
    }

    public function getTotalUserCount()
    {
        if (is_null($this->lastusercount)) {
            $this->requireAccounts();
            $this->buildQuery();
            return (int)XDB::rawFetchOneCell('SELECT  COUNT(DISTINCT a.uid)
                                          ' . $this->query);
        } else {
            return $this->lastusercount;
        }
    }

    public function getTotalProfileCount()
    {
        if (is_null($this->lastprofilecount)) {
            $this->requireProfiles();
            $this->buildQuery();
            return (int)XDB::rawFetchOneCell('SELECT  COUNT(DISTINCT p.pid)
                                          ' . $this->query);
        } else {
            return $this->lastprofilecount;
        }
    }

    public function setCondition(PlFilterCondition $cond)
    {
        $this->root =& $cond;
        $this->query = null;
    }

    public function addSort(PlFilterOrder $sort)
    {
        if (count($this->sort) == 0 && $sort instanceof PlFilterGroupableOrder)
        {
            $this->grouper = $sort;
        }
        $this->sort[] = $sort;
        $this->orderby = null;
    }

    public function export()
    {
        $export = array('conditions' => $this->root->export());
        if (!empty($this->sort)) {
            $export['sorts'] = array();
            foreach ($this->sort as $sort) {
                $export['sorts'][] = $sort->export();
            }
        }
        return $export;
    }

    public function exportConditions()
    {
        return $this->root->export();
    }

    public static function fromExport(array $export)
    {
        $export = new PlDict($export);
        if (!$export->has('conditions')) {
            throw new Exception("Cannot build a user filter without conditions");
        }
        $cond = UserFilterCondition::fromExport($export->v('conditions'));
        $sorts = null;
        if ($export->has('sorts')) {
            $sorts = array();
            foreach ($export->v('sorts') as $sort) {
                $sorts[] = UserFilterOrder::fromExport($sort);
            }
        }
        return new UserFilter($cond, $sorts);
    }

    public static function fromJSon($json)
    {
        $export = json_decode($json, true);
        if (is_null($export)) {
            throw new Exception("Invalid json: $json");
        }
        return self::fromExport($json);
    }

    public static function fromExportedConditions(array $export)
    {
        $cond = UserFilterCondition::fromExport($export);
        return new UserFilter($cond);
    }

    public static function fromJSonConditions($json)
    {
        $export = json_decode($json, true);
        if (is_null($export)) {
            throw new Exception("Invalid json: $json");
        }
        return self::fromExportedConditions($json);
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
    public function requireAccounts()
    {
        $this->with_accounts = true;
    }

    public function accountsRequired()
    {
        return $this->with_accounts;
    }

    public function requireProfiles()
    {
        $this->with_profiles = true;
    }

    public function profilesRequired()
    {
        return $this->with_profiles;
    }

    protected function accountJoins()
    {
        $joins = array();
        if ($this->with_profiles && $this->with_accounts) {
            $joins['ap'] = PlSqlJoin::left('account_profiles', '$ME.uid = $UID AND FIND_IN_SET(\'owner\', ap.perms)');
            $joins['p'] = PlSqlJoin::left('profiles', '$PID = ap.pid');
        }
        return $joins;
    }

    /** PERMISSIONS
     */
    private $at = false;
    public function requirePerms()
    {
        $this->requireAccounts();
        $this->at = true;
        return 'at';
    }

    protected function permJoins()
    {
        if ($this->at) {
            return array('at' => PlSqlJoin::left('account_types', '$ME.type = a.type'));
        } else {
            return array();
        }
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
    private $name_tokens = array();
    private $nb_tokens = 0;

    public function addNameTokensFilter($token)
    {
        $this->requireProfiles();
        $sub = 'sn' . (1 + $this->nb_tokens);
        $this->nb_tokens++;
        $this->name_tokens[$sub] = $token;
        return $sub;
    }

    protected function nameTokensJoins()
    {
        /* We don't return joins, since with_sn forces the SELECT to run on search_name first */
        $joins = array();
        foreach ($this->name_tokens as $sub => $token) {
            $joins[$sub] = PlSqlJoin::left('search_name', '$ME.pid = $PID');
        }
        return $joins;
    }

    public function getNameTokens()
    {
        return $this->name_tokens;
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
        return ($grade !== 0) && ($grade == self::GRADE_ING || $grade == self::GRADE_PHD || $grade == self::GRADE_MST);
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

    /** NLS
     */
    private $nls = array();
    public function addNewsLetterFilter($nlid)
    {
        $this->requireAccounts();
        $sub = 'nl_' . $nlid;
        $this->nls[$nlid] = $sub;
        return $sub;
    }

    protected function newsLetterJoins()
    {
        $joins = array();
        foreach ($this->nls as $key => $sub) {
            $joins[$sub] = PlSqlJoin::left('newsletter_ins', '$ME.nlid = {?} AND $ME.uid = $UID', $key);
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
    private $ra = array();
    /** Allows filtering by redirection.
     * @param $email If null, enable a left join on the email redirection table
     *  (email_redirect_account); otherwise, perform a left join on users having
     *  that email as a redirection.
     * @return Suffix to use to access the adequate table.
     */
    public function addEmailRedirectFilter($email = null)
    {
        $this->requireAccounts();
        return $this->register_optional($this->ra, $email);
    }

    const ALIAS_BEST      = 'bestalias';
    const ALIAS_FORLIFE   = 'forlife';
    const ALIAS_AUXILIARY = 'alias_aux';
    private $sa = array();
    /** Allows filtering by source email.
     * @param $email If null, enable a left join on the email source table
     *  (email_source_account); otherwise, perform a left join on users having
     *  that email as a source email.
     * @return Suffix to use to access the adequate table.
     */
     public function addAliasFilter($email = null)
    {
        $this->requireAccounts();
        return $this->register_optional($this->sa, $email);
    }

    protected function emailJoins()
    {
        global $globals;
        $joins = array();
        foreach ($this->ra as $sub => $redirections) {
            if (is_null($redirections)) {
                $joins['ra' . $sub] = PlSqlJoin::left('email_redirect_account', '$ME.uid = $UID AND $ME.type != \'imap\'');
            } else {
                if (!is_array($redirections)) {
                    $key = array($redirections);
                }
                $joins['ra' . $sub] = PlSqlJoin::left('email_redirect_account', '$ME.uid = $UID AND $ME.type != \'imap\'
                                                                                 AND $ME.redirect IN {?}', $redirections);
            }
        }
        foreach ($this->sa as $sub => $emails) {
            if (is_null($emails)) {
                $joins['sa' . $sub] = PlSqlJoin::left('email_source_account', '$ME.uid = $UID');
            } else if ($key == self::ALIAS_BEST) {
                $joins['sa' . $sub] = PlSqlJoin::left('email_source_account', '$ME.uid = $UID AND FIND_IN_SET(\'bestalias\', $ME.flags)');
            } else if ($key == self::ALIAS_FORLIFE) {
                $joins['sa' . $sub] = PlSqlJoin::left('email_source_account', '$ME.uid = $UID AND $ME.type = \'forlife\'');
            } else if ($key == self::ALIAS_AUXILiIARY) {
                $joins['sa' . $sub] = PlSqlJoin::left('email_source_account', '$ME.uid = $UID AND $ME.type = \'alias_aux\'');
            } else {
                if (!is_array($emails)) {
                    $key = array($emails);
                }
                $joins['sa' . $sub] = PlSqlJoin::left('email_source_account', '$ME.uid = $UID AND $ME.email IN {?}', $emails);
            }
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

    const JOB_USERDEFINED = 0x0001;
    const JOB_CV          = 0x0002;
    const JOB_ANY         = 0x0003;

    /** Joins :
     * pj => profile_job
     * pje => profile_job_enum
     * pjt => profile_job_terms
     */
    private $with_pj  = false;
    private $with_pje = false;
    private $with_pjt = 0;

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

    /**
     * Adds a filter on job terms of profile.
     * @param $nb the number of job terms to use
     * @return an array of the fields to filter (one for each term).
     */
    public function addJobTermsFilter($nb = 1)
    {
        $this->with_pjt = $nb;
        $jobtermstable = array();
        for ($i = 1; $i <= $nb; ++$i) {
            $jobtermstable[] = 'pjtr_'.$i;
        }
        return $jobtermstable;
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
        if ($this->with_pjt > 0) {
            for ($i = 1; $i <= $this->with_pjt; ++$i) {
                $joins['pjt_'.$i] = PlSqlJoin::left('profile_job_term', '$ME.pid = $PID');
                $joins['pjtr_'.$i] = PlSqlJoin::left('profile_job_term_relation', '$ME.jtid_2 = pjt_'.$i.'.jtid');
            }
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
    private $mjtr = false;
    const MENTOR = 1;
    const MENTOR_EXPERTISE = 2;
    const MENTOR_COUNTRY = 3;
    const MENTOR_TERM = 4;

    public function addMentorFilter($type)
    {
        $this->requireAccounts();
        switch($type) {
        case self::MENTOR:
            $this->pms['pm'] = 'profile_mentor';
            return 'pm';
        case self::MENTOR_EXPERTISE:
            $this->pms['pme'] = 'profile_mentor';
            return 'pme';
        case self::MENTOR_COUNTRY:
            $this->pms['pmc'] = 'profile_mentor_country';
            return 'pmc';
        case self::MENTOR_TERM:
            $this->pms['pmt'] = 'profile_mentor_term';
            $this->mjtr = true;
            return 'mjtr';
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
        if ($this->mjtr) {
            $joins['mjtr'] = PlSqlJoin::left('profile_job_term_relation', '$ME.jtid_2 = pmt.jtid');
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


    /** PHOTOS
     */
    private $with_photo;
    public function addPhotoFilter()
    {
        $this->requireProfiles();
        $this->with_photo = true;
        return 'photo';
    }

    protected function photoJoins()
    {
        if ($this->with_photo) {
            return array('photo' => PlSqlJoin::left('profile_photos', '$ME.pid = $PID'));
        } else {
            return array();
        }
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

    public function getIds($limit = null)
    {
        return $this->getPIDs();
    }

    public function filter(array $profiles, $limit = null)
    {
        return $this->filterProfiles($profiles, self::defaultLimit($limit));
    }

    public function getTotalCount()
    {
        return $this->getTotalProfileCount();
    }

    public function getGroups()
    {
        return $this->getPIDGroups();
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
