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
        if (!is_null($this->count)) {
            if (!is_null($this->from) && $this->from != 0) {
                return XDB::format('LIMIT {?}, {?}', (int)$this->from, (int)$this->count);
            } else {
                return XDB::format('LIMIT {?}', (int)$this->count);
            }
        }
        return '';
    }
}

class PlSqlJoin
{
    private $mode;
    private $table;
    private $condition;

    const MODE_LEFT  = 'LEFT';
    const MODE_RIGHT = 'RIGHT';
    const MODE_INNER = 'INNER';

    public function __construct($mode, $table, $condition)
    {
        if ($mode != self::MODE_LEFT && $mode != self::MODE_RIGHT && $mode != self::MODE_INNER) {
            Platal::page()->kill("Join mode error : unknown mode $mode");
            return;
        }
        $this->mode = $mode;
        $this->table = $table;
        $this->condition = $condition;
    }

    public function mode()
    {
        return $this->mode;
    }

    public function table()
    {
        return $this->table;
    }

    public function condition()
    {
        return $this->condition;
    }

    /** Replace all "metas" in the condition with their translation.
     * $ME always becomes the alias of the table
     * @param $key The name the joined table will have in the final query
     * @param $joinMetas An array of meta => conversion to apply to the condition
     */
    public function replaceJoinMetas($key, $joinMetas = array())
    {
        $joinMetas['$ME'] = $key;
        return str_replace(array_keys($joinMetas), $joinMetas, $this->condition);
    }

    /** Create a join command from an array of PlSqlJoin
     * @param $joins The list of 'join' to convert into an SQL query
     * @param $joinMetas An array of ('$META' => 'conversion') to apply to the joins.
     */
    public static function formatJoins(array $joins, array $joinMetas)
    {
        $str = '';
        foreach ($joins as $key => $join) {
            if (! ($join instanceof PlSqlJoin)) {
                Platal::page()->kill("Error: not a join: $join");
            }
            $mode  = $join->mode();
            $table = $join->table();
            $str .= ' ' . $mode . ' JOIN ' . $table . ' AS ' . $key;
            if ($join->condition() != null) {
                $str .= ' ON (' . $join->replaceJoinMetas($key, $joinMetas) . ')';
            }
            $str .= "\n";
        }
        return $str;
    }
}

abstract class PlFilter
{
    /** Filters objects matching the PlFilter
     * @param $objects The objects to filter
     * @param $limit The portion of the matching objects to show
     */
    public abstract function filter(array $objects, PlLimit &$limit);

    public abstract function setCondition(PlFilterCondition &$cond);

    public abstract function addSort(PlFilterOrder &$sort);

    public abstract function getTotalCount();

    /** Get objects, selecting only those within a limit
     * @param $limit The portion of the matching objects to select
     */
    public abstract function get(PlLimit &$limit);

    /** PRIVATE FUNCTIONS
     */

    /** List of metas to replace in joins:
     * '$COIN' => 'pan.x' means 'replace $COIN with pan.x in the condition of the joins'
     *
     * "$ME" => "joined table alias" is always added to these.
     */
    private $joinMetas = array();

    /** Build the 'join' part of the query
     * This function will call all methods declared in self::$joinMethods
     * to get an array of PlSqlJoin objects to merge
     */
    private function buildJoins()
    {
        $joins = array();
        foreach ($this->$joinMethods as $method) {
            $joins = array_merge($joins, $this->$method());
        }
        return PlSqlJoin::formatJoins($joins, $this->$joinMetas);
    }

}

?>
