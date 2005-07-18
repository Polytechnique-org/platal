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

function select_fonction($fonction){
    global $globals;
    $html = "<option value='' ". (($fonction == '0')?"selected='selected'":"") .">&nbsp;</option>\n";

    $res = $globals->xdb->iterRow("SELECT id, fonction_fr, FIND_IN_SET('titre', flags) from fonctions_def ORDER BY id");
    while(list($fid, $flabel, $ftitre) = $res->next()){
	if($ftitre)
	    $html.= "<option value='$fid' " . (($fonction == $fid)?"selected='selected'":"") . ">$flabel</option>\n";
	else
	    $html .= "<option value=\"$fid\" " . (($fonction == $fid)?"selected='selected'":"") . ">* $flabel</option>\n";
    }
    return $html;
}

function _select_fonction_smarty($params){
    return select_fonction($params['fonction']);
}

$page->register_function('select_fonction', '_select_fonction_smarty');
?>
