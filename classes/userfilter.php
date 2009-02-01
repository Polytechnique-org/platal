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
    public function check(PlUser &$user);
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
    public function check(PlUser &$user)
    {
        return true;
    }
}

class UFC_False implements UserFilterCondition
{
    public function check(PlUser &$user)
    {
        return false;
    }
}

class UFC_Not extends UFC_OneChild
{
    public function check(PlUser &$user)
    {
        return !$this->child->child($user);
    }
}

class UFC_And extends UFC_NChildren
{
    public function check(PlUser &$user)
    {
        foreach ($this->children as &$cond) {
            if (!$cond->check($user)) {
                return false;
            }
        }
        return true;
    }
}

class UFC_Or extends UFC_NChildren
{
    public function check(PlUser &$user)
    {
        foreach ($this->children as &$cond) {
            if ($cond->check($user)) {
                return true;
            }
        }
        return false;
    }
}

class UFC_Promo implements UserFilterCondition
{
    const GRADE_ING = 'Ing.';
    const GRADE_PHD = 'PhD';
    const GRADE_MST = 'M%';

    private $grade;
    private $promo;
    private $comparison;

    public function __construct($comparison, $grade, $promo)
    {
        $this->grade = $grade;
        $this->comparison = $comparison;
        $this->promo = $promo;
    }

    public function check(PlUser &$user)
    {
        if (!$user->hasProfile()) {
            return false;
        }
        // XXX: Definition of promotion for phds and masters might change in near future.
        if ($this->grade == self::GRADE_ING) {
            $promo_year = 'entry_year';
        } else {
            $promo_year = 'grad_year';
        }
        $req = XDB::fetchOneCell('SELECT  COUNT(pe.id)
                                    FROM  profile_education AS pe
                              INNER JOIN  profile_education_degree_enum AS pede ON (pe.degreeid = pede.id AND pede.abbreviation LIKE {?})
                              INNER JOIN  profile_education_enum AS pee ON (pe.eduid = pee.id AND pee.abbreviation = \'X\')
                                   WHERE  pe.' . $promo_year . ' ' . $this->comparison . ' {?} AND pe.uid = {?}',
                                 $this->grade, $this->promo, $user->profile()->id());
        return intval($req) > 0;
    }
}

class UserFilter
{
    private $root;

    /** Check that the user match the given rule.
     */
    public function checkUser(PlUser &$user)
    {
        return $this->root->check($user);
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
    }


    static public function getLegacy($promo_min, $promo_max)
    {
        $min = null;
        if ($promo_min != 0) {
            $min = new UFC_Promo('>=', UFC_Promo::GRADE_ING, intval($promo_min));
        }
        $max = null;
        if ($promo_max != 0) {
            $max = new UFC_Promo('<=', UFC_Promo::GRADE_ING, intval($promo_max));
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
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
