<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

function select_options($table, $value, $field, $pad, $where, $join, $group)
{
    $fields = 't.id,' . $field;
    $order = $field;
    if ($group) {
        $fields .= ',' . $group;
        $order = $group . ',' . $order;
    } else {
        $fields .= ', NULL';
    }
    $sql = "SELECT $fields FROM $table AS t $join $where ORDER BY $order";
    $res = XDB::iterRow($sql);
    $sel = ' selected="selected"';

    // An empty entry is added when $pad is true.
    $html = '';
    if ($pad) {
        $html .= '<option value="0"' . ($value == 0 ? $sel : '') . ">&nbsp;</option>\n";
    }
    $optgrp = null;
    while (list($my_id, $my_text, $my_grp) = $res->next()) {
        if ($my_grp != $optgrp) {
            if (!is_null($optgrp)) {
                $html .= '</optgroup>';
            }
            $html .= '<optgroup label="' . pl_entities($my_grp, ENT_QUOTES) . '">';
            $optgrp = $my_grp;
        }
        $html .= sprintf("<option value=\"%s\" %s>%s</option>\n",
                         $my_id, $value == $my_id ? $sel : '', pl_entities($my_text));
    }
    if (!is_null($optgrp)) {
        $html .= '</optgroup>';
    }
    return $html;
}

function smarty_function_select_db_table($params, $smarty) {
    if (empty($params['table']) || !array_key_exists('valeur', $params)) {
        return;
    }
    if (empty($params['champ'])) {
        $params['champ'] = 'text';
    }
    foreach (array('where', 'join', 'group') as $value) {
        if (empty($params[$value])) {
            $params[$value] = '';
        }
    }
    $pad = (!empty($params['pad']) && $params['pad']);

    return select_options($params['table'], $params['valeur'], $params['champ'], $pad,
                          $params['where'], $params['join'], $params['group']);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
