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
        $Id: create.php,v 1.5 2004-11-22 11:15:39 x2000habouzit Exp $
 ***************************************************************************/

require("xorg.inc.php");
new_skinned_page('listes/create.tpl', AUTH_MDP);

$owners  = empty($_POST['owners'])  ? Array() : preg_split("/[\r\n]+/",$_POST['owners']);
$members = empty($_POST['members']) ? Array() : preg_split("/[\r\n]+/",$_POST['members']);

if(isset($_POST['desc'])) $_POST['desc'] = stripslashes($_POST['desc']);

if(isset($_POST['add_owner_sub']) && isset($_POST['add_owner'])) {
    $res = $globals->db->query("
	SELECT  a.alias AS forlife
          FROM  aliases         AS a
    INNER JOIN	aliases         AS b USING(id)
         WHERE  b.alias='{$_POST['add_owner']}' AND b.type!='homonyme' AND a.type='a_vie'");
    if(list($forlife) = mysql_fetch_row($res)) {
	$owners [] = $forlife;
    }
    mysql_free_result($res);
}

if(isset($_POST['add_member_sub']) && isset($_POST['add_member'])) {
    $res = $globals->db->query("
	SELECT  a.alias AS forlife
          FROM  aliases         AS a
    INNER JOIN	aliases         AS b USING(id)
         WHERE  b.alias='{$_POST['add_member']}' AND b.type!='homonyme' AND a.type='a_vie'");
    if(list($forlife) = mysql_fetch_row($res)) {
	$members[] = $forlife;
    }
    mysql_free_result($res);
}

ksort($owners);	 array_unique($owners);
ksort($members); array_unique($members);

if(isset($_POST['submit'])) {
    $err = Array();

    if(empty($_POST['liste'])) $err[] = 'champs «addresse souhaitée» vide';
    if(!preg_match("/^[a-zA-Z0-9\-]*$/", $_POST['liste']))
	$err = 'le nom de la liste ne doit contenir que des lettres, chiffres et tirets';

    $res = $globals->db->query("SELECT COUNT(*) FROM aliases WHERE alias='{$_POST['liste']}'");
    list($n) = mysql_fetch_row($res);
    mysql_free_result($res);
    if($n) $err[] = 'cet alias est déjà pris';

    if(empty($_POST['desc'])) $err[] = 'le sujet est vide';
    if(!count($owners)) $err[] = 'pas de gestionnaire';
    if(count($members)<4) $err[] = 'pas assez de membres';

    if(!count($err)) {
	$page->assign('created', true);
	require('validations.inc.php');
	$req = new ListeReq($_SESSION['uid'], $_POST['liste'], $_POST['desc'],
	    $_POST['advertise'], $_POST['modlevel'], $_POST['inslevel'],
	    $owners, $members);
	$req->submit();
    } else {
	$page->assign('err', $err);
    }
}

$page->assign('owners', join("\n",$owners));
$page->assign('members', join("\n",$members));
$page->run();
?>
