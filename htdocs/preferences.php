<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
$page->assign('xorg_title','Polytechnique.org - Mes préférences');

if (Env::has('mail_fmt')) {
    $fmt=Env::get('mail_fmt');
    if ($fmt != 'texte') $fmt = 'html';
    $globals->xdb->execute("      UPDATE auth_user_quick
                                     SET core_mail_fmt = '$fmt'
                                   WHERE user_id = {?}", Session::getInt('uid'));
    $_SESSION['mail_fmt'] = $fmt;
    redirect('preferences.php');
}

if (Env::has('rss')) {
    if (Env::getBool('rss')) {
        $_SESSION['core_rss_hash'] = rand_url_id(16);
        $globals->xdb->execute('UPDATE auth_user_quick SET core_rss_hash={?} WHERE user_id={?}',
                Session::get('core_rss_hash'), Session::getInt('uid'));
    } else {
        $globals->xdb->execute('UPDATE auth_user_quick SET core_rss_hash="" WHERE user_id={?}', Session::getInt('uid'));
        Session::kill('core_rss_hash');
    }
    redirect('preferences.php');
}

$page->assign('prefs', $globals->hook->prefs());
$page->run();

// vim:set et sw=4 sts=4 sws=4:
?>
