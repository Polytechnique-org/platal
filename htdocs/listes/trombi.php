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
        $Id: trombi.php,v 1.12 2004-11-02 07:48:40 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = strtolower($_REQUEST['liste']);

require("auto.prepend.inc.php");
new_skinned_page('listes/trombi.tpl', AUTH_COOKIE, true);
include('xml-rpc-client.inc.php');
require("trombi.inc.php");

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949");

function getList($offset,$limit) {
    global $client, $globals;
    $liste = $_REQUEST['liste'];
    list($total,$members) = $client->get_members_limit('polytechnique.org',$liste,$offset,$limit);

    $membres = Array();
    foreach($members as $member) {
        list($m) = split('@',$member[1]);
        $res = $globals->db->query("SELECT  prenom,IF(epouse='', nom, epouse) AS nom, promo, a.alias AS forlife
                                      FROM  auth_user_md5 AS u
                                INNER JOIN  aliases AS a ON u.user_id = a.id
                                     WHERE  a.alias = '$m'");
        if($tmp = mysql_fetch_assoc($res)) {
            $membres[$tmp['nom']] = $tmp;
        } else {
            $membres[$member[0]] = Array('addr' => $member[0]);
        }
        mysql_free_result($res);
    }
    return Array($total,$membres);
}

$owners = $client->get_owners('polytechnique.org',$liste);

if(is_array($owners)) {
    $moderos = Array();
    foreach($owners[1] as $owner) {
	list($m) = split('@',$owner);
	$res = $globals->db->query("SELECT  IF(epouse='', CONCAT(prenom, ' ', nom), CONCAT(prenom, ' ', epouse)), promo
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

    $page->assign_by_ref('details', $owners[0]);
    $page->assign_by_ref('owners',  $moderos);
    
    $trombi = new Trombi('getList');
    $page->assign_by_ref('trombi',$trombi);
} else
    $page->assign('no_list',true);

$page->run();
?>
