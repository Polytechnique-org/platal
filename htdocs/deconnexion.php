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

require_once('xorg.inc.php');

if (isset($_SESSION['suid'])) { require_once('./exit.php'); }

if (Get::has('forget')) {
    setcookie('ORGaccess', '', time() - 3600, '/', '', 0);
    Cookie::kill('ORGaccess');
    if (isset($_SESSION['log']))
        $_SESSION['log']->log("cookie_off");
}

if (Get::has('forgetUid')) {
    setcookie('ORGuid', '', time() - 3600, '/', '', 0);
    Cookie::kill('ORGuid');
    setcookie('ORGdomain', '', time() - 3600, '/', '', 0);
    Cookie::kill('ORGdomain');
}

if (isset($_SESSION['log'])) {
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $_SESSION['log']->log('deconnexion',$ref);
}

XorgSession::destroy();

if (Get::has('redirect')) {
    redirect(rawurldecode(Get::get('redirect')));
} else {
    new_skinned_page('deconnexion.tpl', AUTH_PUBLIC);
    $page->run();
}

// vim:set et sws=4 sts=4 sw=4:
?>
