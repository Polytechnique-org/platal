<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
 ***************************************************************************
 $Id: function.select_nat.php,v 1.1 2004/10/29 01:24:21 x2000habouzit Exp $
 ***************************************************************************/


function select_nat($valeur,$pad=false) {
    global $globals;
    $sql = "SELECT a2 AS id,IF(nat='',pays,nat) AS text FROM geoloc_pays ORDER BY text";
    $result = $globals->db->query($sql);

    $sel = ' selected="selected"';

    // on ajoute une entree vide si $pad est vrai
    $html = "";
    if ($pad)
	$html.= '<option value="0"'.($valeur==0?$sel:"")."></option>\n";
    while (list($my_id,$my_text) = mysql_fetch_row($result)) {
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n",$my_id,($valeur==$my_id?$sel:""),$my_text);
    }
    mysql_free_result($result);
    return $html;
}

function smarty_function_select_nat($params, &$smarty) {
    if(empty($params['pad']) || !($params['pad']))
	$pad = false;
    else
	$pad = true;
    return select_nat($params['valeur'], $pad);
}

?>
