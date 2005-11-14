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
    if (!Env::has('liste')) redirect('index.php');
    $liste = strtolower(Env::get('liste'));

    new_skinned_page('listes/options.tpl', AUTH_MDP);
    require_once('lists.inc.php');

    $client =& lists_xmlrpc(Session::getInt('uid'), Session::get('password'));
}

if (Post::has('submit')) {
    $values = $_POST;
    $client->set_bogo_level($liste, intval($values['bogo_level']));
    switch($values['moderate']) {
	case '0':
	    $values['generic_nonmember_action']  = 0;
	    $values['default_member_moderation'] = 0;
	    break;
	case '1':
	    $values['generic_nonmember_action']  = 1;
	    $values['default_member_moderation'] = 0;
	    break;
	case '2':
	    $values['generic_nonmember_action']  = 1;
	    $values['default_member_moderation'] = 1;
	    break;
    }
    unset($values['submit'], $values['bogo_level'], $values['moderate']);
    $values['send_goodbye_msg']      = !empty($values['send_goodbye_msg']);
    $values['admin_notify_mchanges'] = !empty($values['admin_notify_mchanges']);
    $values['subscribe_policy']      = empty($values['subscribe_policy']) ? 0 : 2;
    if (isset($values['subject_prefix'])) {
	$values['subject_prefix'] = trim($values['subject_prefix']).' ';
    }
    $client->set_owner_options($liste, $values);
} elseif (isvalid_email(Post::get('atn_add'))) {
    $client->add_to_wl($liste, Post::get('atn_add'));
} elseif (Get::has('atn_del')) {
    $client->del_from_wl($liste, Get::get('atn_del'));
    redirect("{$_SERVER['PHP_SELF']}?liste=$liste");
}

if(list($details,$options) = $client->get_owner_options($liste)) {
    $page->assign_by_ref('details', $details);
    $page->assign_by_ref('options', $options);
    $page->assign('bogo_level', $client->get_bogo_level($liste));
} else {
    $page->kill("La liste n'existe pas ou tu n'as pas le droit de l'administrer");
}

$page->run();
?>
