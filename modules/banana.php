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

class BananaModule extends PLModule
{
    function handlers()
    {
        return array(
            'banana' => $this->make_hook('banana', AUTH_COOKIE),
            'banana/profile' => $this->make_hook('profile', AUTH_MDP),
        );
    }

    function handler_banana(&$page)
    {
        $page->changeTpl('banana/index.tpl');
        $page->addCssLink('css/banana.css');
        $page->assign('xorg_title','Polytechnique.org - Forums & PA');

        require_once 'banana.inc.php';

        $res = PlatalBanana::run();
        $page->assign_by_ref('banana', $banana);
        $page->assign('banana_res', $res);

        return PL_OK;
    }

    function handler_profile(&$page)
    {
        global $globals;

        $page->changeTpl('banana/profile.tpl');

        if (!(Post::has('action') && Post::has('banananame') && Post::has('bananasig')
        && Post::has('bananadisplay') && Post::has('bananamail')
        && Post::has('bananaupdate') && Post::get('action')=="OK" ))
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

        return PL_OK;
    }
}

?>
