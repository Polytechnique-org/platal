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
        $Id: members.php,v 1.14 2004-11-30 19:02:07 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = strtolower($_REQUEST['liste']);

require_once("xorg.inc.php");
new_skinned_page('listes/members.tpl', AUTH_COOKIE);
require_once('xml-rpc-client.inc.php');

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949/polytechnique.org");
if(isset($_GET['del'])) {
    $client->unsubscribe($liste);
    header("Location: ?liste=$liste");
}
if(isset($_GET['add'])) {
    $client->subscribe($liste);
    header("Location: ?liste=$liste");
}
$members = $client->get_members($liste);

$tri_promo = empty($_REQUEST['alpha']);

if(list($det,$mem,$own) = $members) {
    $membres = Array();
    foreach($mem as $member) {
        list($m) = split('@',$member[1]);
        $res = $globals->db->query("SELECT  prenom,IF(epouse='', nom, epouse), promo
                                      FROM  auth_user_md5 AS u
                                INNER JOIN  aliases AS a ON u.user_id = a.id
                                     WHERE  a.alias = '$m'");
        if(list($prenom, $nom, $promo) = mysql_fetch_row($res)) {
	    $key = $tri_promo ? $promo : strtoupper($nom{0});
            $membres[$key][$nom.$m] = Array('n' => "$prenom $nom", 'l' => $m);
        } else {
            $membres[0][] = Array('l' => $member[1]);
        }
        mysql_free_result($res);
    }
    ksort($membres);
    foreach($membres as $key=>$val) ksort($membres[$key]);

    $moderos = Array();
    foreach($own as $owner) {
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

    $page->assign_by_ref('details', $det);
    $page->assign_by_ref('members', $membres);
    $page->assign_by_ref('owners',  $moderos);
    $page->assign('nb_m',  count($mem));

} else
    $page->assign('no_list',true);

$page->run();
?>
