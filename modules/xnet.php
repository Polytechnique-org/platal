<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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
            'index'        => $this->make_hook('index',        AUTH_PUBLIC),
            'exit'         => $this->make_hook('exit',         AUTH_PUBLIC),

            'login'        => $this->make_hook('login',        AUTH_COOKIE, 'groups'),
            'admin'        => $this->make_hook('admin',        AUTH_PASSWD, 'admin'),
            'groups'       => $this->make_hook('groups',       AUTH_PUBLIC),
            'groupes.php'  => $this->make_hook('groups2',      AUTH_PUBLIC),
            'plan'         => $this->make_hook('plan',         AUTH_PUBLIC),
            // Should be removed in a future release as links will have expired anyway.
            'register/ext' => $this->make_hook('register_ext', AUTH_PUBLIC),
            'photo'        => $this->make_hook('photo',        AUTH_PASSWD, 'groups'),
            'autologin'    => $this->make_hook('autologin',    AUTH_PASSWD, 'groups'),
            'edit'         => $this->make_hook('edit',         AUTH_PASSWD, 'groups'),
            'Xnet'         => $this->make_wiki_hook(),
        );
    }

    function handler_login()
    {
        // We require different credentials for '/login/' ("groups" instead of "user").
        // We have to redirect the call to the actual CoreModule->handler_login.
        $args = func_get_args();
        return call_user_func_array(array("CoreModule", "handler_login"), $args);
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
                'SELECT  dom.id, dom.nom as domnom, groups.diminutif, groups.nom, groups.status
                   FROM  group_dom AS dom
             INNER JOIN  groups ON dom.id = groups.dom
                  WHERE  FIND_IN_SET("GroupesX", dom.cat) AND FIND_IN_SET("GroupesX", groups.cat)
                         AND groups.status IN ("active", "inactive")
               ORDER BY  dom.nom, groups.status, groups.nom');
        $groupesx = array();
        while ($tmp = $res->next()) { $groupesx[$tmp['id']][] = $tmp; }
        $page->assign('groupesx', $groupesx);

        $res = XDB::iterator(
                'SELECT  dom.id, dom.nom as domnom, groups.diminutif, groups.nom, groups.status
                   FROM  group_dom AS dom
             INNER JOIN  groups ON dom.id = groups.dom
                  WHERE  FIND_IN_SET("Binets", dom.cat) AND FIND_IN_SET("Binets", groups.cat)
                         AND groups.status IN ("active", "inactive")
               ORDER BY  dom.nom, groups.status, groups.nom');
        $binets = array();
        while ($tmp = $res->next()) { $binets[$tmp['id']][] = $tmp; }
        $page->assign('binets', $binets);

        $res = XDB::iterator(
                'SELECT  diminutif, nom, status
                   FROM  groups
                  WHERE  cat LIKE "%Promotions%" AND status IN ("active", "inactive")
               ORDER BY  status, diminutif');
        $page->assign('promos', $res);

        $res = XDB::iterator(
                'SELECT  diminutif, nom, status
                   FROM  groups
                  WHERE  FIND_IN_SET("Institutions", cat) AND status IN ("active", "inactive")
               ORDER BY  status, diminutif');
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

        if ($cat == 'deads') {
            // Show dead groups
            $res = XDB::query("SELECT  diminutif, nom, site, status
                                 FROM  groups
                                WHERE  status = 'dead'
                                ORDER  BY nom", $cat);
            $page->assign('doms', array());
            $page->assign('gps', $res->fetchAllAssoc());
        } else {
            $res  = XDB::query("SELECT  id,nom
                                  FROM  group_dom
                                 WHERE  FIND_IN_SET({?}, cat)
                              ORDER BY  nom", $cat);
            $doms = $res->fetchAllAssoc();
            $page->assign('doms', $doms);

            if (empty($doms)) {
                $res = XDB::query("SELECT  diminutif, nom, site, status
                                     FROM  groups
                                    WHERE  FIND_IN_SET({?}, cat)
                                           AND status IN ('active', 'inactive')
                                    ORDER  BY status, nom", $cat);
                $page->assign('gps', $res->fetchAllAssoc());
            } elseif (!is_null($dom)) {
                $res = XDB::query("SELECT  diminutif, nom, site, status
                                     FROM  groups
                                    WHERE  FIND_IN_SET({?}, cat) AND dom={?}
                                           AND status IN ('active', 'inactive')
                                 ORDER BY  status,nom", $cat, $dom);
                $page->assign('gps', $res->fetchAllAssoc());
            }

            $page->setType($cat);
        }
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

            require_once 'emails.inc.php';
            require_once 'name.func.inc.php';

            // Update user info
            $lastname = capitalize_name(Post::t('lastname'));
            $firstname = capitalize_name(Post::t('firstname'));
            $full_name = build_full_name($firstname, $lastname);
            $directory_name = build_directory_name($firstname, $lastname);
            $sort_name = build_sort_name($firstname, $lastname);
            XDB::query('UPDATE  accounts
                           SET  full_name = {?}, directory_name = {?}, sort_name = {?}, display_name = {?},
                                firstname = {?}, lastname = {?}, sex = {?}
                         WHERE  uid = {?}',
                       $full_name, $directory_name, $sort_name, Post::t('display_name'),
                       Post::t('firstname'), Post::t('lastname'),
                       (Post::t('sex') == 'male') ? 'male' : 'female', $user->id());

            // Updates email.
            $new_email = strtolower(Post::t('email'));
            if (require_email_update($user, $new_email)) {
                    XDB::query('UPDATE  accounts
                                   SET  email = {?}
                                 WHERE  uid = {?}',
                               $new_email, $user->id());
                    $listClient = new MMList(S::user());
                    $listClient->change_user_email($user->forlifeEmail(), $new_email);
                    update_alias_user($user->forlifeEmail(), $new_email);
            }
            $user = User::getWithUID($user->id());
            S::set('user', $user);
            $page->trigSuccess('Données mises à jour.');
        }

        $page->addJsLink('password.js');
        $page->assign('user', $user);
    }

    function handler_register_ext($page, $hash = null)
    {
        http_redirect(Platal::globals()->xnet->xorg_baseurl . 'register/ext/' . $hash);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
