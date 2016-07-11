<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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
    function handlers()
    {
        return array(
            '%grp/lists'              => $this->make_hook('lists',    AUTH_PASSWD, 'groupmember'),
            '%grp/lists/create'       => $this->make_hook('create',   AUTH_PASSWD, 'groupmember'),

            '%grp/lists/members'      => $this->make_hook('members',  AUTH_COOKIE, 'groups'),
            '%grp/lists/csv'          => $this->make_hook('csv',      AUTH_COOKIE, 'groups'),
            '%grp/lists/annu'         => $this->make_hook('annu',     AUTH_COOKIE, 'groups'),
            '%grp/lists/archives'     => $this->make_hook('archives', AUTH_COOKIE, 'groups'),
            '%grp/lists/archives/rss' => $this->make_hook('rss',      AUTH_PUBLIC),

            '%grp/lists/moderate'     => $this->make_hook('moderate', AUTH_PASSWD, 'groups'),
            '%grp/lists/admin'        => $this->make_hook('admin',    AUTH_PASSWD, 'groups'),
            '%grp/lists/options'      => $this->make_hook('options',  AUTH_PASSWD, 'groups'),
            '%grp/lists/delete'       => $this->make_hook('delete',   AUTH_PASSWD, 'groups'),

            '%grp/lists/soptions'     => $this->make_hook('soptions', AUTH_PASSWD, 'groups'),
            '%grp/lists/check'        => $this->make_hook('check',    AUTH_PASSWD, 'groups'),
            '%grp/lists/sync'         => $this->make_hook('sync',     AUTH_PASSWD, 'groups'),

            '%grp/alias/admin'        => $this->make_hook('aadmin',   AUTH_PASSWD, 'groupadmin'),
            '%grp/alias/create'       => $this->make_hook('acreate',  AUTH_PASSWD, 'groupadmin'),

            /* hack: lists uses that */
            'profile'                 => $this->make_hook('profile',  AUTH_PUBLIC),
        );
    }

    protected function get_lists_domain()
    {
        global $globals;
        return $globals->asso('mail_domain');
    }

    function handler_lists($page, $order_by = null, $order = null)
    {
        require_once 'emails.inc.php';

        if (!$this->get_lists_domain()) {
            return PL_NOT_FOUND;
        }
        $page->changeTpl('xnetlists/index.tpl');

        if (Get::has('del')) {
            S::assert_xsrf_token();
            $mlist = $this->prepare_list(Get::v('del'));
            $mlist->unsubscribe();
            pl_redirect('lists');
        }
        if (Get::has('add')) {
            S::assert_xsrf_token();
            $mlist = $this->prepare_list(Get::v('add'));
            $mlist->subscribe();
            pl_redirect('lists');
        }

        if (Post::has('del_alias') && may_update()) {
            S::assert_xsrf_token();

            $alias = Post::t('del_alias');
            list($local_part, ) = explode('@', $alias);
            delete_list_alias($local_part, $this->get_lists_domain());
            $page->trigSuccess($alias . ' supprimé&nbsp;!');
        }

        $client = $this->prepare_client();
        $listes = $client->get_lists();
        // Default ordering is by ascending names.
        if (is_null($order_by) || is_null($order)
            || !in_array($order_by, array('list', 'desc', 'nbsub'))
            || !in_array($order, array('asc', 'desc'))) {
            $order_by = 'list';
            $order = 'asc';
        }

        $compare = function ($a, $b) use ($order_by, $order)
        {
            switch ($order_by) {
              case 'desc':
                $a[$order_by] = replace_accent($a[$order_by]);
                $b[$order_by] = replace_accent($b[$order_by]);
              case 'list':
                $res = strcasecmp($a[$order_by], $b[$order_by]);
                break;
              case 'nbsub':
                $res = $a[$order_by] - $b[$order_by];
                break;
              default:
                $res = 0;
            }

            if ($order == 'asc') {
                return $res;
            }
            return $res * -1;
        };
        usort($listes, $compare);
        $page->assign('listes', $listes);
        $page->assign('order_by', $order_by);
        $page->assign('order', $order);
        $page->assign('aliases', iterate_list_alias($this->get_lists_domain()));
        $page->assign('may_update', may_update());
        if (S::suid()) {
            $page->trigWarning("Attention&nbsp;: l'affichage des listes de diffusion ne tient pas compte de l'option « Voir le site comme&hellip; ».");
        }

        global $globals;
        if (count($listes) > 0 && !$globals->asso('has_ml')) {
            XDB::execute("UPDATE  groups
                             SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'has_ml')
                           WHERE  id = {?}",
                         $globals->asso('id'));
        }
    }

    function handler_create($page)
    {
        if (!$this->get_lists_domain()) {
            return PL_NOT_FOUND;
        }
        $page->changeTpl('xnetlists/create.tpl');

        if (!Post::has('submit')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        if (!Post::has('liste') || !Post::t('liste')) {
            $page->trigError('Le champs «&nbsp;adresse souhaitée&nbsp;» est vide.');
            return;
        }

        $list = strtolower(Post::t('liste'));
        if (!preg_match("/^[a-zA-Z0-9\-]*$/", $list)) {
            $page->trigError('le nom de la liste ne doit contenir que des lettres non accentuées, chiffres et tirets');
            return;
        }

        require_once 'emails.inc.php';
        if (list_exist($list, $this->get_lists_domain())) {
            $page->trigError('Cet alias est déjà pris.');
            return;
        }
        if (!Post::t('desc')) {
            $page->trigError('Le sujet est vide.');
            return;
        }

        $mlist = $this->prepare_list($list);
        $success = MailingList::create($mlist->mbox, $mlist->domain, S::user(),
            Post::t('desc'),
            Post::t('advertise'), Post::t('modlevel'), Post::t('inslevel'),
            array(S::user()->forlifeEmail()), array(S::user()->forlifeEmail()));

        if (!$success) {
            $page->kill("Un problème est survenu, contacter "
                        ."<a href='mailto:support@m4x.org'>support@m4x.org</a>");
            return;
        }
        create_list($mlist->mbox, $mlist->domain);

        global $globals;
        XDB::execute("UPDATE  groups
                         SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'has_ml')
                       WHERE  id = {?}",
                     $globals->asso('id'));

        pl_redirect('lists/admin/' . $list);
    }

    function handler_sync($page, $liste = null)
    {
        if (!$this->get_lists_domain()) {
            return PL_NOT_FOUND;
        }
        if (!$liste) {
            return PL_NOT_FOUND;
        }

        $page->changeTpl('xnetlists/sync.tpl');

        $mlist = $this->prepare_list($liste);

        if (Env::has('add')) {
            S::assert_xsrf_token();
            $mlist->subscribeBulk(array_keys(Env::v('add')));
        }

        list(,$members) = $mlist->getMembers();
        $mails = array_map(create_function('$arr', 'return $arr[1];'), $members);
        $subscribers = array_unique($mails);

        global $globals;
        $ann = XDB::fetchColumn('SELECT  uid
                                   FROM  group_members
                                  WHERE  asso_id = {?}', $globals->asso('id'));
        $users = User::getBulkUsersWithUIDs($ann);

        $not_in_list = array();
        foreach ($users as $user) {
            if (!in_array(strtolower($user->forlifeEmail()), $subscribers) && $user->isActive()) {
                $not_in_list[] = $user;
            }
        }

        $page->assign('not_in_list', $not_in_list);
    }

    function handler_aadmin($page, $lfull = null)
    {
        if (!$this->get_lists_domain() || is_null($lfull)) {
            return PL_NOT_FOUND;
        }
        $page->changeTpl('xnetlists/alias-admin.tpl');

        require_once 'emails.inc.php';
        list($local_part, $domain) = explode('@', $lfull);
        if ($this->get_lists_domain() != $domain || !preg_match("/^[a-zA-Z0-9\-\.]*$/", $local_part)) {
            global $globals;
            $page->trigErrorRedirect('Le nom de l\'alias est erroné.', $globals->asso('diminutif') . '/lists');
        }


        if (Env::has('add_member')) {
            S::assert_xsrf_token();

            if (add_to_list_alias(Env::t('add_member'), $local_part, $domain)) {
                $page->trigSuccess('Ajout réussi.');
            } else {
                $page->trigError('Ajout infructueux.');
            }
        }

        if (Env::has('del_member')) {
            S::assert_xsrf_token();

            if (delete_from_list_alias(Env::t('del_member'), $local_part, $domain)) {
                $page->trigSuccess('Suppression réussie.');
            } else {
                $page->trigError('Suppression infructueuse.');
            }
        }

        $page->assign('members', list_alias_members($local_part, $domain));
    }

    function handler_acreate($page)
    {
        if (!$this->get_lists_domain()) {
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
        $list = Post::v('liste');
        if (!preg_match("/^[a-zA-Z0-9\-\.]*$/", $list)) {
            $page->trigError('Le nom de l\'alias ne doit contenir que des lettres,'
                            .' chiffres, tirets et points.');
            return;
        }

        require_once 'emails.inc.php';
        $lists_domain = $this->get_lists_domain();
        if (list_exist($list, $lists_domain)) {
            $page->trigError('Cet alias est déjà pris.');
            return;
        }

        add_to_list_alias(S::i('uid'), $list, $lists_domain);
        pl_redirect('alias/admin/' . $list . '@' . $lists_domain);
    }

    function handler_profile($page, $user = null)
    {
        http_redirect('https://www.polytechnique.org/profile/'.$user);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
