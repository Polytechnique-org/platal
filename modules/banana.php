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
            'banana'              => $this->make_hook('banana', AUTH_COOKIE),
            'banana/profile'      => $this->make_hook('profile', AUTH_MDP),
            'banana/subscription' => $this->make_hook('subscription', AUTH_COOKIE),
            'banana/xface'        => $this->make_hook('xface', AUTH_COOKIE),
        );
    }

    function handler_banana(&$page, $group = null, $action = null, $artid = null)
    {
        $get = Array();
        if (!is_null($group)) {
            $get['group'] = $group;
        }
        if (Post::has('updateall')) {
            $get['banana'] = 'updateall';
        }
        if (!is_null($action)) {
            if ($action == 'new') {
                $get['action'] = 'new';
            } elseif ($action == 'reply' && !is_null($artid)) {
                $get['action'] = 'new';
                $get['artid']  = $artid;
            } elseif ($action == 'cancel' && !is_null($artid)) {
                $get['action'] = $action;
                $get['artid']  = $artid;
            } elseif ($action == 'from' && !is_null($artid)) {
                $get['first'] = $artid;
            } elseif ($action == 'read' && !is_null($artid)) {
                $get['artid'] = $artid;
            }
        }
        return BananaModule::run_banana($page, $get);
    }

    function handler_profile(&$page, $action = null)
    {
        global $globals;

        $page->changeTpl('banana/profile.tpl');

        if (!(Post::has('action') && Post::has('banananame') && Post::has('bananasig')
        && Post::has('bananadisplay') && Post::has('bananamail')
        && Post::has('bananaupdate') && Post::v('action')=="OK" ))
        {
            $req = XDB::query("
                SELECT  nom,mail,sig,if(FIND_IN_SET('threads',flags),'1','0'),
                        IF(FIND_IN_SET('automaj',flags),'1','0') 
                  FROM  forums.profils
                 WHERE  uid = {?}", S::v('uid'));
            if (!(list($nom,$mail,$sig,$disp,$maj) = $req->fetchOneRow())) {
                $nom  = S::v('prenom').' '.S::v('nom');
                $mail = S::v('forlife').'@'.$globals->mail->domain;
                $sig  = $nom.' ('.S::v('promo').')';
                $disp = 0;
                $maj  = 0;
            }
            $page->assign('nom' , $nom);
            $page->assign('mail', $mail);
            $page->assign('sig' , $sig);
            $page->assign('disp', $disp);
            $page->assign('maj' , $maj);
        } else {
            XDB::execute(
                'REPLACE INTO  forums.profils (uid,sig,mail,nom,flags)
                       VALUES  ({?},{?},{?},{?},{?})',
                S::v('uid'), Post::v('bananasig'),
                Post::v('bananamail'), Post::v('banananame'),
                (Post::b('bananadisplay') ? 'threads,' : '') .
                (Post::b('bananaupdate') ? 'automaj' : '')
            );
        }
    }

    function handler_subscription(&$page)
    {
        return $this->run_banana($page, Array('subscribe' => 1));
    }

    function handler_xface(&$page, $face = null)
    {
        header('Content-Type: image/gif');
        passthru('echo ' . escapeshellarg(base64_decode(strtr($face, '.:', '+/')))
                . '| uncompface -X '
                . '| convert -transparent white xbm:- gif:-');
    }

    function run_banana(&$page, $params = null)
    {
        $page->changeTpl('banana/index.tpl');
        $page->addCssLink('css/banana.css');
        $page->assign('xorg_title','Polytechnique.org - Forums & PA');

        require_once('banana.inc.php');

        $res = PlatalBanana::run($params);
        $page->assign_by_ref('banana', $banana);
        $page->assign('banana_res', $res);
    }
}

?>
