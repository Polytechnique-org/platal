<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

interface UserFilterCondition
{
    const COND_TRUE  = 'TRUE';
    const COND_FALSE = 'FALSE';

    /** Check that the given user matches the rule.
     */
    public function buildCondition(UserFilter &$uf);
}

abstract class UFC_OneChild implements UserFilterCondition
{
    protected $child;

    public function __construct($child = null)
    {
        if (!is_null($child) && ($child instanceof UserFilterCondition)) {
            $this->setChild($child);
        }
    }

    public function setChild(UserFilterCondition &$cond)
    {
        $this->child =& $cond;
    }
}

abstract class UFC_NChildren implements UserFilterCondition
{
    protected $children = array();

    public function __construct()
    {
        $children = func_get_args();
        foreach ($children as &$child) {
            if (!is_null($child) && ($child instanceof UserFilterCondition)) {
                $this->addChild($child);
            }
        }
    }

    public function addChild(UserFilterCondition &$cond)
    {
        $this->children[] =& $cond;
    }

    protected function catConds(array $cond, $op, $fallback)
    {
        if (count($cond) == 0) {
            return $fallback;
        } else if (count($cond) == 1) {
            return $cond[0];
        } else {
            return '(' . implode(') ' . $op . ' (', $cond) . ')';
        }
    }
}

class UFC_True implements UserFilterCondition
{
    public function buildCondition(UserFilter &$uf)
    {
        return self::COND_TRUE;
    }
}

class UFC_False implements UserFilterCondition
{
    public function buildCondition(UserFilter &$uf)
    {
        return self::COND_FALSE;
    }
}

class UFC_Not extends UFC_OneChild
{
    public function buildCondition(UserFilter &$uf)
    {
        $val = $this->child->buildCondition($uf);
        if ($val == self::COND_TRUE) {
            return self::COND_FALSE;
        } else if ($val == self::COND_FALSE) {
            return self::COND_TRUE;
        } else {
            return 'NOT (' . $val . ')';
        }
    }
}

class UFC_And extends UFC_NChildren
{
    public function buildCondition(UserFilter &$uf)
    {
        if (empty($this->children)) {
            return self::COND_FALSE;
        } else {
            $true = self::COND_FALSE;
            $conds = array();
            foreach ($this->children as &$child) {
                $val = $child->buildCondition($uf);
                if ($val == self::COND_TRUE) {
                    $true = self::COND_TRUE;
                } else if ($val == self::COND_FALSE) {
                    return self::COND_FALSE;
                } else {
                    $conds[] = $val;
                }
            }
            return $this->catConds($conds, 'AND', $true);
        }
    }
}

class UFC_Or extends UFC_NChildren
{
    public function buildCondition(UserFilter &$uf)
    {
        if (empty($this->children)) {
            return self::COND_TRUE;
        } else {
            $true = self::COND_TRUE;
            $conds = array();
            foreach ($this->children as &$child) {
                $val = $child->buildCondition($uf);
                if ($val == self::COND_TRUE) {
                    return self::COND_TRUE;
                } else if ($val == self::COND_FALSE) {
                    $true = self::COND_FALSE;
                } else {
                    $conds[] = $val;
                }
            }
            return $this->catConds($conds, 'OR', $true);
        }
    }
}

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
        UserFilter::assertGrade($this->grade);
    }

    public function buildCondition(UserFilter &$uf)
    {
        $sub = $uf->addEducationFilter(true, $this->grade);
        $field = 'pe' . $sub . '.' . UserFilter::promoYear($this->grade);
        return $field . ' IS NOT NULL AND ' . $field . ' ' . $this->comparison . ' ' . XDB::format('{?}', $this->promo);
    }
}

class UFC_Name implements UserFilterCondition
{
    const PREFIX   = 1;
    const SUFFIX   = 2;
    const PARTICLE = 7;
    const VARIANTS = 8;
    const CONTAINS = 3;

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

