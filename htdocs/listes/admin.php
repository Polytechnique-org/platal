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
        $Id: admin.php,v 1.14 2004-11-22 20:04:44 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = strtolower($_REQUEST['liste']);

require_once("xorg.inc.php");
new_skinned_page('listes/admin.tpl', AUTH_MDP);
require_once('xml-rpc-client.inc.php');

$err = Array();

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949/polytechnique.org");

if(isset($_REQUEST['add_member'])) {
    $arr = $client->mass_subscribe($liste, Array($_REQUEST['add_member']));
    if(is_array($arr)) {
	foreach($arr as $addr) $err[] = "{$addr[0]} inscrit.";
    }
}

if(isset($_REQUEST['del_member'])) {
    $client->mass_unsubscribe($liste, Array($_REQUEST['del_member']));
    header("Location: ?liste=$liste");
}

if(isset($_REQUEST['add_owner'])) {
    if($client->add_owner($liste, $_REQUEST['add_owner']))
	$err = $_REQUEST['add_owner']." ajouté aux modérateurs.";
}

if(isset($_REQUEST['del_owner'])) {
    $client->del_owner($liste, $_REQUEST['del_owner']);
    header("Location: ?liste=$liste");
}

if(list($det,$mem,$own) = $client->get_members($liste)) {
    $membres = Array();
    foreach($mem as $member) {
	if(preg_match('/^([^.]*\.([^.]*)\.\d\d\d\d)@polytechnique.org$/', $member[1], $matches)) {
	    $key = strtoupper($matches[2]{0});
	    $membres[$key][$matches[2]] = Array('n' => $member[0], 'l' => $matches[1], 'a' => $member[1]);
	} else {
	    $membres[0][] = Array('l' => $member[1], 'a' => $member[1]);
	}
    }
    foreach($membres as $key=>$val) ksort($membres[$key]);
    ksort($membres);

    $moderos = Array();
    foreach($own as $owner) {
	list($m) = split('@',$owner);
	$res = $globals->db->query("SELECT  CONCAT(prenom, ' ', nom), promo
				      FROM  auth_user_md5 AS u
			        INNER JOIN  aliases AS a ON u.user_id = a.id
				     WHERE  a.alias = '$m'");
	if(list($nom, $promo) = mysql_fetch_row($res)) {
	    $moderos[$promo][] = Array('n' => $nom, 'l' => $m, 'a'=>$owner);
	} else {
	    $moderos[0][] = Array('l' => $owner, 'a' => $owner);
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

$page->assign('err', $err);
$page->run();
?>
