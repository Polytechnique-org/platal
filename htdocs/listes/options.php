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

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = strtolower($_REQUEST['liste']);

require_once("xorg.inc.php");
new_skinned_page('listes/options.tpl', AUTH_MDP);
require_once('xml-rpc-client.inc.php');

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949/polytechnique.org");

if(isset($_POST['submit'])) {
    $values =array_map('stripslashes',$_POST);
    $client->set_bogo_level($liste, intval($values['bogo_level']));
    unset($values['submit']);
    unset($values['bogo_level']);
    $values['send_goodbye_msg'] = empty($values['send_goodbye_msg']) ? false : true;
    $values['admin_notify_mchanges'] = empty($values['admin_notify_mchanges']) ? false : true;
    $values['subscribe_policy'] = empty($values['subscribe_policy']) ? 0 : 2;
    if(isset($values['subject_prefix'])) {
	$values['subject_prefix'] = trim($values['subject_prefix']).' ';
    }
    $client->set_owner_options($liste, $values);
} elseif(isset($_POST['atn_add']) && isvalid_email($_POST['atn_add'])) {
    $client->add_to_wl($liste, $_POST['atn_add']);
} elseif(isset($_GET['atn_del'])) {
    $client->del_from_wl($liste, $_GET['atn_del']);
    header("Location: ?liste=$liste");
}

if(list($details,$options) = $client->get_owner_options($liste)) {
    $page->assign_by_ref('details', $details);
    $page->assign_by_ref('options', $options);
    $page->assign('bogo_level', $client->get_bogo_level($liste));
} else
    $page->assign('no_list', true);

$page->run();
?>
