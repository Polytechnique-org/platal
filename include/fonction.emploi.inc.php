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
        $Id: fonction.emploi.inc.php,v 1.3 2004-08-31 11:16:48 x2000habouzit Exp $
 ***************************************************************************/


function select_fonction($fonction){
	echo "<option value=\"\" ". (($fonction == '0')?"selected='selected'":"") .">&nbsp;</option>\n";
        $res = mysql_query("SELECT id, fonction_fr, FIND_IN_SET('titre', flags) from fonctions_def ORDER BY id");
	while(list($fid, $flabel, $ftitre) = mysql_fetch_row($res)){
		if($ftitre)
			echo "<option value=\"$fid\" " . (($fonction == $fid)?"selected='selected'":"") . ">$flabel</option>\n";
		else
			echo "<option value=\"$fid\" " . (($fonction == $fid)?"selected='selected'":"") . ">* $flabel</option>\n";
	}
	mysql_free_result($res);
}

function _select_fonction_smarty($params){
  select_fonction($params['fonction']);
}
$page->register_function('select_fonction', '_select_fonction_smarty');
?>
