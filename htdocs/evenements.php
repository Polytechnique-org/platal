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
        $Id: evenements.php,v 1.3 2004-11-22 11:15:30 x2000habouzit Exp $
 ***************************************************************************/

require("xorg.inc.php");
new_skinned_page('evenements.tpl', AUTH_MDP);

if (isset($_POST['titre'])) {$titre = stripslashes($_POST['titre']);}
else {$titre = "";}

if (isset($_POST['texte'])) {$texte = stripslashes($_POST['texte']);}
else {$texte = "";}

if (isset($_POST['promo_min'])) {$promo_min = $_POST['promo_min'];}
else {$promo_min = 0;}

if (isset($_POST['promo_max'])) {$promo_max = $_POST['promo_max'];}
else {$promo_max = 0;}

if (isset($_POST['peremption'])) {$peremption = $_POST['peremption'];}
else {$peremption = 0;}

if (isset($_POST['validation_message'])) {$validation_message = stripslashes($_POST['validation_message']);}
else {$validation_message = "";}

if (isset($_POST['action'])) { $action = $_POST['action']; } else $action = "" ;

$page->assign('titre', $titre);
$page->assign('texte', $texte);
$page->assign('promo_min', $promo_min);
$page->assign('promo_max', $promo_max);
$page->assign('peremption', $peremption);
$page->assign('validation_message', $validation_message);
$page->assign('action', strtolower($action));

if ($action=="Confirmer") {
    $sql = "INSERT INTO evenements set user_id = {$_SESSION['uid']}"
	.", creation_date = NULL"
	.", titre = '".addslashes($titre)."'"
	.", texte = '".addslashes($texte)."'"
	.", peremption = '".$peremption."'"
	.", promo_min = '$promo_min', promo_max = '$promo_max'"
	.", validation_message = '".addslashes($validation_message)."'"
	.", validation_date = 0";

    if ($res = $globals->db->query($sql)) {
	require("validations.inc.php");
	$evtreq = new evtreq(mysql_insert_id(),$titre,$texte,$promo_min,
		$promo_max,$peremption,$validation_message,$_SESSION['uid']);
	$evtreq->submit();
	$page->assign('ok', true);
    }
}

$select = "";
for ($i = 1 ; $i < 30 ; $i++) {
    $p_stamp=date("Ymd",time()+3600*24*$i);
    $year=substr($p_stamp,0,4);
    $month=substr($p_stamp,4,2);
    $day=substr($p_stamp,6,2);

    $select .= "<option value=\"$p_stamp\"" . (($p_stamp == strtr($peremption, array("-" => ""))) ? " selected" : "")."> $day / $month / $year</option>\n";
}
$page->assign('select',$select);
$page->run();

?>
