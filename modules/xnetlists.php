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

Platal::load('lists');

class XnetListsModule extends ListsModule
{
    var $client;

    function handlers()
    {
        return array(
            '%grp/lists'              => $this->make_hook('lists',    AUTH_MDP, 'groupmember'),
            '%grp/lists/create'       => $this->make_hook('create',   AUTH_MDP, 'groupmember'),

            '%grp/lists/members'      => $this->make_hook('members',  AUTH_COOKIE),
            '%grp/lists/csv'          => $this->make_hook('csv',      AUTH_COOKIE),
            '%grp/lists/annu'         => $this->make_hook('annu',     AUTH_COOKIE),
            '%grp/lists/archives'     => $this->make_hook('archives', AUTH_COOKIE),
            '%grp/lists/archives/rss' => $this->make_hook('rss',      AUTH_PUBLIC),

            '%grp/lists/moderate'     => $this->make_hook('moderate', AUTH_MDP),
            '%grp/lists/admin'        => $this->make_hook('admin',    AUTH_MDP),
            '%grp/lists/options'      => $this->make_hook('options',  AUTH_MDP),
            '%grp/lists/delete'       => $this->make_hook('delete',   AUTH_MDP),

            '%grp/lists/soptions'     => $this->make_hook('soptions', AUTH_MDP),
            '%grp/lists/check'        => $this->make_hook('check',    AUTH_MDP),
            '%grp/lists/sync'         => $this->make_hook('sync',     AUTH_MDP),

            '%grp/alias/admin'        => $this->make_hook('aadmin',   AUTH_MDP, 'groupadmin'),
            '%grp/alias/create'       => $this->make_hook('acreate',  AUTH_MDP, 'groupadmin'),

            /* hack: lists uses that */
            'profile'                 => $this->make_hook('profile',  AUTH_PUBLIC),
        );
    }

    function prepare_client($page, $user = null)
    {
        global $globals;
        Platal::load('lists', 'lists.inc.php');

        if (is_null($user)) {
            $user =& S::user();
        }
        $this->client = new MMList($user, $globals->asso('mail_domain'));

        $page->assign('asso', $globals->asso());
        $page->setType($globals->asso('cat'));

        return $globals->asso('mail_domain');
    }

