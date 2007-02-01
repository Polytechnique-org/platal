<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require_once 'platal.inc.php';

function select_options($table,$valeur,$champ="text",$pad=false,
                        $where="",$join="",$group="")
{
    $fields = 't.id,' . $champ;
    $order = $champ;
    if ($group) {
        $fields .= ',' . $group;
        $order = $group . ',' . $order; 
    } 
    $sql = "SELECT $fields FROM $table AS t $join $where ORDER BY $order";
    $res = XDB::iterRow($sql);
    $sel = ' selected="selected"';

    // on ajoute une entree vide si $pad est vrai
    $html = "";
    if ($pad) {
        $html.= '<option value="0"'.($valeur==0?$sel:"")."></option>\n";
    }
    $optgrp = null;
    while (list($my_id,$my_text,$my_grp) = $res->next()) {
        if ($my_grp != $optgrp) {
            if (!is_null($optgrp)) {
                $html .= '</optgroup>';
            }
            $html .= '<optgroup label="' . pl_entities($my_grp, ENT_QUOTES) . '">';
            $optgrp = $my_grp;
        }
        $html .= sprintf("<option value=\"%s\" %s>%s</option>\n", 
                         $my_id, ($valeur==$my_id?$sel:""), $my_text);
    }
    if (!is_null($optgrp)) {
        $html .= '</optgroup>';
    }
    return $html;
}

function smarty_function_select_db_table($params, &$smarty) {
    if(empty($params['table']))
        return;
    if(empty($params['champ']))
        $params['champ'] = 'text';
    if(empty($params['pad']) || !($params['pad']))
        $pad = false;
    else
        $pad = true;
    if(empty($params['where']))
        $params['where'] = '';
    return select_options($params['table'], $params['valeur'], $params['champ'], $pad,
                          $params['where'], $params['join'], $params['group']);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
