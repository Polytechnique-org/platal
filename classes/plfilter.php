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

    public function condiftion()
    {
        return $this->condition;
    }
}

abstract class PlFilter
{
    public abstract function filter(array $objects, $count = null, $offset = null);

    public abstract function setCondition(PlFilterCondition &$cond);

    public abstract function addSort(PlFilterOrder &$sort);

    private function replaceJoinMetas($cond, $key)
    {
        return str_replace(array('$ME'), array($key), $cond);
    }

    private function formatJoin(array $joins)
    {
        $str = '';
        foreach ($joins as $key => $join) {
            if (! $join instanceof PlSqlJoin) {
                Platal::page()->kill("Error : not a join : $join");
            }
            $mode  = $join->mode();
            $table = $join->table();
            $str .= ' ' . $mode . ' JOIN ' . $table . ' AS ' . $key;
            if ($join->condition() != null) {
                $str .= ' ON (' . $this->replaceJoinMetas($join->condition(), $key) . ')';
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

}

?>
