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
 ***************************************************************************/


function select_secteur($secteur){
    global $globals;
    if ($secteur == '') {
        $secteur = -1;
    }
    $html = "<option value=\"\" ". (($secteur == '')?"selected='selected'":"") .">&nbsp;</option>\n";
    $res  = $globals->xdb->iterRow("SELECT id, label FROM emploi_secteur");
    while (list($tmp_id, $tmp_label) = $res->next()) {
	$html .= "<option value=\"$tmp_id\" " . (($secteur == $tmp_id)?"selected='selected'":"") . ">$tmp_label</option>\n";
    }
    return $html;
}

function select_ss_secteur($secteur,$ss_secteur){
    global $globals;
    if ($secteur) {
	$html = "<option value=\"\">&nbsp;</option>\n";
	$res  = $globals->xdb->iterRow("SELECT id, label FROM emploi_ss_secteur WHERE secteur = {?}", $secteur);
	while (list($tmp_id, $tmp_label) = $res->next()){
	    $html .= "<option value=\"$tmp_id\" ". (($ss_secteur == $tmp_id)?"selected='selected'":"") .">$tmp_label</option>\n";
	}
	return $html;
    }
    else{
	return "<option value=\"\" selected='selected'>&nbsp;</option>\n";
    }
}

//fonctions pour smarty
function _select_secteur_smarty($params){
    return select_secteur($params['secteur']);
}

function _select_ss_secteur_smarty($params){
    return select_ss_secteur($params['secteur'], $params['ss_secteur']);
}
$page->register_function('select_secteur', '_select_secteur_smarty');
$page->register_function('select_ss_secteur', '_select_ss_secteur_smarty');
?>
