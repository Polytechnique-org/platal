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
new_skinned_page('listes/admin.tpl', AUTH_MDP);
require_once('lists.inc.php');

$client =& lists_xmlrpc($_SESSION['uid'], $_SESSION['password']);

if(isset($_REQUEST['add_member'])) {
    require_once('user.func.inc.php');
    if (($login = get_user_forlife($_REQUEST['add_member'])) === false) {;
        $login = $_REQUEST['add_member'];
    }

    $arr = $client->mass_subscribe($liste, Array($login));
    if(is_array($arr)) {
	foreach($arr as $addr) {
            $page->trig("{$addr[0]} inscrit.");
        }
    }
}

if(isset($_REQUEST['del_member'])) {
    $client->mass_unsubscribe($liste, Array($_REQUEST['del_member']));
    header("Location: ?liste=$liste");
}

if(isset($_REQUEST['add_owner'])) {
    require_once('user.func.inc.php');
    if (($login = get_user_forlife($_REQUEST['add_owner'])) === false) {;
        $login = $_REQUEST['add_owner'];
    }

    if($client->add_owner($liste, $login)) {
        $page->trig($_REQUEST['add_owner']." ajouté aux modérateurs.");
    }
}

if(isset($_REQUEST['del_owner'])) {
    $client->del_owner($liste, $_REQUEST['del_owner']);
    header("Location: ?liste=$liste");
}

if(list($det,$mem,$own) = $client->get_members($liste)) {
    if (!$det['own'] && !has_perms()) {
        $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer");
    }
    
    $membres = list_sort_members($mem, $tri_promo);
    $moderos = list_sort_owners($own, $tri_promo);

    $page->assign_by_ref('details', $det);
    $page->assign_by_ref('members', $membres);
    $page->assign_by_ref('owners',  $moderos);
    $page->assign('np_m', count($mem));

} else {
    $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer");
}

$page->run();
?>
