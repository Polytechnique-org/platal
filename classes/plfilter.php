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

__autoload('xdb');

// {{{ class PlLimit
class PlLimit
{
    private $count = null;
    private $from  = null;

    public function __construct($count = null, $from = null)
    {
        $this->count = $count;
        $this->from  = $from;
    }

    public function getSql()
    {
        if (!is_null($this->count) && $this->count != 0) {
            if (!is_null($this->from) && $this->from != 0) {
                return XDB::format('LIMIT {?}, {?}', (int)$this->from, (int)$this->count);
            } else {
                return XDB::format('LIMIT {?}', (int)$this->count);
            }
        }
        return '';
    }
}
// }}}

// {{{ class PlFilterOrder
/** Base class for ordering results of a query.
 * Parameters for the ordering must be given to the constructor ($desc for a
 *     descending order).
 * The getSortTokens function is used to get actual ordering part of the query.
 */
abstract class PlFilterOrder implements PlExportable
{
    protected $desc = false;
    public function __construct($desc = false)
    {
        $this->desc = $desc;
        $this->_tokens = null;
    }

    public function export()
    {
        throw new Exception("This instance is not exportable");
    }

    public function toggleDesc()
    {
        $this->desc = !$this->desc;
    }

    public function setDescending($desc = true)
    {
        $this->desc = $desc;
    }

    public function buildSort(PlFilter &$pf)
    {
        $sel = $this->getSortTokens($pf);
        $this->_tokens = $sel;
        if (!is_array($sel)) {
            $sel = array($sel);
        }
        if ($this->desc) {
            foreach ($sel as $k => $s) {
                $sel[$k] = $s . ' DESC';
            }
        }
        return $sel;
    }

    /** This function must return the tokens to use for ordering
     * @param &$pf The PlFilter whose results must be ordered
     * @return The name of the field to use for ordering results
     */
    abstract protected function getSortTokens(PlFilter &$pf);
}
// }}}

// {{{ class PlFilterGroupableOrder
/** Extension of a PlFilterOrder, for orders where the value on which ordering
 * is done could be used for grouping results (promo, country, ...)
 */
abstract class PlFilterGroupableOrder extends PlFilterOrder
{
    /** This function will be called when trying to retrieve groups;
     * the returned token will be used to group the values.
     * It will always be called AFTER getSortTokens().
     */
    public function getGroupToken(PlFilter &$pf)
    {
        return $this->_tokens;
    }
}
// }}}

// {{{ class PFO_Random
class PFO_Random extends PlFilterOrder
{
    private $seed = null;

    public function __construct($seed = null, $desc = false)
    {
        parent::__construct($desc);
        $this->seed = $seed;
    }

    protected function getSortTokens(PlFilter &$pf)
    {
        if ($this->seed == null) {
            return 'RAND()';
        } else {
            return XDB::format('RAND({?})', $this->seed);
        }
    }

    public function export()
    {
        $export = array('type' => 'random',);
        if ($this->seed !== null)
            $export['seed'] = $this->seed;
        return $export;
    }
}
// }}}

// {{{ interface PlFilterCondition
interface PlFilterCondition extends PlExportable
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

    public function export()
    {
        return array('child' => $child->export());
    }
}
// }}}

// {{{ class PFC_NChildren
abstract class PFC_NChildren implements PlFilterCondition
{
    protected $children = array();

    public function __construct()
    {
        $this->addChildren(pl_flatten(func_get_args()));
    }

    public function addChildren(array $conds)
    {
        foreach ($conds as &$cond) {
            if (!is_null($cond) && ($cond instanceof PlFilterCondition)) {
                $this->addChild($cond);
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

    public function export()
    {
        $export = array();
        foreach ($this->children as $child)
            $export[] = $child->export();
        return array('children' => $export);
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

    public function export()
    {
        return array('type' => 'true');
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

    public function export()
    {
        return array('type' => 'false');
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

    public function export()
    {
        $export = parent::export();
        $export['type'] = 'not';
        return $export;
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

    public function export() {
        $export = parent::export();
        $export['type'] = 'and';
        return $export;
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

    public function export() {
        $export = parent::export();
        $export['type'] = 'or';
        return $export;
    }
}
// }}}

// {{{ class PlFilter
abstract class PlFilter implements PlExportable
{
    /** Filters objects matching the PlFilter
     * @param $objects The objects to filter
     * @param $limit The portion of the matching objects to show
     */
    public abstract function filter(array $objects, $limit = null);

    public abstract function setCondition(PlFilterCondition &$cond);

    public abstract function addSort(PlFilterOrder &$sort);

    public abstract function getTotalCount();

    /** Whether this PlFilter can return grouped results through
     * $this->getGroups();
     */
    public abstract function hasGroups();

    /** Used to retrieve value/amount resulting from grouping by the first
     * given order.
     */
    public abstract function getGroups();

    /** Get objects, selecting only those within a limit
     * @param $limit The portion of the matching objects to select
     */
    public abstract function get($limit = null);

    /** PRIVATE FUNCTIONS
     */

    /** List of metas to replace in joins:
     * '$COIN' => 'pan.x' means 'replace $COIN with pan.x in the condition of the joins'
     *
     * "$ME" => "joined table alias" is always added to these.
     */
    protected $joinMetas = array();

    protected $joinMethods = array();

    /** Build the 'join' part of the query
     * This function will call all methods declared in self::$joinMethods
     * to get an array of PlSqlJoin objects to merge
     */
    protected function buildJoins()
    {
        $joins = array();
        foreach ($this->joinMethods as $method) {
            $joins = array_merge($joins, $this->$method());
        }
        return PlSqlJoin::formatJoins($joins, $this->joinMetas);
    }

}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
