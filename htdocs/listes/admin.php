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
        $Id: admin.php,v 1.5 2004-10-15 07:30:40 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = $_REQUEST['liste'];

require("auto.prepend.inc.php");
new_skinned_page('listes/admin.tpl', AUTH_MDP, true);
include('xml-rpc-client.inc.php');

$res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id={$_SESSION['uid']}");
list($pass) = mysql_fetch_row($res);
mysql_free_result($res);

$client = new xmlrpc_client("http://{$_SESSION['uid']}:$pass@localhost:4949");

if(isset($_REQUEST['add_member'])) {
    $client->mass_subscribe('polytechnique.org', $liste, Array($_REQUEST['add_member']));
}

if(isset($_REQUEST['del_member'])) {
    $client->mass_unsubscribe('polytechnique.org', $liste, Array($_REQUEST['del_member']));
    header("Location: ?liste=$liste");
}

if(isset($_REQUEST['add_owner'])) {
    $client->add_owner('polytechnique.org', $liste, $_REQUEST['add_owner']);
}

if(isset($_REQUEST['del_owner'])) {
    $client->del_owner('polytechnique.org', $liste, $_REQUEST['del_owner']);
    header("Location: ?liste=$liste");
}

if(list($det,$mem,$own) = $client->get_members('polytechnique.org', $liste)) {
    $membres = Array();
    foreach($mem as $member) {
	if(preg_match('/^([^.]*.[^.]*.(\d\d\d\d))@polytechnique.org$/', $member[1], $matches)) {
	    $membres[$matches[2]][] = Array('n' => $member[0], 'l' => $matches[1]);
	} else {
	    $membres[0][] = Array('l' => $member[1]);
	}
    }
    ksort($membres);

    $moderos = Array();
    foreach($own as $owner) {
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

    $page->assign_by_ref('details', $det);
    $page->assign_by_ref('members', $membres);
    $page->assign_by_ref('owners',  $moderos);
    $page->assign('np_m', count($mem));

} else
    $page->assign('no_list',true);

$page->run();
?>
