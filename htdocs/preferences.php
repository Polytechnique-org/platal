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
new_skinned_page('preferences.tpl', AUTH_COOKIE);

if (Env::has('remember')) {
    setcookie('ORGaccess', md5(Session::get('password')),  (time()+25920000), '/', '' ,0);
    $_SESSION['log']->log('cookie_on');
    header('Location: preferences.php');
}

if (Env::has('forget')) {
    setcookie('ORGaccess', '', time() - 3600, '/', '', 0);
    $_SESSION['log']->log("cookie_off");
    if (!identified()) {
        session_destroy();
        $_SESSION = array();
        header('Location: index.php');
    }
    header('Location: preferences.php');
}

if (Env::has('mail_fmt')) {
    $fmt=Env::get('mail_fmt');
    if ($fmt != 'texte') $fmt = 'html';
    $globals->xdb->execute("REPLACE INTO auth_user_quick
                                     SET core_mail_fmt = '$fmt'
                                   WHERE user_id = {?}", Session::get('uid'));
    $_SESSION['mail_fmt'] = $fmt;
}

$page->assign('prefs', $globals->hook->prefs());
$page->run();

// vim:set et sw=4 sts=4 sws=4:
?>
