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

if (!Env::has('liste')) header('Location: index.php');
$liste = strtolower(Env::get('liste'));

require_once("xorg.inc.php");
new_skinned_page('listes/members.tpl', AUTH_COOKIE);
require_once('lists.inc.php');

$client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));

if(Get::has('del')) {
    $client->unsubscribe($liste);
    header("Location: ?liste=$liste");
}
if(Get::has('add')) {
    $client->subscribe($liste);
    header("Location: ?liste=$liste");
}
$members = $client->get_members($liste);

$tri_promo = !Env::getBool('alpha');

if(list($det,$mem,$own) = $members) {
    $membres = list_sort_members($mem, $tri_promo);
    $moderos = list_sort_owners($own, $tri_promo);

    $page->assign_by_ref('details', $det);
    $page->assign_by_ref('members', $membres);
    $page->assign_by_ref('owners',  $moderos);
    $page->assign('nb_m',  count($mem));

} else {
    $page->kill("La liste n'existe pas ou tu n'as pas le droit d'en voir les détails");
}

$page->run();
?>
