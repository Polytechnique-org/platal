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
        $Id: liste.php,v 1.3 2004-09-10 22:45:43 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = $_REQUEST['liste'];

require("auto.prepend.inc.php");
new_skinned_page('listes/liste.tpl', AUTH_COOKIE, true);
include('xml-rpc-client.inc.php');

$res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id={$_SESSION['uid']}");
list($pass) = mysql_fetch_row($res);
mysql_free_result($res);

$client = new xmlrpc_client("http://{$_SESSION['uid']}:$pass@localhost:4949");


if(isset($_REQUEST['info'])) $client->set_welcome($liste, $_REQUEST['info']);

if(isset($_REQUEST['add_member']) && isset($_REQUEST['member'])) {
    if(list($added) = $client->mass_subscribe($liste, Array($_REQUEST['member']))) {
	$members = $client->get_members($liste);
	include_once("diogenes.mailer.inc.php");
	$mailer = new DiogenesMailer("\"Mailing list $liste\" <$liste-owner@polytechnique.org>",
				     "\"{$added[0]}\" <{$added[1]}>",
				     "Bienvenue sur la liste de diffusion $liste@polytechnique.org");
	$mailer->setBody($members[0]['info']);
	$mailer->send();
    }
}

if(isset($_REQUEST['del_member']) && isset($_REQUEST['member'])) {
    $res = $globals->db->query("SELECT  b.alias
                                  FROM  aliases AS a
		            INNER JOIN  aliases AS b ON (a.id=b.id AND b.type='a_vie')
		                 WHERE  a.alias='{$_REQUEST['member']}'");
    if($forlife = mysql_fetch_row($res)) {
	$client->mass_unsubscribe($liste, $forlife);
    }
    mysql_free_result($res);
}

if(isset($_REQUEST['add_owner']) && isset($_REQUEST['owner'])) {
    $client->add_owner($liste, $_REQUEST['owner']);
}

if(isset($_REQUEST['del_owner']) && isset($_REQUEST['owner'])) {
    $res = $globals->db->query("SELECT  b.alias
                                  FROM  aliases AS a
		            INNER JOIN  aliases AS b ON (a.id=b.id AND b.type='a_vie')
		                 WHERE  a.alias='{$_REQUEST['owner']}'");
    if(list($forlife) = mysql_fetch_row($res)) {
	$client->del_owner($liste, $forlife);
    }
    mysql_free_result($res);
}

if(empty($members)) $members = $client->get_members($liste);
if(is_array($members)) {
    $membres = Array();
    foreach($members[1] as $member) {
	if(preg_match('/^([^.]*.[^.]*.(\d\d\d\d))@polytechnique.org$/', $member[1], $matches)) {
	    $membres[$matches[2]][] = Array('n' => $member[0], 'l' => $matches[1]);
	} else {
	    $membres[0][] = Array('l' => $member[1]);
	}
    }
    ksort($membres);

    $moderos = Array();
    foreach($members[2] as $owner) {
	list($m) = split('@',$owner);
	$res = $globals->db->query("SELECT  CONCAT(prenom, ' ', nom), promo
				      FROM  auth_user_md5 AS u
			        INNER JOIN  aliases AS a ON u.user_id = a.id
				     WHERE  a.alias = '$m'");
	if(list($nom, $promo) = mysql_fetch_row($res)) {
	    $moderos[$promo][] = Array('n' => $nom, 'l' => $m);
	} else {
	    $moderos[0][] = Array('l' => $owner);
	}
	mysql_free_result($res);
    }
    ksort($moderos);

    $page->assign_by_ref('details', $members[0]);
    $page->assign_by_ref('members', $membres);
    $page->assign_by_ref('owners',  $moderos);

} else
    $page->assign('no_list',true);

$page->run();
?>
