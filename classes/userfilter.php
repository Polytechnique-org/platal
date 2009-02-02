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

interface UserFilterCondition
{
    /** Check that the given user matches the rule.
     */
    public function buildCondition(UserFilter &$uf);
}

abstract class UFC_OneChild implements UserFilterCondition
{
    protected $child;

    public function setChild(UserFilterCondition &$cond)
    {
        $this->child =& $cond;
    }
}

abstract class UFC_NChildren implements UserFilterCondition
{
    protected $children = array();

    public function addChild(UserFilterCondition &$cond)
    {
        $this->children[] =& $cond;
    }
}

class UFC_True implements UserFilterCondition
{
    public function buildCondition(UserFilter &$uf)
    {
        return 'TRUE';
    }
}

class UFC_False implements UserFilterCondition
{
    public function buildCondition(UserFilter &$uf)
    {
        return 'FALSE';
    }
}

class UFC_Not extends UFC_OneChild
{
    public function buildCondition(UserFilter &$uf)
    {
        return 'NOT (' . $this->child->buildCondition($uf) . ')';
    }
}

class UFC_And extends UFC_NChildren
{
    public function buildCondition(UserFilter &$uf)
    {
        if (empty($this->children)) {
            return 'FALSE';
        } else {
            $conds = array();
            foreach ($this->children as &$child) {
                $conds[] = $child->buildCondition($uf);
            }
            return '(' . implode (') AND (', $conds) . ')';
        }
    }
}

class UFC_Or extends UFC_NChildren
{
    public function buildCondition(UserFilter &$uf)
    {
        if (empty($this->children)) {
            return 'TRUE';
        } else {
            $conds = array();
            foreach ($this->children as &$child) {
                $conds[] = $child->buildCondition($uf);
            }
            return '(' . implode (') OR (', $conds) . ')';
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
        // XXX: Definition of promotion for phds and masters might change in near future.
        if ($this->grade == UserFilter::GRADE_ING) {
            $promo_year = 'entry_year';
        } else {
            $promo_year = 'grad_year';
        }
        $sub = $uf->addEducationFilter(true, $this->grade);
        $field = 'pe' . $sub . '.' . $promo_year;
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
        if (($this->mode & self::VARIANTS) != 0) {
            foreach (UserFilter::$name_variants[$this->type] as $var) {
                $conds[] = $this->buildNameQuery($this->type, $var, $cond, $uf);
            }
        }
        return implode(' OR ', $conds);
    }
}

class UserFilter
{
    private $root;
    private $query = null;

    private function buildQuery()
    {
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
                $str .= ' ON (' . str_replace(array('$ME', '$PID'), array($key, 'p.pid'), $infos[2]) . ')';
            }
            $str .= "\n";
        }
        return $str;
    }

    private function buildJoins()
    {
        $joins = $this->educationJoins() + $this->nameJoins();
        return $this->formatJoin($joins);
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
    public function filter(array $users)
    {
        $output = array();
        foreach ($users as &$user) {
            if ($this->checkUser($user)) {
                $output[] = $user;
            }
        }
        return $output;
    }

    public function setCondition(UserFilterCondition &$cond)
    {
        $this->root =& $cond;
        $this->query = null;
    }

    static public function getLegacy($promo_min, $promo_max)
    {
        $min = null;
        if ($promo_min != 0) {
            $min = new UFC_Promo('>=', self::GRADE_ING, intval($promo_min));
        }
        $max = null;
        if ($promo_max != 0) {
            $max = new UFC_Promo('<=', self::GRADE_ING, intval($promo_max));
        }
        $uf = new UserFilter();
        if (is_null($max) && is_null($min)) {
            $uf->setCondition(new UFC_True());
        } else if (is_null($max)) {
            $uf->setCondition($min);
        } else if (is_null($min)) {
            $uf->setCondition($max);
        } else {
            $cond = new UFC_And();
            $cond->addChild($min);
            $cond->addChild($max);
            $uf->setCondition($cond);
        }
        return $uf;
    }


    /** NAMES
     */
    const LASTNAME  = 'lastname';
    const FIRSTNAME = 'firstname';
    const NICKNAME  = 'nickname';
    const PSEUDONYM = 'pseudonym';
    const NAME      = 'name';
    const VN_MARITAL  = 'marital';
    const VN_ORDINARY = 'ordinary';
    const VN_OTHER    = 'other';
    const VN_INI      = 'ini';

    static public $name_variants = array(
        self::LASTNAME => array(self::VN_MARITAL, self::VN_ORDINARY),
        self::FIRSTNAME => array(self::VN_ORDINARY, self::VN_INI, self::VN_OTHER),
        self::NICKNAME => array(), self::PSEUDONYM => array(),
        self::NAME => array());

    static public function assertName($name)
    {
        if (!Profile::getNameTypeId($name)) {
            Platal::page()->kill('Invalid name type');
        }
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
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
