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
new_skinned_page('listes/create.tpl', AUTH_MDP);

$owners  = preg_split("/[\r\n]+/",Post::get('owners'), -1, PREG_SPLIT_NO_EMPTY);
$members = preg_split("/[\r\n]+/",Post::get('members'), -1, PREG_SPLIT_NO_EMPTY);

// click on validate button 'add_owner_sub' or type <enter>
if (Post::has('add_owner_sub') && Post::has('add_owner')) {
    require_once('user.func.inc.php');
    // if we want to add an owner and then type <enter>, then both add_owner_sub and add_owner are filled.
    if (Post::get('add_owner') != "") {
      if (($forlife = get_user_forlife(Post::get('add_owner'))) !== false) {
          $owners [] = $forlife;
      }
    // if we want to add a member and then type <enter>, then add_owner_sub is filled, whereas add_owner is empty.
    } else if (Post::has('add_member')) {
      if (($forlife = get_user_forlife(Post::get('add_member'))) !== false) {
          $members[] = $forlife;
      }
    }
}

// click on validate button 'add_member_sub'
if (Post::has('add_member_sub') && Post::has('add_member')) {
    require_once('user.func.inc.php');
    if (($forlife = get_user_forlife(Post::get('add_member'))) !== false) {
        $members[] = $forlife;
    }
}

ksort($owners);	 array_unique($owners);
ksort($members); array_unique($members);

if (Post::has('submit')) {

    $liste = Post::get('liste');

    if(empty($liste)) {
        $page->trig('champs «addresse souhaitée» vide');
    }
    if(!preg_match("/^[a-zA-Z0-9\-]*$/", $liste)) {
	$page->trig('le nom de la liste ne doit contenir que des lettres, chiffres et tirets');
    }

    $res = $globals->xdb->query("SELECT COUNT(*) FROM aliases WHERE alias={?}", $liste);
    $n   = $res->fetchOneCell();

    if($n) {
        $page->trig('cet alias est déjà pris');
    }

    if(!Post::get(desc)) {
        $page->trig('le sujet est vide');
    }
    
    if(!count($owners)) {
        $page->trig('pas de gestionnaire');
    }
    
    if(count($members)<4) {
        $page->trig('pas assez de membres');
    }

    if (!$page->nb_errs()) {
	$page->assign('created', true);
	require_once('validations.inc.php');
	$req = new ListeReq(Session::getInt('uid'), $liste, Post::get('desc'),
                Post::getInt('advertise'), Post::getInt('modlevel'), Post::getInt('inslevel'),
                $owners, $members);
        $req->submit();
    }
}

$page->assign('owners', join("\n",$owners));
$page->assign('members', join("\n",$members));
$page->run();
?>
