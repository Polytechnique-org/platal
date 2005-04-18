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

if (!$page) {
    require_once("xorg.inc.php");
    if (!Env::has('liste')) header('Location: index.php');
    $liste = strtolower(Env::get('liste'));

    new_admin_page('listes/check.tpl');
    require_once('lists.inc.php');

    $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));
}

if(Post::has('correct')) {
    $client->check_options($liste, true);
}

if(list($details,$options) = $client->check_options($liste)) {
    $page->assign_by_ref('details', $details);
    $page->assign_by_ref('options', $options);
} else {
    $page->kill("La liste n'existe pas");
}

$page->run();
?>
