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
new_skinned_page('acces_smtp.tpl', AUTH_MDP);
    
$uid  = Session::getInt('uid');
$pass = Env::get('smtppass1');

if ( Env::get('op') == "Valider" && Env::get('smtppass1') == Env::get('smtppass2') && strlen($pass) >= 6 ) {

    $globals->db->query("update auth_user_md5 set smtppass = '$pass' where user_id = $uid");
    $_SESSION['log']->log("passwd_ssl");
    $page->trig('Mot de passe enregistré');

} elseif (Env::get('op') == "Supprimer") {

    $globals->db->query("update auth_user_md5 set smtppass = '' where user_id = $uid");
    $_SESSION['log']->log("passwd_del");
    $page->trig('Compte SMTP et NNTP supprimé');

}

$result = $globals->db->query("select IF(smtppass != '', 'actif', '') from auth_user_md5 where user_id = ".$_SESSION['uid']);
list($actif) = mysql_fetch_row($result);
mysql_free_result($result);

$page->assign('actif', $actif);
$page->run($actif);

// vim:et:sw=4:
?>
