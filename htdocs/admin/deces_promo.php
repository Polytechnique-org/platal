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
        $Id: deces_promo.php,v 1.7 2004-11-06 17:37:22 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/deces_promo.tpl');

$promo = ( isset($_REQUEST["promo"]) ? $_REQUEST["promo"] : 0 );
if(isset($_REQUEST['sub10']))   $promo -= 10;
if(isset($_REQUEST['sub01']))   $promo --;
if(isset($_REQUEST['add01']))   $promo ++;
if(isset($_REQUEST['add10']))   $promo += 10;

$page->assign('promo',$promo);

if (isset($_REQUEST["valider"]) && $_REQUEST["valider"] == "Valider") {
    $res = $globals->db->query("SELECT user_id,matricule,deces FROM auth_user_md5 WHERE promo = $promo");
    while (list($uid,$mat,$deces) = mysql_fetch_row($res)) {
	if($_REQUEST[$mat] == $deces) continue;
	$globals->db->query("UPDATE auth_user_md5 SET deces='".$_REQUEST[$mat]."' WHERE matricule = '".$mat."'");
	if($deces=='0000-00-00' or empty($deces)) {
	    require_once('notifs.inc.php');
	    register_watch_op($uid,WATCH_DEATH,$_REQUEST[$mat]);
	}
    }
}

$sql = "SELECT matricule, nom, prenom, deces FROM auth_user_md5 WHERE promo = $promo ORDER BY nom,prenom";
$page->mysql_assign($sql, 'decedes');

$page->run();
?>
