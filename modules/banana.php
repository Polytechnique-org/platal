<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

    function on_subscribe($forlife, $uid, $promo, $password)
    {
        $cible = array('xorg.general', 'xorg.pa.divers', 'xorg.pa.logements');
        $p_for = "xorg.promo.x$promo";

        // récupération de l'id du forum promo
        $res = XDB::query("SELECT fid FROM forums.list WHERE nom={?}", $p_for);
        if ($res->numRows()) {
            $cible[] = $p_for;
        } else { // pas de forum promo, il faut le créer
            $res = XDB::query("SELECT  SUM(perms IN ('admin','user') AND deces=0),COUNT(*)
                                 FROM  auth_user_md5 WHERE promo={?}", $promo);
            list($effau, $effid) = $res->fetchOneRow();
            if (5*$effau>$effid) { // + de 20% d'inscrits
                $mymail = new PlMailer('mails/forums.promo.tpl');
                $mymail->assign('promo', $promo);
                $mymail->send();
            }
        }

        while (list ($key, $val) = each ($cible)) {
            XDB::execute("INSERT INTO  forums.abos (fid,uid)
                               SELECT  fid,{?} FROM forums.list WHERE nom={?}", $uid, $val);
        }
    }

    function handler_banana(&$page, $group = null, $action = null, $artid = null)
    {
        $get = Array();
        if (!is_null($group)) {
            $get['group'] = $group;
        }
        if (Post::has('updateall')) {
            $get['updateall'] = Post::v('updateall');
        }
        if (!is_null($action)) {
            if ($action == 'new') {
                $get['action'] = 'new';
            } elseif (!is_null($artid)) {
                $get['artid'] = $artid; 
                if ($action == 'reply') {
                    $get['action'] = 'new';
                } elseif ($action == 'cancel') {
                    $get['action'] = $action;
                } elseif ($action == 'from') {
                    $get['first'] = $artid;
                } elseif ($action == 'read') {
                    $get['part']  = @$_GET['part'];
                } elseif ($action == 'source') {
                    $get['part'] = 'source';
                } elseif ($action == 'xface') {
                    $get['part']  = 'xface';
                }   
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
        return $this->run_banana($page, Array('action' => 'subscribe'));
    }

    function handler_xface(&$page, $face = null)
    {
        header('Content-Type: image/gif');
        passthru('echo ' . escapeshellarg(base64_decode(strtr($face, '.:', '+/')))
                . '| uncompface -X '
                . '| convert -transparent white xbm:- gif:-');
    }

    static function run_banana(&$page, $params = null)
    {
        $page->changeTpl('banana/index.tpl');
        $page->assign('xorg_title','Polytechnique.org - Forums & PA');

        require_once 'banana/forum.inc.php';

        $banana = new ForumsBanana($params);
        $res = $banana->run();
        $page->assign_by_ref('banana', $banana);
        $page->assign('banana_res', $res);
        $page->addCssInline($banana->css());
        $page->addCssLink('banana.css');
    }
}

?>