    public function buildCondition(UserFilter &$uf)
    {
        $left = '$ME.name';
        $op   = ' LIKE ';
        if (($this->mode & self::PARTICLE) == self::PARTICLE) {
            $left = 'CONCAT($ME.particle, \' \', $ME.name)';
        }
        if (($this->mode & self::CONTAINS) == 0) {
            $right = XDB::format('{?}', $this->text);
            $op    = ' = ';
        } else if (($this->mode & self::CONTAINS) == self::PREFIX) {
            $right = XDB::format('CONCAT({?}, \'%\')', $this->text);
        } else if (($this->mode & self::CONTAINS) == self::SUFFIX) {
            $right = XDB::format('CONCAT(\'%\', {?})', $this->text);
        } else {
            $right = XDB::format('CONCAT(\'%\', {?}, \'%\')', $this->text);
        }
        $cond = $left . $op . $right;
        $conds = array($this->buildNameQuery($this->type, null, $cond, $uf));
        if (($this->mode & self::VARIANTS) != 0 && isset(UserFilter::$name_variants[$this->type])) {
            foreach (UserFilter::$name_variants[$this->type] as $var) {
                $conds[] = $this->buildNameQuery($this->type, $var, $cond, $uf);
            }
        }
        return implode(' OR ', $conds);
    }
}

class UFC_Dead implements UserFilterCondition
{
    private $dead;
    public function __construct($dead)
    {
        $this->dead = $dead;
    }

    public function buildCondition(UserFilter &$uf)
    {
        if ($this->dead) {
            return 'p.deathdate IS NOT NULL';
        } else {
            return 'p.deathdate IS NULL';
        }
    }
}

class UFC_Registered implements UserFilterCondition
{
    private $active;
    public function __construct($active = false)
    {
        $this->only_active = $active;
    }

    public function buildCondition(UserFilter &$uf)
    {
        if ($this->active) {
            return 'a.uid IS NOT NULL AND a.state = \'active\'';
        } else {
            return 'a.uid IS NOT NULL AND a.state != \'pending\'';
        }
    }
}

class UFC_Sex implements UserFilterCondition
{
    private $sex;
    public function __construct($sex)
    {
        $this->sex = $sex;
    }

    public function buildCondition(UserFilter &$uf)
    {
        if ($this->sex != User::GENDER_MALE && $this->sex != User::GENDER_FEMALE) {
            return self::COND_FALSE;
        } else {
            return XDB::format('p.sex = {?}', $this->sex == User::GENDER_FEMALE ? 'female' : 'male');
        }
    }
}

class UFC_Group implements UserFilterCondition
{
    private $group;
    private $admin;
    public function __construct($group, $admin = false)
    {
        $this->group = $group;
        $this->admin = $admin;
    }

    public function buildCondition(UserFilter &$uf)
    {
        $sub = $uf->addGroupFilter($this->group);
        $where = 'gpm' . $sub . '.perms IS NOT NULL';
        if ($this->admin) {
            $where .= ' AND gpm' . $sub . '.perms = \'admin\'';
        }
        return $where;
    }
}



/******************
 * ORDERS
 ******************/

abstract class UserFilterOrder
{
    protected $desc = false;

    public function buildSort(UserFilter &$uf)
    {
        $sel = $this->getSortTokens($uf);
        if (!is_array($sel)) {
            $sel = array($sel);
        }
        if ($this->desc) {
            foreach ($sel as $k=>$s) {
                $sel[$k] = $s . ' DESC';
            }
        }
        return $sel;
    }

    abstract protected function getSortTokens(UserFilter &$uf);
}

class UFO_Promo extends UserFilterOrder
{
    private $grade;

    public function __construct($grade = null, $desc = false)
    {
        $this->grade = $grade;
        $this->desc  = $desc;
    }

    protected function getSortTokens(UserFilter &$uf)
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

class UFO_Name extends UserFilterOrder
{
    private $type;
    private $variant;
    private $particle;

    public function __construct($type, $variant = null, $particle = false, $desc = false)
    {
        $this->type = $type;
        $this->variant = $variant;
        $this->particle = $particle;
        $this->desc = $desc;
    }

