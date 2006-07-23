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
new_admin_page('admin/synchro_ax.tpl');
$page->assign('xorg_title','Polytechnique.org - Administration - Synchro AX');

require_once('synchro_ax.inc.php');

if (is_ax_key_missing()) {
    $page->assign('no_private_key', true);
    $page->run();
}

require_once('user.func.inc.php');

if (Env::has('user')) {
    $login = get_user_forlife(Env::v('user'));
    if ($login === false) {
        exit;
    }
}

if (Env::has('mat')) {
    $res = XDB::query(
            "SELECT  alias 
               FROM  aliases       AS a
         INNER JOIN  auth_user_md5 AS u ON (a.id=u.user_id AND a.type='a_vie')
              WHERE  matricule={?}", Env::i('mat'));
    $login = $res->fetchOneCell();
}

if ($login) {
    if (Env::has('importe')) {
        ax_synchronize($login, S::v('uid'));
    }
    // get details from user, but looking only info that can be seen by ax
    $user  = get_user_details($login, S::v('uid'), 'ax');
    $userax= get_user_ax($user['matricule_ax']);
    require_once 'profil.func.inc.php';
    $diff = diff_user_details($userax, $user, 'ax');

    $page->assign('x', $user);
    $page->assign('diff', $diff);
}
$page->run();

// vim:set et sts=4 sws=4 sw=4:
?>
