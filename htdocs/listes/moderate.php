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
        $Id: moderate.php,v 1.2 2004-09-21 15:40:35 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = strtolower($_REQUEST['liste']);

require("auto.prepend.inc.php");
include('xml-rpc-client.inc.php');
$res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id={$_SESSION['uid']}");
list($pass) = mysql_fetch_row($res);
mysql_free_result($res);

$client = new xmlrpc_client("http://{$_SESSION['uid']}:$pass@localhost:4949");
if(!$client->is_admin($liste)) header('Location: index.php');

if(isset($_REQUEST['sadd'])) {
    $client->handle_request($liste,$_REQUEST['sadd'],4,''); /** 4 is the magic for SUBSCRIBE see Defaults.py **/
    header("Location: moderate.php?liste=$liste");
}

if(isset($_POST['sdel'])) {
    $client->handle_request($liste,$_POST['sdel'],2,stripslashes($_POST['reason'])); /** 2 is the magic for REJECT see Defaults.py **/
}

if(isset($_REQUEST['sid'])) {
    $sid = $_REQUEST['sid'];
    if(list($subs,$mails) = $client->get_pending_ops($liste)) {
	foreach($subs as $user) {
	    if($user['id'] == $sid) $u = $user;
	}
    }

    if($u) {
	new_skinned_page('listes/moderate_sub.tpl', AUTH_MDP, true);
	$page->assign('del_user',$user);
    } else {
	new_skinned_page('listes/moderate.tpl', AUTH_MDP, true);
	$page->assign_by_ref('subs', $subs);
	$page->assign_by_ref('mails', $mails);
    }
} elseif(isset($_REQUEST['mid'])) {
    $mid = $_REQUEST['mid'];
    new_skinned_page('listes/moderate_mail.tpl', AUTH_MDP, true);
    $mail = $client->get_pending_mail($liste,$mid);
    if(is_array($mail)) {
        $page->assign_by_ref('mail', $mail);
    }
} else {
    new_skinned_page('listes/moderate.tpl', AUTH_MDP, true);

    if(list($subs,$mails) = $client->get_pending_ops($liste)) {
	$page->assign_by_ref('subs', $subs);
	$page->assign_by_ref('mails', $mails);
    }
}
$page->run();
?>
