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

// {{{ interface PlFilterCondition
interface PlFilterCondition
{
    const COND_TRUE  = 'TRUE';
    const COND_FALSE = 'FALSE';

    public function buildCondition(PlFilter &$pf);
}
// }}}

// {{{ class PFC_OneChild
abstract class PFC_OneChild implements PlFilterCondition
{
    protected $child;

    public function __construct(&$child = null)
    {
        if (!is_null($child) && ($child instanceof PlFilterCondition)) {
            $this->setChild($child);
        }
    }

    public function setChild(PlFilterCondition &$cond)
    {
        $this->child =& $cond;
    }
}
// }}}

// {{{ class PFC_NChildren
abstract class PFC_NChildren implements PlFilterCondition
{
    protected $children = array();

    public function __construct()
    {
        $children = func_get_args();
        foreach ($children as &$child) {
            if (!is_null($child) && ($child instanceof PlFilterCondition)) {
                $this->addChild($child);
            }
        }
    }

    public function addChild(PlFilterCondition &$cond)
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
// }}}

// {{{ class PFC_True
class PFC_True implements PlFilterCondition
{
    public function buildCondition(PlFilter &$uf)
    {
        return self::COND_TRUE;
    }
}
// }}}

// {{{ class PFC_False
class PFC_False implements PlFilterCondition
{
    public function buildCondition(PlFilter &$uf)
    {
        return self::COND_FALSE;
    }
}
// }}}

// {{{ class PFC_Not
class PFC_Not extends PFC_OneChild
{
    public function buildCondition(PlFilter &$uf)
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
// }}}

// {{{ class PFC_And
class PFC_And extends PFC_NChildren
{
    public function buildCondition(PlFilter &$uf)
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
// }}}

// {{{ class PFC_Or
class PFC_Or extends PFC_NChildren
{
    public function buildCondition(PlFilter &$uf)
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
// }}}
?>
