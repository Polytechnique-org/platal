<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

// {{{ abstract class UserFilterOrder
/** Class providing factories for the UserFilterOrders.
 */
abstract class UserFilterOrders
{
    public static function fromExport(array $export)
    {
        $export = new PlDict($export);
        if (!$export->has('type')) {
            throw new Exception("Missing type in export");
        }
        $type = $export->s('type');
        $desc = ($export->s('order') == 'desc');
        switch ($type) {
          case 'promo':
            return new UFO_Promo($export->v('grade'), $desc);

          case 'name':
            return new UFO_Name($desc);

          case 'score':
          case 'registration':
          case 'birthday':
          case 'profile_update':
          case 'death':
          case 'uid':
          case 'hruid':
          case 'pid':
          case 'hrpid':
          case 'is_admin':
            $class = 'UFO_' . str_replace('_', '', $type);
            return new $class($desc);

          default:
            throw new Exception("Unknown order field: $type");
        }
    }
}
// }}}
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
            $sub = $uf->addEducationFilter(true, $this->grade);
            return 'pe' . $sub . '.' . UserFilter::promoYear($this->grade);
        } else {
            $sub = $uf->addDisplayFilter();
            return 'pd' . $sub . '.promo';
        }
    }

    public function getCondition($promo)
    {
        return new UFC_Promo(UserFilterCondition::OP_EQUALS, $this->grade, $promo);
    }

    public function export()
    {
        $export = $this->buildExport('promo');
        if (!is_null($this->grade)) {
            $export['grade'] = $this->grade;
        }
        return $export;
    }
}
// }}}
// {{{ class UFO_Name
/** Sorts users by name
 * @param $desc If sort order should be descending
 */
class UFO_Name extends PlFilterGroupableOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $sub = $uf->addDisplayFilter();
        $token = 'pd.sort_name';
        if ($uf->accountsRequired()) {
            $account_token = Profile::getAccountEquivalentName('sort_name');
            return 'IFNULL(' . $token . ', a.' . $account_token . ')';
        } else {
            return $token;
        }
    }

    public function getGroupToken(PlFilter $pf)
    {
        return 'SUBSTRING(' . $this->_tokens . ', 1, 1)';
    }

    public function getCondition($initial)
    {
        return new UFC_NameInitial($initial);
    }

    public function export()
    {
        $export = $this->buildExport();
        return $export;
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

    public function export()
    {
        return $this->buildExport('score');
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

    public function export()
    {
        return $this->buildExport('registration');
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

    public function export()
    {
        return $this->buildExport('birthday');
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

    public function export()
    {
        return $this->buildExport('profile_update');
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

    public function export()
    {
        return $this->buildExport('death');
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

    public function export()
    {
        return $this->buildExport('uid');
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

    public function export()
    {
        return $this->buildExport('hruid');
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

    public function export()
    {
        return $this->buildExport('pid');
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

    public function export()
    {
        return $this->buildExport('hrpid');
    }
}
// }}}
// {{{ class UFO_IsAdmin
/** Sorts users, putting admins first
 */
class UFO_IsAdmin extends PlFilterOrder
{
    protected function getSortTokens(PlFilter $uf)
    {
        $uf->requireAccounts();
        return 'a.is_admin';
    }

    public function export()
    {
        return $this->buildExport('is_admin');
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
