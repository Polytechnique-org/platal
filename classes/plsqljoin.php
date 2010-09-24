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

// {{{ class PlSqlJoin
class PlSqlJoin
{
    private $mode;
    private $table;
    private $condition;

    const MODE_LEFT  = 'LEFT';
    const MODE_RIGHT = 'RIGHT';
    const MODE_INNER = 'INNER';

    private function __construct($mode, $params)
    {
        $table = array_shift($params);
        $condition = call_user_func_array(array('XDB', 'format'), $params);
        if ($mode != self::MODE_LEFT && $mode != self::MODE_RIGHT && $mode != self::MODE_INNER) {
            Platal::page()->kill("Join mode error: unknown mode $mode");
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
            if (!($join instanceof PlSqlJoin)) {
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

    /** Build a left join
     * @param table The name of the table.
     * @param condition The condition of the jointure
     */
    public static function left()
    {
        $params = func_get_args();
        return new PlSqlJoin(self::MODE_LEFT, $params);
    }

    /** Build a right join
     * @param table The name of the table.
     * @param condition The condition of the jointure
     */
    public static function right()
    {
        $params = func_get_args();
        return new PlSqlJoin(self::MODE_RIGHT, $params);
    }

    /** Build a inner join
     * @param table The name of the table.
     * @param condition The condition of the jointure
     */
    public static function inner()
    {
        $params = func_get_args();
        return new PlSqlJoin(self::MODE_INNER, $params);
    }
}
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
