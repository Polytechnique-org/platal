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
        $Id: index.php,v 1.13 2004/11/22 20:04:44 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
new_skinned_page('listes/index.tpl', AUTH_MDP);
require_once('xml-rpc-client.inc.php');
require_once('newsletter.inc.php');

$client = new xmlrpc_client("http://{$_SESSION['uid']}:{$_SESSION['password']}@localhost:4949/polytechnique.org");
if(isset($_GET['del'])) {
    $client->unsubscribe($_GET['del']);
    header('Location: index.php');
}
if(isset($_GET['add'])) {
    $client->subscribe($_GET['add']);
    header('Location: index.php');
}
if(isset($_POST['promo_add'])) {
    $promo = intval($_POST['promo_add']);
    if($promo>=1900 and $promo<2100) {
	$client->subscribe("promo$promo");
    }
}
if(!empty($_GET['nl_unsub'])) unsubscribe_nl();
if(!empty($_GET['nl_sub']))   subscribe_nl($_GET['nl_sub']=='html');

$listes = $client->get_lists();
$page->assign_by_ref('listes',$listes);
$page->assign('nl',get_nl_state());
$page->run();
?>
