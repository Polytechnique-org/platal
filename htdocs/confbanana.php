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

require_once("xorg.inc.php");
new_skinned_page('confbanana.tpl', AUTH_MDP);

if (!(isset($_POST["action"]) && isset($_POST["banananame"]) && 
	isset($_POST["bananasig"]) &&  isset($_POST["bananadisplay"]) && 
	isset($_POST["bananamail"]) && isset($_POST["bananaupdate"])
	&& $_POST["action"]=="OK" ))
{
  
    $req = $globals->db->query("
	SELECT  nom,mail,sig,if(FIND_IN_SET('threads',flags),'1','0'),
		IF(FIND_IN_SET('automaj',flags),'1','0') 
	  FROM  forums.profils
	 WHERE  uid='{$_SESSION['uid']}'");
    if (!(list($nom,$mail,$sig,$disp,$maj)=mysql_fetch_row($req))) {
	$nom = $_SESSION["prenom"]." ".$_SESSION["nom"];
	$mail = $_SESSION["forlife"]."@polytechnique.org";
	$sig = $nom." ({$_SESSION['promo']})";
	$disp=0;
	$maj=0;
    }
    $page->assign('nom', $nom);
    $page->assign('mail', $mail);
    $page->assign('sig', $sig);
    $page->assign('disp', $disp);
    $page->assign('maj', $maj);
} else {
  mysql_query("REPLACE INTO forums.profils (uid,sig,mail,nom,flags)
		     VALUES ('{$_SESSION['uid']}','{$_POST['bananasig']}',
			     '{$_POST['bananamail']}','{$_POST['banananame']}',
			     '".($_POST['bananadisplay']?"threads":"").","
			       .($_POST['bananaupdate']?"automaj":"")."')");
}

$page->run();
?>
