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
        $Id: soptions.php,v 1.3 2004-10-06 13:23:20 x2000habouzit Exp $
 ***************************************************************************/

if(empty($_REQUEST['liste'])) header('Location: index.php');
$liste = $_REQUEST['liste'];

require("auto.prepend.inc.php");
new_admin_page('listes/soptions.tpl', true);
include('xml-rpc-client.inc.php');

$res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id={$_SESSION['uid']}");
list($pass) = mysql_fetch_row($res);
mysql_free_result($res);

$client = new xmlrpc_client("http://{$_SESSION['uid']}:$pass@localhost:4949");

if(isset($_POST['submit'])) {
    $values = array_map('stripslashes', $_POST);
    unset($values['submit']);
    switch($values['moderate']) {
	case '0':
	    $values['generic_nonmember_action'] = 0;
	    $values['default_member_moderation'] = 0;
	    break;
	case '1':
	    $values['generic_nonmember_action'] = 1;
	    $values['default_member_moderation'] = 0;
	    break;
	case '2':
	    $values['generic_nonmember_action'] = 1;
	    $values['default_member_moderation'] = 1;
	    break;
    }
    unset($values['moderate']);
    $values['advertised'] = empty($values['advertised']) ? false : true;
    $values['archive'] = empty($values['archive']) ? false : true;
    $client->set_admin_options('polytechnique.org', $liste, $values);
}

if(list($details,$options) = $client->get_admin_options('polytechnique.org', $liste)) {
    $page->assign_by_ref('details', $details);
    $page->assign_by_ref('options', $options);
} else
    $page->assign('no_list', true);

$page->run();
?>
