<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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


function select_options($table,$valeur,$champ="text",$pad=false,$where="") {
    $sql = "SELECT id,$champ FROM $table $where ORDER BY $champ";
    $res = XDB::iterRow($sql);
    $sel = ' selected="selected"';

    // on ajoute une entree vide si $pad est vrai
    $html = "";
    if ($pad) {
	$html.= '<option value="0"'.($valeur==0?$sel:"")."></option>\n";
    }
    while (list($my_id,$my_text) = $res->next()) {
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n", 
                         $my_id, ($valeur==$my_id?$sel:""), $my_text);
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
    return select_options($params['table'], $params['valeur'],
                          $params['champ'], $pad, $params['where']);
}

?>
