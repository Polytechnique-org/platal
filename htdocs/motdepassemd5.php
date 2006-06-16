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

if (Env::has('response2'))  {
    $_SESSION['password'] = $password = Post::get('response2');
    
    $globals->xdb->execute('UPDATE auth_user_md5 SET password={?} WHERE user_id={?}', $password, Session::getInt('uid'));
    
    $log =& Session::getMixed('log');
    $log->log('passwd', '');

    if (Cookie::get('ORGaccess')) {
        setcookie('ORGaccess', md5($password), (time()+25920000), '/', '' ,0);
    }

    new_skinned_page('motdepassemd5.success.tpl', AUTH_MDP);
    $page->run();
}

new_skinned_page('motdepassemd5.tpl', AUTH_MDP);
$page->addJsLink('javascript/md5.js');
$page->addJsLink('javascript/motdepassemd5.js');
$page->assign('xorg_title','Polytechnique.org - Mon mot de passe');
$page->run();
?>
