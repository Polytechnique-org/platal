<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class XnetModule extends PLModule
{
    function handlers()
    {
        return array(
            'index'       => $this->make_hook('index',     AUTH_PUBLIC),
            'exit'        => $this->make_hook('exit',      AUTH_PUBLIC),

            'admin'       => $this->make_hook('admin',     AUTH_MDP, 'admin'),
            'groups'      => $this->make_hook('groups',    AUTH_PUBLIC),
            'groupes.php' => $this->make_hook('groups2',   AUTH_PUBLIC),
            'plan'        => $this->make_hook('plan',      AUTH_PUBLIC),
            'photo'       => $this->make_hook('photo',     AUTH_MDP),
            'autologin'   => $this->make_hook('autologin', AUTH_MDP),
            'login/ext'   => $this->make_hook('login_ext', AUTH_PUBLIC),
            'register/ext' => $this->make_hook('register_ext', AUTH_PUBLIC),
            'edit'        => $this->make_hook('edit',      AUTH_MDP, 'user'),
            'password'    => $this->make_hook('password',  AUTH_MDP, 'user'),

            'Xnet'        => $this->make_wiki_hook(),
        );
    }

    function handler_photo($page, $x = null)
    {
        if (!$x || !($profile = Profile::get($x))) {
            return PL_NOT_FOUND;
        }

        // Retrieve the photo and its mime type.
        $photo = $profile->getPhoto(true, true);

        // Display the photo, or a default one when not available.
        $photo->send();
    }

    function handler_index($page)
    {
        $page->nomenu = true;
        $page->changeTpl('xnet/index.tpl');
    }

    function handler_exit($page)
    {
        Platal::session()->stopSUID();
        Platal::session()->destroy();
        $page->changeTpl('xnet/deconnexion.tpl');
    }

    function handler_admin($page)
    {
        $page->changeTpl('xnet/admin.tpl');

        if (Get::has('del')) {
            $res = XDB::query('SELECT id, nom, mail_domain
                                           FROM groups WHERE diminutif={?}',
                                        Get::v('del'));
            list($id, $nom, $domain) = $res->fetchOneRow();
            $page->assign('nom', $nom);
            if ($id && Post::has('del')) {
                S::assert_xsrf_token();

                XDB::query('DELETE FROM group_members WHERE asso_id={?}', $id);
                $page->trigSuccess('membres supprimés');

                if ($domain) {
                    XDB::execute('DELETE  v
                                    FROM  email_virtual         AS v
                              INNER JOIN  email_virtual_domains AS d ON (v.domain = d.id)
                                   WHERE  d.name = {?}',
                                 $domain);
                    XDB::execute('DELETE FROM  email_virtual_domains
                                        WHERE  name = {?}', $domain);
                    $page->trigSuccess('suppression des alias mails');

                    $mmlist = new MMList(S::v('uid'), S::v('password'), $domain);
                    if ($listes = $mmlist->get_lists()) {
                        foreach ($listes as $l) {
                            $mmlist->delete_list($l['list'], true);
                        }
                        $page->trigSuccess('mail lists surpprimées');
                    }
                }

                XDB::query('DELETE FROM groups WHERE id={?}', $id);
                $page->trigSuccess("Groupe $nom supprimé");
                Get::kill('del');
            }
            if (!$id) {
                Get::kill('del');
            }
        }

        if (Post::has('diminutif') && Post::v('diminutif') != "") {
            S::assert_xsrf_token();

            $res = XDB::query('SELECT  COUNT(*)
                                 FROM  groups
                                WHERE  diminutif = {?}',
                              Post::v('diminutif'));

            if ($res->fetchOneCell() == 0) {
                XDB::execute('INSERT INTO  groups (id, diminutif)
                                   VALUES  (NULL, {?})',
                             Post::v('diminutif'));
                pl_redirect(Post::v('diminutif') . '/edit');
            } else {
                $page->trigError('Le diminutif demandé est déjà pris.');
            }
        }

        $res = XDB::query('SELECT  nom, diminutif
                             FROM  groups
                         ORDER BY  nom');
        $page->assign('assos', $res->fetchAllAssoc());
    }

    function handler_plan($page)
    {
        $page->changeTpl('xnet/plan.tpl');

        $page->setType('plan');

        $res = XDB::iterator(
                'SELECT  dom.id, dom.nom as domnom, groups.diminutif, groups.nom
                   FROM  group_dom AS dom
             INNER JOIN  groups ON dom.id = groups.dom
                  WHERE  FIND_IN_SET("GroupesX", dom.cat) AND FIND_IN_SET("GroupesX", groups.cat)
               ORDER BY  dom.nom, groups.nom');
        $groupesx = array();
        while ($tmp = $res->next()) { $groupesx[$tmp['id']][] = $tmp; }
        $page->assign('groupesx', $groupesx);

        $res = XDB::iterator(
                'SELECT  dom.id, dom.nom as domnom, groups.diminutif, groups.nom
                   FROM  group_dom AS dom
             INNER JOIN  groups ON dom.id = groups.dom
                  WHERE  FIND_IN_SET("Binets", dom.cat) AND FIND_IN_SET("Binets", groups.cat)
               ORDER BY  dom.nom, groups.nom');
        $binets = array();
        while ($tmp = $res->next()) { $binets[$tmp['id']][] = $tmp; }
        $page->assign('binets', $binets);

        $res = XDB::iterator(
                'SELECT  diminutif, nom
                   FROM  groups
                  WHERE  cat LIKE "%Promotions%"
               ORDER BY  diminutif');
        $page->assign('promos', $res);

        $res = XDB::iterator(
                'SELECT  diminutif, nom
                   FROM  groups
                  WHERE  FIND_IN_SET("Institutions", cat)
               ORDER BY  diminutif');
        $page->assign('inst', $res);
    }

    function handler_groups2($page)
    {
        $this->handler_groups($page, Get::v('cat'), Get::v('dom'));
    }

    function handler_groups($page, $cat = null, $dom = null)
    {
        if (!$cat) {
            $this->handler_index($page);
        }

        $cat = mb_strtolower($cat);

        $page->changeTpl('xnet/groupes.tpl');
        $page->assign('cat', $cat);
        $page->assign('dom', $dom);

        $res  = XDB::query("SELECT  id,nom
                              FROM  group_dom
                             WHERE  FIND_IN_SET({?}, cat)
                          ORDER BY  nom", $cat);
        $doms = $res->fetchAllAssoc();
        $page->assign('doms', $doms);

        if (empty($doms)) {
            $res = XDB::query("SELECT  diminutif, nom, site
                                 FROM  groups
                                WHERE  FIND_IN_SET({?}, cat)
                                ORDER  BY nom", $cat);
            $page->assign('gps', $res->fetchAllAssoc());
        } elseif (!is_null($dom)) {
            $res = XDB::query("SELECT  diminutif, nom, site
                                 FROM  groups
                                WHERE  FIND_IN_SET({?}, cat) AND dom={?}
                             ORDER BY  nom", $cat, $dom);
            $page->assign('gps', $res->fetchAllAssoc());
        }

        $page->setType($cat);
    }

    function handler_autologin($page)
    {
        $allkeys = func_get_args();
        unset($allkeys[0]);
        $url = join('/',$allkeys);
        pl_content_headers("text/javascript");
        echo '$.ajax({ url: "'.$url.'?forceXml=1", dataType: "xml", success: function(xml) { $("body",xml).insertBefore("body"); $("body:eq(1)").remove(); }});';
        exit;
    }

    function handler_login_ext($page)
    {
        if (!S::logged()) {
            $page->changeTpl('xnet/login.tpl');
        } else {
            pl_redirect('');
        }
    }

    function handler_register_ext($page, $hash = null)
    {
        XDB::execute('DELETE FROM  register_pending_xnet
                            WHERE  DATE_SUB(NOW(), INTERVAL 1 MONTH) > date');
        $res = XDB::fetchOneAssoc('SELECT  uid, hruid
                                     FROM  register_pending_xnet
                                    WHERE  hash = {?}',
                                  $hash);

        if (is_null($hash) || is_null($res)) {
            $page->trigErrorRedirect('Cette adresse n\'existe pas ou n\'existe plus sur le serveur.', '');
        }

        if (Post::has('pwhash') && Post::t('pwhash')) {
            XDB::query('UPDATE  accounts
                           SET  password = {?}, state = \'active\'
                         WHERE  uid = {?} AND state = \'pending\' AND type = \'xnet\'',
                       Post::t('pwhash'), $res['uid']);
            XDB::query('DELETE FROM  register_pending_xnet
                              WHERE  uid = {?}',
                       $res['uid']);

            S::logger($res['uid'])->log('passwd', '');

            // Try to start a session (so the user don't have to log in); we will use
            // the password available in Post:: to authenticate the user.
            Post::kill('wait');
            Platal::session()->startAvailableAuth();

            $page->changeTpl('xnet/register.success.tpl');
            $page->assign('hruid', $res['hruid']);
        } else {
            $page->changeTpl('platal/password.tpl');
            $page->assign('xnet', true);
            $page->assign('hruid', $res['hruid']);
            $page->assign('do_auth', true);
        }
    }

    function handler_edit($page)
    {
        global $globals;

        $user = S::user();
        if (empty($user)) {
            return PL_NOT_FOUND;
        }
        if ($user->type != 'xnet') {
            pl_redirect('index');
        }

        $page->changeTpl('xnet/edit.tpl');
        if (Post::has('change')) {
            S::assert_xsrf_token();

            // Convert user status to X
            if (!Post::blank('login_X')) {
                $forlife = $this->changeLogin($page, $user, Post::t('login_X'));
                if ($forlife) {
                    pl_redirect('index');
                }
            }

            // Update user info
            XDB::query('UPDATE  accounts
                           SET  full_name = {?}, directory_name = {?}, display_name = {?},
                                sex = {?}, email = {?}
                         WHERE  uid = {?}',
                       Post::t('full_name'), Post::t('directory_name'), Post::t('display_name'),
                       (Post::t('sex') == 'male') ? 'male' : 'female', Post::t('email'), $user->id());
            if (XDB::affectedRows()) {
                $user = User::getWithUID($user->id());
                S::set('user', $user);
                $page->trigSuccess('Données mises à jour.');
            }
        }

        $page->addJsLink('password.js');
        $page->assign('user', $user);
    }

    function handler_password ($page)
    {
        if (Post::has('pwhash') && Post::t('pwhash'))  {
            S::assert_xsrf_token();

            S::set('password', $password = Post::t('pwhash'));
            XDB::execute('UPDATE  accounts
                             SET  password = {?}
                           WHERE  uid={?}', $password,
                         S::i('uid'));
            S::logger()->log('passwd');
            Platal::session()->setAccessCookie(true);
            $page->changeTpl('platal/password.success.tpl');
            $page->run();
        }

        $page->changeTpl('platal/password.tpl');
        $page->assign('xnet_reset', true);
        $page->assign('do_auth', false);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
