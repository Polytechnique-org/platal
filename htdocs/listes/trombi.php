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
        $Id: trombi.php,v 1.6 2004-10-15 09:40:31 web Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = $_REQUEST['liste'];
$npage = isset($_REQUEST['npage']) ? $_REQUEST['npage'] : 1;

require("auto.prepend.inc.php");
new_skinned_page('listes/trombi.tpl', AUTH_COOKIE, true);
include('xml-rpc-client.inc.php');

$res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id={$_SESSION['uid']}");
list($pass) = mysql_fetch_row($res);
mysql_free_result($res);

$client = new xmlrpc_client("http://{$_SESSION['uid']}:$pass@localhost:4949");
$members = $client->get_members_limit('polytechnique.org',$liste,$npage,30);

if(is_array($members)) {
    $membres = Array();
    foreach($members[1] as $member) {
        list($m) = split('@',$member[1]);
        $res = $globals->db->query("SELECT  IF(epouse='', CONCAT(prenom, ' ', nom), CONCAT(prenom, ' ', epouse)), promo
                                      FROM  auth_user_md5 AS u
                                INNER JOIN  aliases AS a ON u.user_id = a.id
                                     WHERE  a.alias = '$m'");
        if(list($nom, $promo) = mysql_fetch_row($res)) {
            $membres[$promo][] = Array('n' => $nom, 'l' => $m);
        } else {
            $membres[0][] = Array('l' => $member[0]);
        }
        mysql_free_result($res);

/*	if(preg_match('/^([^.]*.[^.]*.(\d\d\d\d))@polytechnique.org$/', $member[1], $matches)) {
	    $membres[$matches[2]][] = Array('n' => $member[0], 'l' => $matches[1]);
	} else {
	    $membres[0][] = Array('l' => $member[1]);
	}*/
    }
    ksort($membres);

    $moderos = Array();
    foreach($members[2] as $owner) {
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

    $page->assign_by_ref('details', $members[0]);
    $page->assign_by_ref('members', $membres);
    $page->assign_by_ref('owners',  $moderos);
    $links = Array();
    if($npage>1)
	$links[] = Array('i'=>$npage - 1, 'text' => 'Précédent');
    for($i=1; $i<=$members[3]; $i++)
	$links[] = Array('i'=>$i, 'text' => $i);
    if($npage<$members[3])
	$links[] = Array('i'=>$npage + 1, 'text' => 'Suivant');
    $page->assign('links', $links);
    $page->assign('npage', $npage);

} else
    $page->assign('no_list',true);

$page->run();
?>
