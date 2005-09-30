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

require_once("xorg.inc.php");
if (!Env::has('liste')) header('Location: index.php');
$liste = strtolower(Env::get('liste'));

new_skinned_page('listes/trombi.tpl', AUTH_COOKIE);
require_once("trombi.inc.php");
require_once('lists.inc.php');

$client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));

if(Get::has('del')) {
    $client->unsubscribe($liste);
    header("Location: {$_SERVER['PHP_SELF']}?liste=$liste");
}
if(Get::has('add')) {
    $client->subscribe($liste);
    header("Location: {$_SERVER['PHP_SELF']}?liste=$liste");
}

function getList($offset,$limit)
{
    global $client, $globals;
    $liste = Env::get('liste');
    list($total,$members) = $client->get_members_limit($liste,$offset,$limit);

    $membres = Array();
    foreach ($members as $member) {
        list($m) = explode('@',$member[1]);
        $res = $globals->xdb->query("SELECT  prenom,IF(nom_usage='', nom, nom_usage) AS nom, promo, a.alias AS forlife
                                       FROM  auth_user_md5 AS u
                                 INNER JOIN  aliases AS a ON u.user_id = a.id
                                      WHERE  a.alias = {?}", $m);
        if ($tmp = $res->fetchOneAssoc()) {
            $membres[$tmp['nom']] = $tmp;
        } else {
            $membres[$member[0]] = Array('addr' => $member[0]);
        }
    }
    return Array($total,$membres);
}

$owners = $client->get_owners($liste);

if(is_array($owners)) {
    $moderos = list_sort_owners($owners[1]);

    $page->assign_by_ref('details', $owners[0]);
    $page->assign_by_ref('owners',  $moderos);
    
    $trombi = new Trombi('getList');
    $page->assign_by_ref('trombi',$trombi);
} else {
    $page->kill("La liste n'existe pas ou tu n'as pas le droit d'en voir les détails");
}

$page->run();
?>
