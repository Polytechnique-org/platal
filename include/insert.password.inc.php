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
        $Id: insert.password.inc.php,v 1.5 2004-10-10 09:09:34 x2000chevalier Exp $
 ***************************************************************************/

function smarty_insert_getName() {
    global $globals;
    if(empty($_COOKIE['ORGuid'])) return "";
    $res = $globals->db->query("SELECT prenom FROM auth_user_md5 WHERE user_id='{$_COOKIE['ORGuid']}'");
    if(list($prenom) = mysql_fetch_row($res)) {
	mysql_free_result($res);
	return $prenom;
    }
    return "";
}

function smarty_insert_getUsername() {
    global $globals;
    if(isset($_COOKIE['ORGuid'])) $id = $_COOKIE['ORGuid'];
    if(isset($_SESSION['uid'])) $id = $_SESSION['uid'];
    if(empty($id)) return "";
    $res = $globals->db->query("SELECT alias FROM aliases WHERE id='$id' AND (type='a_vie' OR type='alias' OR type='epouse') ORDER BY type!='epouse', LENGTH(alias) LIMIT 1");
    if(list($uname) = mysql_fetch_row($res)) {
	mysql_free_result($res);
	return $uname;
    }
    return "";
}
?>
