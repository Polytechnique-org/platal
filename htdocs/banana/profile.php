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
new_skinned_page('banana/profile.tpl', AUTH_MDP);

if (!(Post::has('action') && Post::has('banananame') && Post::has('bananasig') && Post::has('bananadisplay')
            && Post::has('bananamail') && Post::has('bananaupdate') && Post::get('action')=="OK" ))
{
    $req = $globals->xdb->query("
        SELECT  nom,mail,sig,if(FIND_IN_SET('threads',flags),'1','0'),
                IF(FIND_IN_SET('automaj',flags),'1','0') 
          FROM  forums.profils
         WHERE  uid = {?}", Session::getInt('uid'));
    if (!(list($nom,$mail,$sig,$disp,$maj) = $req->fetchOneRow())) {
        $nom  = Session::get('prenom').' '.Session::get('nom');
        $mail = Session::get('forlife').'@'.$globals->mail->domain;
        $sig  = $nom.' ('.Session::getInt('promo').')';
        $disp = 0;
        $maj  = 0;
    }
    $page->assign('nom' , $nom);
    $page->assign('mail', $mail);
    $page->assign('sig' , $sig);
    $page->assign('disp', $disp);
    $page->assign('maj' , $maj);
} else {
    $globals->xdb->execute(
        'REPLACE INTO  forums.profils (uid,sig,mail,nom,flags)
               VALUES  ({?},{?},{?},{?},{?})',
               Session::getInt('uid'), Post::get('bananasig'), Post::get('bananamail'), Post::get('banananame'),
               (Post::getBool('bananadisplay') ? 'threads,' : '') . (Post::getBool('bananaupdate') ? 'automaj' : '')
    );
}

$page->run();
?>
