<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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


function select_nat($valeur, $pad=false) {
    $res = XDB::iterRow("SELECT  iso_3166_1_a2 AS id, nationality AS text
                           FROM  geoloc_countries
                          WHERE  nationality IS NOT NULL
                       ORDER BY  nationality");
    $sel = ' selected="selected"';

    // on ajoute une entree vide si $pad est vrai
    $html = "";
    if ($pad) {
	      $html .= sprintf("<option value=\"\"%s>&nbsp;</option>\n", ($valeur ? $sel : ""));
    }
    while (list($my_id, $my_text) = $res->next()) {
        $html .= sprintf("<option value=\"%s\"%s>%s</option>\n", $my_id, ($valeur==$my_id ? $sel : ""), $my_text?$my_text:"&nbsp;");
    }

    return $html;
}

function smarty_function_select_nat($params, $smarty) {
    if (empty($params['pad']) || !($params['pad'])) {
	      $pad = false;
    } else {
	      $pad = true;
    }

    return select_nat($params['valeur'], $pad);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
