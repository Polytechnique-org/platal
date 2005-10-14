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
$page->assign('xorg_title','Polytechnique.org - Acces SMTP/NNTP');
    
$uid  = Session::getInt('uid');
$pass = Env::get('smtppass1');
$log  = Session::getMixed('log');

if ( Env::get('op') == "Valider" && Env::get('smtppass1') == Env::get('smtppass2') && strlen($pass) >= 6 ) {

    $globals->xdb->execute('update auth_user_md5 set smtppass = {?} where user_id = {?}', $pass, $uid);
    $page->trig('Mot de passe enregistré');
    $log->log("passwd_ssl");

} elseif (Env::get('op') == "Supprimer") {

    $globals->xdb->execute('update auth_user_md5 set smtppass = "" where user_id = {?}', $uid);
    $page->trig('Compte SMTP et NNTP supprimé');
    $log->log("passwd_del");

}

$res = $globals->xdb->query("select IF(smtppass != '', 'actif', '') from auth_user_md5 where user_id = {?}", $uid);
$page->assign('actif', $res->fetchOneCell());
$page->run();

// vim:et:sw=4:
?>
