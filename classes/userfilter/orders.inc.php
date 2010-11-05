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

// {{{ class UFO_Promo
/** Orders users by promotion
 * @param $grade Formation whose promotion users should be sorted by (restricts results to users of that formation)
 * @param $desc Whether sort is descending
 */
class UFO_Promo extends PlFilterGroupableOrder
{
    private $grade;

    public function __construct($grade = null, $desc = false)
    {
        parent::__construct($desc);
        $this->grade = $grade;
    }

    protected function getSortTokens(PlFilter $uf)
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
class UFO_Name extends PlFilterOrder
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

    protected function getSortTokens(PlFilter $uf)
    {
        if (Profile::isDisplayName($this->type)) {
            $sub = $uf->addDisplayFilter();
            $token = 'pd' . $sub . '.' . $this->type;
            if ($uf->accountsRequired()) {
                $account_token = Profile::getAccountEquivalentName($this->type);
                return 'IFNULL(' . $token . ', a.' . $account_token . ')';
            } else {
                return $token;
            }
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
class UFO_Score extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $toks = $uf->getNameTokens();
        $scores = array();

        // If there weren't any sort tokens, we shouldn't sort by score, sort by NULL instead
        if (count($toks) == 0) {
            return 'NULL';
        }

        foreach ($toks as $sub => $token) {
            $scores[] = XDB::format('SUM(' . $sub . '.score + IF (' . $sub . '.token = {?}, 5, 0) )', $token);
        }
        return implode(' + ', $scores);
    }
}
// }}}

// {{{ class UFO_Registration
/** Sorts users based on registration date
 */
class UFO_Registration extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireAccounts();
        return 'a.registration_date';
    }
}
// }}}

// {{{ class UFO_Birthday
/** Sorts users based on next birthday date
 */
class UFO_Birthday extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireProfiles();
        return 'p.next_birthday';
    }
}
// }}}

// {{{ class UFO_ProfileUpdate
/** Sorts users based on last profile update
 */
class UFO_ProfileUpdate extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireProfiles();
        return 'p.last_change';
    }
}
// }}}

// {{{ class UFO_Death
/** Sorts users based on death date
 */
class UFO_Death extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireProfiles();
        return 'p.deathdate';
    }
}
// }}}

// {{{ class UFO_Uid
/** Sorts users based on their uid
 */
class UFO_Uid extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireAccounts();
        return '$UID';
    }
}
// }}}

// {{{ class UFO_Hruid
/** Sorts users based on their hruid
 */
class UFO_Hruid extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireAccounts();
        return 'a.hruid';
    }
}
// }}}

// {{{ class UFO_Pid
/** Sorts users based on their pid
 */
class UFO_Pid extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireProfiles();
        return '$PID';
    }
}
// }}}

// {{{ class UFO_Hrpid
/** Sorts users based on their hrpid
 */
class UFO_Hrpid extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireProfiles();
        return 'p.hrpid';
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
