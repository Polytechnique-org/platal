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
new_skinned_page('listes/create.tpl', AUTH_MDP);

$owners  = empty($_POST['owners'])  ? Array() : preg_split("/[\r\n]+/",$_POST['owners']);
$members = empty($_POST['members']) ? Array() : preg_split("/[\r\n]+/",$_POST['members']);

if(isset($_POST['desc'])) $_POST['desc'] = stripslashes($_POST['desc']);

if(isset($_POST['add_owner_sub']) && !empty($_POST['add_owner'])) {
    require_once('user.func.inc.php');
    if (($forlife = get_user_forlife($_REQUEST['add_owner'])) === false) {;
        $owners [] = $forlife;
    }
}

if(isset($_POST['add_member_sub']) && !empty($_POST['add_member'])) {
    require_once('user.func.inc.php');
    if (($forlife = get_user_forlife($_REQUEST['add_member'])) === false) {;
        $members[] = $forlife;
    }
}

ksort($owners);	 array_unique($owners);
ksort($members); array_unique($members);

if(isset($_POST['submit'])) {

    if(empty($_POST['liste'])) {
        $page->trig('champs «addresse souhaitée» vide');
    }
    if(!preg_match("/^[a-zA-Z0-9\-]*$/", $_POST['liste'])) {
	$page->trig('le nom de la liste ne doit contenir que des lettres, chiffres et tirets');
    }

    $res = $globals->db->query("SELECT COUNT(*) FROM aliases WHERE alias='{$_POST['liste']}'");
    list($n) = mysql_fetch_row($res);
    mysql_free_result($res);

    if($n) {
        $page->trig('cet alias est déjà pris');
    }

    if(empty($_POST['desc'])) {
        $page->trig('le sujet est vide');
    }
    
    if(!count($owners)) {
        $page->trig('pas de gestionnaire');
    }
    
    if(count($members)<4) {
        $page->trig('pas assez de membres');
    }

    if (!$page->nb_errs()) {
	$page->assign('created', true);
	require_once('validations.inc.php');
	$req = new ListeReq($_SESSION['uid'], $_POST['liste'], $_POST['desc'],
	    $_POST['advertise'], $_POST['modlevel'], $_POST['inslevel'],
	    $owners, $members);
	$req->submit();
    }
}

$page->assign('owners', join("\n",$owners));
$page->assign('members', join("\n",$members));
$page->run();
?>
