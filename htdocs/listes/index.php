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
        $Id: index.php,v 1.9 2004-10-18 07:06:49 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('listes/index.tpl', AUTH_MDP);
include('xml-rpc-client.inc.php');
include('newsletter.inc.php');

$res = $globals->db->query("SELECT password FROM auth_user_md5 WHERE user_id={$_SESSION['uid']}");
list($pass) = mysql_fetch_row($res);
mysql_free_result($res);

$client = new xmlrpc_client("http://{$_SESSION['uid']}:$pass@localhost:4949");
if(isset($_GET['del'])) {
    $client->unsubscribe('polytechnique.org',$_GET['del']);
    header('Location: index.php');
}
if(isset($_GET['add'])) {
    $client->subscribe('polytechnique.org',$_GET['add']);
    header('Location: index.php');
}
if(isset($_POST['promo_add'])) {
    $promo = intval($_POST['promo_add']);
    if($promo>=1900 and $promo<2100) {
	$client->subscribe('polytechnique.org',"promo$promo");
    }
}
if(!empty($_GET['nl_unsub'])) unsubscribe_nl();
if(!empty($_GET['nl_sub']))   subscribe_nl($_GET['nl_sub']=='html');

$listes = $client->get_lists('polytechnique.org');
$page->assign_by_ref('listes',$listes);
$page->assign('nl',get_nl_state());
$page->run();
?>