    function handler_lists($page)
    {
        global $globals;

        if (!$globals->asso('mail_domain')) {
            return PL_NOT_FOUND;
        }
        $this->prepare_client($page);
        $page->changeTpl('xnetlists/index.tpl');

        if (Get::has('del')) {
            S::assert_xsrf_token();
            $this->client->unsubscribe(Get::v('del'));
            pl_redirect('lists');
        }
        if (Get::has('add')) {
            S::assert_xsrf_token();
            $this->client->subscribe(Get::v('add'));
            pl_redirect('lists');
        }

        if (Post::has('del_alias') && may_update()) {
            S::assert_xsrf_token();

            $alias = Post::v('del_alias');
            // prevent group admin from erasing aliases from other groups
            $alias = substr($alias, 0, strpos($alias, '@')).'@'.$globals->asso('mail_domain');
            XDB::query(
                    'DELETE FROM  r, v
                           USING  virtual AS v
                       LEFT JOIN  virtual_redirect AS r USING(vid)
                           WHERE  v.alias={?}', $alias);
            $page->trigSuccess(Post::v('del_alias')." supprimé&nbsp;!");
        }

        $listes = $this->client->get_lists();
        $page->assign('listes', $listes);

        $alias  = XDB::iterator(
                'SELECT  alias,type
                   FROM  virtual
                  WHERE  alias
                   LIKE  {?} AND type="user"
               ORDER BY  alias', '%@'.$globals->asso('mail_domain'));
        $page->assign('alias', $alias);

        $page->assign('may_update', may_update());

        if (count($listes) > 0 && !$globals->asso('has_ml')) {
            XDB::execute("UPDATE  groups
                             SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'has_ml')
                           WHERE  id = {?}",
                         $globals->asso('id'));
        }
    }

    function handler_create($page)
    {
        global $globals;

        if (!$globals->asso('mail_domain')) {
            return PL_NOT_FOUND;
        }
        $this->prepare_client($page);
        $page->changeTpl('xnetlists/create.tpl');

        if (!Post::has('submit')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        if (!Post::has('liste') || !Post::v('liste')) {
            $page->trigError('Le champs «&nbsp;adresse souhaitée&nbsp;» est vide.');
            return;
        }

        $liste = strtolower(Post::v('liste'));

        if (!preg_match("/^[a-zA-Z0-9\-]*$/", $liste)) {
            $page->trigError('le nom de la liste ne doit contenir que des lettres non accentuées, chiffres et tirets');
            return;
        }

        $new = $liste.'@'.$globals->asso('mail_domain');
        $res = XDB::query('SELECT alias FROM virtual WHERE alias={?}', $new);

        if ($res->numRows()) {
            $page->trigError('cet alias est déjà pris');
            return;
        }
        if (!Post::v('desc')) {
            $page->trigError('le sujet est vide');
            return;
        }

        $ret = $this->client->create_list(
                    $liste, utf8_decode(Post::v('desc')), Post::v('advertise'),
                    Post::v('modlevel'), Post::v('inslevel'),
                    array(S::user()->forlifeEmail()), array(S::user()->forlifeEmail()));

        $dom = strtolower($globals->asso("mail_domain"));
        $red = $dom.'_'.$liste;

        if (!$ret) {
            $page->kill("Un problème est survenu, contacter "
                        ."<a href='mailto:support@m4x.org'>support@m4x.org</a>");
            return;
        }
        foreach (array('', 'owner', 'admin', 'bounces', 'unsubscribe') as $app) {
            $mdir = $app == '' ? '+post' : '+' . $app;
            if (!empty($app)) {
                $app  = '-' . $app;
            }
            XDB::execute('INSERT INTO virtual (alias,type)
                                    VALUES({?},{?})', $liste. $app . '@'.$dom, 'list');
            XDB::execute('INSERT INTO virtual_redirect (vid,redirect)
                                    VALUES ({?}, {?})', XDB::insertId(),
                                   $red . $mdir . '@listes.polytechnique.org');
        }

        XDB::execute("UPDATE  groups
                         SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'has_ml')
                       WHERE  id = {?}",
                     $globals->asso('id'));

        pl_redirect('lists/admin/'.$liste);
    }

    function handler_sync($page, $liste = null)
    {
        global $globals;

        if (!$globals->asso('mail_domain')) {
            return PL_NOT_FOUND;
        }
        $this->prepare_client($page);
        $page->changeTpl('xnetlists/sync.tpl');

        if (Env::has('add')) {
            S::assert_xsrf_token();
            $this->client->mass_subscribe($liste, array_keys(Env::v('add')));
        }

        list(,$members) = $this->client->get_members($liste);
        $mails = array_map(create_function('$arr', 'return $arr[1];'), $members);
        $subscribers = array_unique($mails);

        $ann = XDB::fetchColumn('SELECT  uid
                                   FROM  group_members
                                  WHERE  asso_id = {?}', $globals->asso('id'));
        $users = User::getBulkUsersWithUIDs($ann);

        $not_in_list = array();
        foreach ($users as $user) {
            if (!in_array(strtolower($user->forlifeEmail()), $subscribers)) {
                $not_in_list[] = $user;
            }
        }

        $page->assign('not_in_list', $not_in_list);
    }

    function handler_aadmin($page, $lfull = null)
    {
        global $globals;

        if (!$globals->asso('mail_domain') || is_null($lfull)) {
            return PL_NOT_FOUND;
        }
        $page->changeTpl('xnetlists/alias-admin.tpl');

        if (Env::has('add_member')) {
            S::assert_xsrf_token();

            $add = Env::t('add_member');
            $user = User::getSilent($add);
            if ($user) {
                $add = $user->forlifeEmail();
            } else if (!User::isForeignEmailAddress($add)) {
                $add = null;
            }
            if (!empty($add)) {
                XDB::execute('INSERT INTO  virtual_redirect (vid, redirect)
                                   SELECT  vid, {?}
                                     FROM  virtual
                                    WHERE  alias = {?}', strtolower($add), $lfull);
                $page->trigSuccess($add . ' ajouté.');
            } else {
                $page->trigError($add . " n'existe pas.");
            }
        }

        if (Env::has('del_member')) {
            S::assert_xsrf_token();
            XDB::query(
                    "DELETE FROM  virtual_redirect
                           USING  virtual_redirect
                      INNER JOIN  virtual USING(vid)
                           WHERE  redirect={?} AND alias={?}", Env::v('del_member'), $lfull);
            pl_redirect('alias/admin/'.$lfull);
        }

        global $globals;
        $emails = XDB::fetchColumn('SELECT  redirect
                                      FROM  virtual_redirect AS vr
                                INNER JOIN  virtual          AS v  USING(vid)
                                     WHERE  v.alias = {?}
                                  ORDER BY  redirect', $lfull);
        $mem = array();
        foreach ($emails as $email) {
            $user = User::getSilent($email);
            if ($user) {
                $mem[] = array('user' => $user, 'email' => $email);
            } else {
                $mem[] = array('email' => $email);
            }
        }
        $page->assign('mem', $mem);
    }

    function handler_acreate($page)
    {
        global $globals;

        if (!$globals->asso('mail_domain')) {
            return PL_NOT_FOUND;
        }
        $page->changeTpl('xnetlists/alias-create.tpl');

        if (!Post::has('submit')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        if (!Post::has('liste')) {
            $page->trigError('Le champs «&nbsp;adresse souhaitée&nbsp;» est vide.');
            return;
        }
        $liste = Post::v('liste');
        if (!preg_match("/^[a-zA-Z0-9\-\.]*$/", $liste)) {
            $page->trigError('le nom de l\'alias ne doit contenir que des lettres,'
                            .' chiffres, tirets et points');
            return;
        }

        $new = $liste.'@'.$globals->asso('mail_domain');
        $res = XDB::query('SELECT COUNT(*) FROM virtual WHERE alias = {?}', $new);
        $n   = $res->fetchOneCell();
        if ($n) {
            $page->trigError('cet alias est déjà pris');
            return;
        }

        XDB::query('INSERT INTO virtual (alias,type) VALUES({?}, "user")', $new);

        pl_redirect("alias/admin/$new");
    }

    function handler_profile($page, $user = null)
    {
        http_redirect('https://www.polytechnique.org/profile/'.$user);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