    protected function getSortTokens(UserFilter &$uf)
    {
        if (UserFilter::isDisplayName($this->type)) {
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

/***********************************
  *********************************
          USER FILTER CLASS
  *********************************
 ***********************************/

class UserFilter
{
    static private $joinMethods = array();

    private $root;
    private $sort = array();
    private $query = null;
    private $orderby = null;

    private $lastcount = 0;

    public function __construct($cond = null, $sort = null)
    {
        if (empty(self::$joinMethods)) {
            $class = new ReflectionClass('UserFilter');
            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if (substr($name, -5) == 'Joins' && $name != 'buildJoins') {
                    self::$joinMethods[] = $name;
                }
            }
        }
        if (!is_null($cond)) {
            if ($cond instanceof UserFilterCondition) {
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
            $joins = $this->buildJoins();
            $this->query = 'FROM  accounts AS a
                      INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET(\'owner\', ap.perms))
                      INNER JOIN  profiles AS p ON (p.pid = ap.pid)
                               ' . $joins . '
                           WHERE  (' . $where . ')';
        }
    }

    private function formatJoin(array $joins)
    {
        $str = '';
        foreach ($joins as $key => $infos) {
            $mode  = $infos[0];
            $table = $infos[1];
            if ($mode == 'inner') {
                $str .= 'INNER JOIN ';
            } else if ($mode == 'left') {
                $str .= 'LEFT JOIN ';
            } else {
                Platal::page()->kill("Join mode error");
            }
            $str .= $table . ' AS ' . $key;
            if (isset($infos[2])) {
                $str .= ' ON (' . str_replace(array('$ME', '$PID', '$UID'), array($key, 'p.pid', 'a.uid'), $infos[2]) . ')';
            }
            $str .= "\n";
        }
        return $str;
    }

    private function buildJoins()
    {
        $joins = array();
        foreach (self::$joinMethods as $method) {
            $joins = array_merge($joins, $this->$method());
        }
        return $this->formatJoin($joins);
    }

    private function getUIDList($uids = null, $count = null, $offset = null)
    {
        $this->buildQuery();
        $limit = '';
        if (!is_null($count)) {
            if (!is_null($offset)) {
                $limit = XDB::format('LIMIT {?}, {?}', $offset, $count);
            } else {
                $limit = XDB::format('LIMIT {?}', $count);
            }
        }
        $cond = '';
        if (!is_null($uids)) {
            $cond = ' AND a.uid IN (' . implode(', ', $uids) . ')';
        }
        $fetched = XDB::fetchColumn('SELECT SQL_CALC_FOUND_ROWS  a.uid
                                    ' . $this->query . $cond . '
                                   GROUP BY  a.uid
                                    ' . $this->orderby . '
                                    ' . $limit);
        $this->lastcount = (int)XDB::fetchOneCell('SELECT FOUND_ROWS()');
        return $fetched;
    }

    /** Check that the user match the given rule.
     */
    public function checkUser(PlUser &$user)
    {
        $this->buildQuery();
        $count = (int)XDB::fetchOneCell('SELECT  COUNT(*)
                                        ' . $this->query . XDB::format(' AND a.uid = {?}', $user->id()));
        return $count == 1;
    }

    /** Filter a list of user to extract the users matching the rule.
     */
    public function filter(array $users, $count = null, $offset = null)
    {
        $this->buildQuery();
        $table = array();
        $uids  = array();
        foreach ($users as $user) {
            $uids[] = $user->id();
            $table[$user->id()] = $user;
        }
        $fetched = $this->getUIDList($uids, $count, $offset);
        $output = array();
        foreach ($fetched as $uid) {
            $output[] = $table[$uid];
        }
        return $output;
    }

    public function getUIDs($count = null, $offset = null)
    {
        return $this->getUIDList(null, $count, $offset);
    }

    public function getUsers($count = null, $offset = null)
    {
        return User::getBulkUsersWithUIDs($this->getUIDs($count, $offset));
    }

    public function getTotalCount()
    {
        return $this->lastcount;
    }

    public function setCondition(UserFilterCondition &$cond)
    {
        $this->root =& $cond;
        $this->query = null;
    }

    public function addSort(UserFilterOrder &$sort)
    {
        $this->sort[] = $sort;
        $this->orderby = null;
    }

    static public function getLegacy($promo_min, $promo_max)
    {
        if ($promo_min != 0) {
            $min = new UFC_Promo('>=', self::GRADE_ING, intval($promo_min));
        } else {
            $min = new UFC_True();
        }
        if ($promo_max != 0) {
            $max = new UFC_Promo('<=', self::GRADE_ING, intval($promo_max));
        } else {
            $max = new UFC_True();
        }
        return new UserFilter(new UFC_And($min, $max));
    }


    /** DISPLAY
     */
    private $pd = false;
    public function addDisplayFilter()
    {
        $this->pd = true;
        return '';
    }

    private function displayJoins()
    {
        if ($this->pd) {
            return array('pd' => array('left', 'profile_display', '$ME.pid = $PID'));
        } else {
            return array();
        }
    }

    /** NAMES
     */
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

    static public $name_variants = array(
        self::LASTNAME => array(self::VN_MARITAL, self::VN_ORDINARY),
        self::FIRSTNAME => array(self::VN_ORDINARY, self::VN_INI, self::VN_OTHER)
    );

    static public function assertName($name)
    {
        if (!Profile::getNameTypeId($name)) {
            Platal::page()->kill('Invalid name type');
        }
    }

    static public function isDisplayName($name)
    {
        return $name == self::DN_FULL || $name == self::DN_DISPLAY
            || $name == self::DN_YOURSELF || $name == self::DN_DIRECTORY
            || $name == self::DN_PRIVATE || $name == self::DN_PUBLIC
            || $name == self::DN_SHORT || $name == self::DN_SORT;
    }

    private $pn  = array();
    private $pno = 0;
    public function addNameFilter($type, $variant = null)
    {
        if (!is_null($variant)) {
            $ft  = $type . '_' . $variant;
        } else {
            $ft = $type;
        }
        $sub = '_' . $ft;
        self::assertName($ft);

        if (!is_null($variant) && $variant == 'other') {
            $sub .= $this->pno++;
        }
        $this->pn[$sub] = Profile::getNameTypeId($ft);
        return $sub;
    }

    private function nameJoins()
    {
        $joins = array();
        foreach ($this->pn as $sub => $type) {
            $joins['pn' . $sub] = array('left', 'profile_name', '$ME.pid = $PID AND $ME.typeid = ' . $type);
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
        return $grade == self::GRADE_ING || self::$grade == GRADE_PHD || self::$grade == GRADE_MST;
    }

    static public function assertGrade($grade)
    {
        if (!self::isGrade($grade)) {
            Platal::page()->killError("Diplôme non valide");
        }
    }

    static public function promoYear($grade)
    {
        // XXX: Definition of promotion for phds and masters might change in near future.
        return ($grade == UserFilter::GRADE_ING) ? 'entry_year' : 'grad_year';
    }

    private $pepe     = array();
    private $with_pee = false;
    private $pe_g     = 0;
    public function addEducationFilter($x = false, $grade = null)
    {
        if (!$x) {
            $index = $this->pe_g;
            $sub   = $this->pe_g++;
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

    private function educationJoins()
    {
        $joins = array();
        if ($this->with_pee) {
            $joins['pee'] = array('inner', 'profile_education_enum', 'pee.abbreviation = \'X\'');
        }
        foreach ($this->pepe as $grade => $sub) {
            if ($this->isGrade($grade)) {
                $joins['pe' . $sub] = array('left', 'profile_education', '$ME.eduid = pee.id AND $ME.uid = $PID');
                $joins['pede' . $sub] = array('inner', 'profile_education_degree_enum', '$ME.id = pe' . $sub . '.degreeid AND $ME.abbreviation LIKE ' .
                                                  XDB::format('{?}', $grade));
            } else {
                $joins['pe' . $sub] = array('left', 'profile_education', '$ME.uid = $PID');
                $joins['pee' . $sub] = array('inner', 'profile_education_enum', '$ME.id = pe' . $sub . '.eduid');
                $joins['pede' . $sub] = array('inner', 'profile_education_degree_enum', '$ME.id = pe' . $sub . '.degreeid');
            }
        }
        return $joins;
    }


    /** GROUPS
     */
    private $gpm = array();
    private $gpm_o = 0;
    public function addGroupFilter($group = null)
    {
        if (!is_null($group)) {
            if (ctype_digit($group)) {
                $index = $sub = $group;
            } else {
                $index = $group;
                $sub   = preg_replace('/[^a-z0-9]/i', '', $group);
            }
        } else {
            $sub = 'group_' . $this->gpm_o++;
            $index = null;
        }
        $sub = '_' . $sub;
        $this->gpm[$sub] = $index;
        return $sub;
    }

    private function groupJoins()
    {
        $joins = array();
        foreach ($this->gpm as $sub => $key) {
            if (is_null($key)) {
                $joins['gpa' . $sub] = array('inner', 'groupex.asso');
                $joins['gpm' . $sub] = array('left', 'groupex.membres', '$ME.uid = $UID AND $ME.asso_id = gpa' . $sub . '.id');
            } else if (ctype_digit($key)) {
                $joins['gpm' . $sub] = array('left', 'groupex.membres', '$ME.uid = $UID AND $ME.asso_id = ' . $key);
            } else {
                $joins['gpa' . $sub] = array('inner', 'groupex.asso', XDB::format('$ME.diminutif = {?}', $key));
                $joins['gpm' . $sub] = array('left', 'groupex.membres', '$ME.uid = $UID AND $ME.asso_id = gpa' . $sub . '.id');
            }
        }
        return $joins;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
