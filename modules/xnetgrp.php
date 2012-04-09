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


class XnetGrpModule extends PLModule
{
    function handlers()
    {
        return array(
            '%grp'                 => $this->make_hook('index',                 AUTH_PUBLIC),
            '%grp/asso.php'        => $this->make_hook('index',                 AUTH_PUBLIC),
            '%grp/logo'            => $this->make_hook('logo',                  AUTH_PUBLIC),
            '%grp/site'            => $this->make_hook('site',                  AUTH_PUBLIC),
            '%grp/edit'            => $this->make_hook('edit',                  AUTH_PASSWD, 'groupadmin'),
            '%grp/mail'            => $this->make_hook('mail',                  AUTH_PASSWD, 'groupadmin'),
            '%grp/forum'           => $this->make_hook('forum',                 AUTH_PASSWD, 'groupmember'),
            '%grp/former_users'    => $this->make_hook('former_users',          AUTH_PASSWD, 'admin'),
            '%grp/annuaire'        => $this->make_hook('annuaire',              AUTH_PASSWD, 'groupannu'),
            '%grp/annuaire/vcard'  => $this->make_hook('vcard',                 AUTH_PASSWD, 'groupmember:groupannu'),
            '%grp/annuaire/csv'    => $this->make_hook('csv',                   AUTH_PASSWD, 'groupmember:groupannu'),
            '%grp/directory/sync'  => $this->make_hook('directory_sync',        AUTH_PASSWD, 'groupadmin'),
            '%grp/directory/unact' => $this->make_hook('non_active',            AUTH_PASSWD, 'groupadmin'),
            '%grp/directory/awact' => $this->make_hook('awaiting_active',       AUTH_PASSWD, 'groupadmin'),
            '%grp/trombi'          => $this->make_hook('trombi',                AUTH_PASSWD, 'groupannu'),
            '%grp/geoloc'          => $this->make_hook('geoloc',                AUTH_PASSWD, 'groupannu'),
            '%grp/subscribe'       => $this->make_hook('subscribe',             AUTH_PASSWD, 'groups'),
            '%grp/subscribe/valid' => $this->make_hook('subscribe_valid',       AUTH_PASSWD, 'groupadmin'),
            '%grp/unsubscribe'     => $this->make_hook('unsubscribe',           AUTH_PASSWD, 'groupmember'),

            '%grp/change_rights'   => $this->make_hook('change_rights',         AUTH_PASSWD, 'groups'),
            '%grp/admin/annuaire'  => $this->make_hook('admin_annuaire',        AUTH_PASSWD, 'groupadmin'),
            '%grp/member'          => $this->make_hook('admin_member',          AUTH_PASSWD, 'groupadmin'),
            '%grp/member/new'      => $this->make_hook('admin_member_new',      AUTH_PASSWD, 'groupadmin'),
            '%grp/member/new/ajax' => $this->make_hook('admin_member_new_ajax', AUTH_PASSWD, 'groups', NO_AUTH),
            '%grp/member/del'      => $this->make_hook('admin_member_del',      AUTH_PASSWD, 'groupadmin'),
            '%grp/member/suggest'  => $this->make_hook('admin_member_suggest',  AUTH_PASSWD, 'groupadmin'),
            '%grp/member/reg'      => $this->make_hook('admin_member_reg',      AUTH_PASSWD, 'groupadmin'),

            '%grp/rss'             => $this->make_token_hook('rss',             AUTH_PUBLIC),
            '%grp/announce/new'    => $this->make_hook('edit_announce',         AUTH_PASSWD, 'groupadmin'),
            '%grp/announce/edit'   => $this->make_hook('edit_announce',         AUTH_PASSWD, 'groupadmin'),
            '%grp/announce/photo'  => $this->make_hook('photo_announce',        AUTH_PUBLIC),
            '%grp/admin/announces' => $this->make_hook('admin_announce',        AUTH_PASSWD, 'groupadmin'),
        );
    }

    function handler_index($page, $arg = null)
    {
        global $globals, $platal;
        if (!is_null($arg)) {
            return PL_NOT_FOUND;
        }
        $page->changeTpl('xnetgrp/asso.tpl');

        if (S::logged()) {
            if (Env::has('read')) {
                XDB::query('DELETE  r.*
                              FROM  group_announces_read AS r
                        INNER JOIN  group_announces      AS a ON (a.id = r.announce_id)
                             WHERE  expiration < CURRENT_DATE()');
                XDB::query('INSERT INTO  group_announces_read
                                 VALUES  ({?}, {?})',
                            Env::i('read'), S::i('uid'));
                pl_redirect("");
            }
            if (Env::has('unread')) {
                XDB::query('DELETE FROM  group_announces_read
                                  WHERE  announce_id = {?} AND uid = {?}',
                            Env::i('unread'), S::i('uid'));
                pl_redirect("#art" . Env::i('unread'));
            }

            /* TODO: refines this filter on promotions by using userfilter. */
            $user = S::user();
            if ($user->hasProfile()) {
                $promo = XDB::format('{?}', $user->profile()->entry_year);
                $minCondition = ' OR promo_min <= ' . $promo;
                $maxCondition = ' OR promo_max >= ' . $promo;
            } else {
                $minCondition = '';
                $maxCondition = '';
            }
            $arts = XDB::iterator('SELECT  a.*, FIND_IN_SET(\'photo\', a.flags) AS photo
                                     FROM  group_announces      AS a
                                LEFT JOIN  group_announces_read AS r ON (r.uid = {?} AND r.announce_id = a.id)
                                    WHERE  asso_id = {?} AND expiration >= CURRENT_DATE()
                                           AND (promo_min = 0' . $minCondition . ')
                                           AND (promo_max = 0' . $maxCondition . ')
                                           AND r.announce_id IS NULL
                                 ORDER BY  a.expiration',
                                   S::i('uid'), $globals->asso('id'));
            $index = XDB::iterator('SELECT  a.id, a.titre, r.uid IS NULL AS nonlu
                                      FROM  group_announces      AS a
                                 LEFT JOIN  group_announces_read AS r ON (a.id = r.announce_id AND r.uid = {?})
                                     WHERE  asso_id = {?} AND expiration >= CURRENT_DATE()
                                            AND (promo_min = 0' . $minCondition . ')
                                            AND (promo_max = 0' . $maxCondition . ')
                                  ORDER BY  a.expiration',
                                   S::i('uid'), $globals->asso('id'));
            $page->assign('article_index', $index);
        } else {
            $arts = XDB::iterator("SELECT  *, FIND_IN_SET('photo', flags) AS photo
                                     FROM  group_announces
                                    WHERE  asso_id = {?} AND expiration >= CURRENT_DATE()
                                           AND FIND_IN_SET('public', flags)",
                                  $globals->asso('id'));
            $payments = XDB::fetchAllAssoc("SELECT  id, text
                                              FROM  payments
                                             WHERE  asso_id = {?} AND NOT FIND_IN_SET('old', flags) AND FIND_IN_SET('public', flags)
                                          ORDER BY  id DESC",
                                           $globals->asso('id'));
            $page->assign('payments', $payments);
        }
        if (may_update()) {
            $subs_valid = XDB::query("SELECT  uid
                                        FROM  group_member_sub_requests
                                       WHERE  asso_id = {?}",
                                     $globals->asso('id'));
            $page->assign('requests', $subs_valid->numRows());
        }

        if (!S::hasAuthToken()) {
            $page->setRssLink("Polytechnique.net :: {$globals->asso("nom")} :: News publiques",
                              $platal->ns . "rss/rss.xml");
        } else {
            $page->setRssLink("Polytechnique.net :: {$globals->asso("nom")} :: News",
                              $platal->ns . 'rss/' . S::v('hruid') . '/' . S::user()->token . '/rss.xml');
        }

        $page->assign('articles', $arts);
    }

    function handler_logo($page)
    {
        global $globals;
        $globals->asso()->getLogo()->send();
    }

    function handler_site($page)
    {
        global $globals;
        $site = $globals->asso('site');
        if (!$site) {
            $page->trigError('Le groupe n\'a pas de site web.');
            return $this->handler_index($page);
        }
        http_redirect($site);
        exit;
    }

    function handler_edit($page)
    {
        global $globals;
        $page->changeTpl('xnetgrp/edit.tpl');
        $error = false;

        if (S::admin()) {
            $domains = XDB::iterator('SELECT  *
                                        FROM  group_dom
                                    ORDER BY  nom');
            $page->assign('domains', $domains);
            $page->assign('super', true);
        }

        if (Post::has('submit')) {
            S::assert_xsrf_token();

            $flags = new PlFlagSet('wiki_desc');
            $flags->addFlag('notif_unsub', Post::i('notif_unsub') == 1);
            $site = Post::t('site');
            if ($site && ($site != "http://")) {
                $scheme = parse_url($site, PHP_URL_SCHEME);
                if (!$scheme) {
                    $site = "http://" . $site;
                }
            } else {
                $site = "";
            }

            $notify_all = (Post::v('notify_all') ? true : false);
            if (!$notify_all) {
                $to_notify = array();
                $uf = New UserFilter(New UFC_Group($globals->asso('id'), true));
                $uids = $uf->getIds();
                foreach ($uids as $uid) {
                    if (Post::b('to_notify_' . $uid)) {
                        $to_notify[] = $uid;
                    }
                }
                if (count($to_notify) == 0) {
                    $notify_all = true;
                    $page->trigWarning("Aucun animateur n'ayant été selectionné pour recevoir les demandes d'inscriptions, tous le seront.");
                }
            }
            $flags->addFlag('notify_all', $notify_all);

            if (S::admin()) {
                $page->assign('super', true);

                if (Post::v('mail_domain') && (strstr(Post::v('mail_domain'), '.') === false)) {
                    $page->trigError('Le domaine doit être un FQDN (aucune modification effectuée)&nbsp;!!!');
                    $error = true;
                }
                if (Post::t('nom') == '' || Post::t('diminutif') == '') {
                    $page->trigError('Ni le nom ni le diminutif du groupe ne peuvent être vide.');
                    $error = true;
                }
                if ($error) {
                    $page->assign('nom', Post::t('nom'));
                    $page->assign('diminutif', Post::t('diminutif'));
                    $page->assign('mail_domain', Post::t('mail_domain'));
                    $page->assign('cat', Post::v('cat'));
                    $page->assign('dom', Post::v('dom'));
                    $page->assign('ax', Post::v('ax'));
                    $page->assign('axDate', Post::t('axDate'));
                    $page->assign('site', $site);
                    $page->assign('resp', Post::t('resp'));
                    $page->assign('mail', Post::t('mail'));
                    $page->assign('phone', Post::t('phone'));
                    $page->assign('fax', Post::t('fax'));
                    $page->assign('address', Post::t('address'));
                    $page->assign('forum', Post::t('forum'));
                    $page->assign('inscriptible', Post::v('inscriptible'));
                    $page->assign('sub_url', Post::t('sub_url'));
                    $page->assign('unsub_url', Post::t('unsub_url'));
                    $page->assign('welcome_msg', Post::t('welcome_msg'));
                    $page->assign('pub', Post::v('pub'));
                    $page->assign('notif_unsub', Post::i('notif_unsub'));
                    $page->assign('descr', Post::t('descr'));
                    $page->assign('error', $error);
                    return;
                }

                $axDate = make_datetime(Post::v('axDate'));
                if (Post::t('axDate') != '') {
                    $axDate = make_datetime(Post::v('axDate'))->format('Y-m-d');
                } else {
                    $axDate = null;
                }
                XDB::execute(
                    "UPDATE  groups
                        SET  nom={?}, diminutif={?}, cat={?}, dom={?},
                             descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, mail_domain={?}, ax={?}, axDate = {?}, pub={?},
                             sub_url={?}, inscriptible={?}, unsub_url={?},
                             flags = {?}, welcome_msg = {?}
                      WHERE  id={?}",
                      Post::v('nom'), Post::v('diminutif'),
                      Post::v('cat'), (Post::i('dom') == 0) ? null : Post::i('dom'),
                      Post::v('descr'), $site,
                      Post::v('mail'), Post::v('resp'),
                      Post::v('forum'), Post::v('mail_domain'),
                      Post::has('ax'), $axDate, Post::v('pub'),
                      Post::v('sub_url'), Post::v('inscriptible'),
                      Post::v('unsub_url'), $flags, Post::t('welcome_msg'),
                      $globals->asso('id'));
                if (Post::v('mail_domain')) {
                    XDB::execute('INSERT IGNORE INTO  email_virtual_domains (name)
                                              VALUES  ({?})',
                                 Post::t('mail_domain'));
                    XDB::execute('UPDATE  email_virtual_domains
                                     SET  aliasing = id
                                   WHERE  name = {?}',
                                 Post::t('mail_domain'));
                }
            } else {
                XDB::execute(
                    "UPDATE  groups
                        SET  descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, pub= {?}, sub_url={?},
                             unsub_url = {?}, flags = {?}, welcome_msg = {?}
                      WHERE  id={?}",
                      Post::v('descr'), $site,
                      Post::v('mail'), Post::v('resp'),
                      Post::v('forum'), Post::v('pub'),
                      Post::v('sub_url'), Post::v('unsub_url'),
                      $flags, Post::t('welcome_msg'),
                      $globals->asso('id'));
            }

            Phone::deletePhones(0, Phone::LINK_GROUP, $globals->asso('id'));
            $phone = new Phone(array('link_type' => 'group', 'link_id' => $globals->asso('id'), 'id' => 0,
                                     'type' => 'fixed', 'display' => Post::v('phone'), 'pub' => 'public'));
            $fax   = new Phone(array('link_type' => 'group', 'link_id' => $globals->asso('id'), 'id' => 1,
                                     'type' => 'fax', 'display' => Post::v('fax'), 'pub' => 'public'));
            $phone->save();
            $fax->save();
            Address::deleteAddresses(null, Address::LINK_GROUP, null, $globals->asso('id'));
            $address = new Address(array('groupid' => $globals->asso('id'), 'type' => Address::LINK_GROUP, 'text' => Post::v('address')));
            $address->save();

            if ($_FILES['logo']['name']) {
                $upload = PlUpload::get($_FILES['logo'], $globals->asso('id'), 'asso.logo', true);
                if (!$upload) {
                    $page->trigError("Impossible de télécharger le logo.");
                } else {
                    XDB::execute('UPDATE  groups
                                     SET  logo = {?}, logo_mime = {?}
                                   WHERE  id = {?}', $upload->getContents(), $upload->contentType(),
                                 $globals->asso('id'));
                    $upload->rm();
                }
            }

            XDB::execute("UPDATE  group_members
                             SET  flags = ''
                           WHERE  asso_id = {?}",
                         $globals->asso('id'));
            if (!$notify_all) {
                XDB::execute("UPDATE  group_members
                                 SET  flags = 'notify'
                               WHERE  asso_id = {?} AND uid IN {?}",
                             $globals->asso('id'), $to_notify);
            }

            pl_redirect('../' . Post::v('diminutif', $globals->asso('diminutif')) . '/edit');
        }

        $uf = New UserFilter(New UFC_Group($globals->asso('id'), true, UFC_Group::NOTIFIED));
        $page->assign('notified', $uf->getUsers());
        $uf = New UserFilter(New UFC_Group($globals->asso('id'), true, UFC_Group::UNNOTIFIED));
        $page->assign('unnotified', $uf->getUsers());

        $page->assign('error', $error);
        $page->assign('cat', $globals->asso('cat'));
        $page->assign('dom', $globals->asso('dom'));
        $page->assign('ax', $globals->asso('ax'));
        $page->assign('inscriptible', $globals->asso('inscriptible'));
        $page->assign('pub', $globals->asso('pub'));
        $page->assign('notif_unsub', $globals->asso('notif_unsub'));
        $page->assign('notify_all', $globals->asso('notify_all'));
    }

    function handler_mail($page)
    {
        global $globals;

        $page->changeTpl('xnetgrp/mail.tpl');
        $mmlist = new MMList(S::user(), $globals->asso('mail_domain'));
        $page->assign('listes', $mmlist->get_lists());
        $page->assign('user', S::user());

        if (Post::has('send')) {
            S::assert_xsrf_token();
            $from  = Post::v('from');
            $sujet = Post::v('sujet');
            $body  = Post::v('body');

            $mls = array_keys(Env::v('ml', array()));
            $mbr = array_keys(Env::v('membres', array()));

            $this->load('mail.inc.php');
            set_time_limit(120);
            $tos = get_all_redirects($mbr,  $mls, $mmlist);

            $upload = PlUpload::get($_FILES['uploaded'], S::user()->login(), 'xnet.emails', true);
            if (!$upload && @$_FILES['uploaded']['name'] && PlUpload::$lastError != null) {
                $page->trigError(PlUpload::$lastError);
                return;
            }

            send_xnet_mails($from, $sujet, $body, Env::v('wiki'), $tos, Post::v('replyto'), $upload, @$_FILES['uploaded']['name']);
            if ($upload) {
                $upload->rm();
            }
            $page->killSuccess("Email envoyé&nbsp;!");
            $page->assign('sent', true);
        }
    }

    function handler_forum($page, $group = null, $artid = null)
    {
        global $globals;
        $page->changeTpl('xnetgrp/forum.tpl');
        if (!$globals->asso('forum')) {
            return PL_NOT_FOUND;
        }
        require_once 'banana/forum.inc.php';
        $get = array();
        get_banana_params($get, $globals->asso('forum'), $group, $artid);
        run_banana($page, 'ForumsBanana', $get);
    }

    function handler_annuaire($page, $action = null, $subaction = null)
    {
        global $globals;

        __autoload('userset');
        $admins = false;
        if ($action == 'admins') {
            $admins = true;
            $action = $subaction;
        }
        $view = new UserSet(new UFC_Group($globals->asso('id'), $admins));
        $view->addMod('groupmember', 'Annuaire');
        $view->addMod('trombi', 'Trombinoscope');
        $view->addMod('map', 'Planisphère');
        $view->apply('annuaire', $page, $action);
        $page->assign('only_admin', $admins);
        $page->changeTpl('xnetgrp/annuaire.tpl');
    }

    function handler_former_users($page)
    {
        global $globals;
        require_once 'userset.inc.php';

        $view = new UserSet(new UFC_GroupFormerMember($globals->asso('id')));
        $view->addMod('groupmember', 'Anciens membres', true, array('noadmin' => true));
        $view->apply('former_users', $page);
        $page->changeTpl('xnetgrp/former_users.tpl');
    }

    function handler_trombi($page)
    {
        pl_redirect('annuaire/trombi');
    }

    function handler_geoloc($page)
    {
        pl_redirect('annuaire/geoloc');
    }

    function handler_vcard($page, $photos = null)
    {
        global $globals;
        $vcard = new VCard($photos == 'photos', 'Membre du groupe ' . $globals->asso('nom'));
        $vcard->addProfiles($globals->asso()->getMembersFilter()->getProfiles(null, Profile::FETCH_ALL));
        $vcard->show();
    }

    function handler_csv($page, $filename = null)
    {
        global $globals;
        if (is_null($filename)) {
            $filename = $globals->asso('diminutif') . '.csv';
        }
        $users = $globals->asso()->getMembersFilter(null, new UFO_Name())->getUsers();
        pl_cached_content_headers('text/x-csv', 'iso-8859-1', 1);

        echo utf8_decode("Nom;Prénom;Sexe;Promotion;Commentaire\n");
        foreach ($users as $user) {
            $line = $user->lastName() . ';' . $user->firstName() . ';' . ($user->isFemale() ? 'F' : 'M')
                  . ';' . $user->promo() . ';' . strtr($user->group_comm, ';', ',');
            echo utf8_decode($line) . "\n";
        }
        exit();
    }

    function handler_directory_sync($page)
    {
        global $globals;
        require_once 'emails.inc.php';

        $page->changeTpl('xnetgrp/sync.tpl');
        Platal::load('lists', 'lists.inc.php');

        if (Env::has('add_users')) {
            S::assert_xsrf_token();

            $users = array_keys(Env::v('add_users'));
            $former_users = XDB::fetchColumn('SELECT  uid
                                                FROM  group_former_members
                                               WHERE  remember = TRUE AND uid IN {?}',
                                             $users);
            $new_users = array_diff($users, $former_users);

            foreach ($former_users as $uid) {
                $user = User::getSilentWithUID($uid);
                $page->trigWarning($user->fullName() . ' est un ancien membre du groupe qui ne souhaite pas y revenir.');
            }
            if (count($former_users) > 1) {
                $page->trigWarning('S\'ils souhaitent revenir dans le groupe, il faut qu\'ils en fassent la demande sur la page d\'accueil du groupe.');
            } elseif (count($former_users)) {
                $page->trigWarning('S\'il souhaite revenir dans le groupe, il faut qu\'il en fasse la demande sur la page d\'accueil du groupe.');
            }

            $data = array();
            foreach ($new_users as $uid) {
                $data[] = XDB::format('({?}, {?})', $globals->asso('id'), $uid);
            }
            XDB::rawExecute('INSERT INTO  group_members (asso_id, uid)
                                  VALUES  ' . implode(',', $data));
        }

        if (Env::has('add_nonusers')) {
            S::assert_xsrf_token();

            $nonusers = array_keys(Env::v('add_nonusers'));
            foreach ($nonusers as $email) {
                if ($user = User::getSilent($email) || !isvalid_email($email)) {
                    continue;
                }

                list($local_part, $domain) = explode('@', strtolower($email));
                $hruid = User::makeHrid($local_part, $domain, 'ext');
                if ($user = User::getSilent($hruid)) {
                    continue;
                }

                require_once 'name.func.inc.php';
                $parts = explode('.', $local_part);
                if (count($parts) == 1) {
                    $lastname = $display_name = capitalize_name($mbox);
                    $firstname = '';
                } else {
                    $display_name = $firstname = capitalize_name($parts[0]);
                    $lastname = capitalize_name(implode(' ', array_slice($parts, 1)));
                }
                $full_name = build_full_name($firstname, $lastname);
                $directory_name = build_directory_name($firstname, $lastname);
                $sort_name = build_sort_name($firstname, $lastname);
                XDB::execute('INSERT INTO  accounts (hruid, display_name, full_name, directory_name, sort_name,
                                                     firstname, lastname, email, type, state)
                                   VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, \'xnet\', \'disabled\')',
                             $hruid, $display_name, $full_name, $directory_name, $sort_name, $firstname, $lastname, $email);
                $uid = XDB::insertId();
                XDB::execute('INSERT INTO  group_members (asso_id, uid)
                                   VALUES  ({?}, {?})',
                             $globals->asso('id'), $uid);
            }
        }

        if (Env::has('add_users') || Env::has('add_nonusers')) {
            $page->trigSuccess('Ajouts réalisés avec succès.');
        }

        $user = S::user();
        $client = new MMList($user, $globals->asso('mail_domain'));
        $lists = $client->get_lists();
        $members = array();
        foreach ($lists as $list) {
            $details = $client->get_members($list['list']);
            $members = array_merge($members, list_extract_members($details[1]));
        }
        $members = array_unique($members);
        $uids = array();
        $users = array();
        $nonusers = array();
        foreach ($members as $email) {
            if ($user = User::getSilent($email)) {
                $uids[] = $user->id();
            } else {
                $nonusers[] = $email;
            }
        }

        $aliases = iterate_list_alias($globals->asso('mail_domain'));
        foreach ($aliases as $alias) {
            list($local_part, $domain) = explode('@', $alias);
            $aliases_members = list_alias_members($local_part, $domain);
            $users = array_merge($users, $aliases_members['users']);
            $nonusers = array_merge($nonusers, $aliases_members['nonusers']);
        }
        foreach ($users as $user) {
            $uids[] = $user->id();
        }
        $nonusers = array_unique($nonusers);
        $uids = array_unique($uids);
        if (count($uids)) {
            $uids = XDB::fetchColumn('SELECT  a.uid
                                        FROM  accounts AS a
                                       WHERE  a.uid IN {?} AND NOT EXISTS (SELECT  *
                                                                             FROM  group_members AS g
                                                                            WHERE  a.uid = g.uid AND g.asso_id = {?})',
                                     $uids, $globals->asso('id'));

            $users = User::getBulkUsersWithUIDs($uids);
            usort($users, 'User::compareDirectoryName');
        } else {
            $users = array();
        }
        sort($nonusers);

        $page->assign('users', $users);
        $page->assign('nonusers', $nonusers);
    }

    function handler_non_active($page)
    {
        global $globals;
        $page->changeTpl('xnetgrp/non_active.tpl');

        $uids = XDB::fetchColumn('SELECT  g.uid
                                    FROM  group_members         AS g
                              INNER JOIN  accounts              AS a ON (a.uid = g.uid)
                               LEFT JOIN  register_pending_xnet AS p ON (p.uid = g.uid)
                                   WHERE  a.uid = g.uid AND g.asso_id = {?} AND a.type = \'xnet\' AND a.state = \'disabled\' AND p.uid IS NULL',
                                 $globals->asso('id'));
        foreach ($uids as $key => $uid) {
            if (AccountReq::isPending($uid) || BulkAccountsReq::isPending($uid)) {
                unset($uids[$key]);
            }
        }

        if (Post::has('enable_accounts')) {
            S::assert_xsrf_token();

            $uids_to_enable = array_intersect(array_keys(Post::v('enable_accounts')), $uids);

            $user = S::user();
            $request = new BulkAccountsReq($user, $uids_to_enable, $globals->asso('nom'), $globals->asso('diminutif'));
            $request->submit();
            $page->trigSuccess('Un email va bientôt être envoyé aux personnes sélectionnées pour l\'activation de leur compte.');

            foreach ($uids as $key => $uid) {
                if (in_array($uid, $uids_to_enable)) {
                    unset($uids[$key]);
                }
            }
        }

        $users = User::getBulkUsersWithUIDs($uids);
        $page->assign('users', $users);
    }

    private function again($uid)
    {
        $data = XDB::fetchOneAssoc('SELECT  hash, group_name, sender_name, email
                                      FROM  register_pending_xnet
                                     WHERE  uid = {?}',
                                   $uid);
        XDB::execute('UPDATE  register_pending_xnet
                         SET  last_date = NOW()
                       WHERE  uid = {?}',
                     $uid);

        $mailer = new PlMailer('xnet/account.mail.tpl');
        $mailer->addCc('validation+xnet_account@polytechnique.org');
        $mailer->setTo($data['email']);
        $mailer->assign('hash', $data['hash']);
        $mailer->assign('email', $data['email']);
        $mailer->assign('group', $data['group_name']);
        $mailer->assign('sender_name', $data['sender_name']);
        $mailer->assign('again', true);
        $mailer->assign('baseurl', Platal::globals()->xnet->xorg_baseurl);
        $mailer->send();
    }

    function handler_awaiting_active($page)
    {
        global $globals;
        $page->changeTpl('xnetgrp/awaiting_active.tpl');

        XDB::execute('DELETE FROM  register_pending_xnet
                            WHERE  DATE_SUB(NOW(), INTERVAL 1 MONTH) > date');

        $uids = XDB::fetchColumn('SELECT  g.uid
                                    FROM  group_members         AS g
                              INNER JOIN  accounts              AS a ON (a.uid = g.uid)
                              INNER JOIN  register_pending_xnet AS p ON (p.uid = g.uid)
                                   WHERE  a.uid = g.uid AND g.asso_id = {?} AND a.type = \'xnet\' AND a.state = \'pending\'',
                                 $globals->asso('id'));

        if (Post::has('again')) {
            S::assert_xsrf_token();

            $uids_to_again = array_intersect(array_keys(Post::v('again')), $uids);
            foreach ($uids_to_again as $uid) {
                $this->again($uid);
            }
            $page->trigSuccess('Relances effectuées avec succès.');
        }

        $registration_date = XDB::fetchAllAssoc('uid', 'SELECT  uid, date
                                                          FROM  register_pending_xnet
                                                         WHERE  uid IN {?}', $uids);
        $last_date = XDB::fetchAllAssoc('uid', 'SELECT  uid, last_date
                                                  FROM  register_pending_xnet
                                                 WHERE  uid IN {?}', $uids);

        $users = User::getBulkUsersWithUIDs($uids);
        $page->assign('users', $users);
        $page->assign('registration_date', $registration_date);
        $page->assign('last_date', $last_date);

    }

    private function removeSubscriptionRequest($uid)
    {
        global $globals;
        XDB::execute("DELETE FROM group_member_sub_requests
                            WHERE asso_id = {?} AND uid = {?}",
                     $globals->asso('id'), $uid);
    }

    private function validSubscription(User $user)
    {
        global $globals;
        $this->removeSubscriptionRequest($user->id());
        Group::subscribe($globals->asso('id'), $user->id());

        if (XDB::affectedRows() == 1) {
            $mailer = new PlMailer();
            $mailer->addTo($user->forlifeEmail());
            $mailer->setFrom('"' . S::user()->fullName() . '" <' . S::user()->forlifeEmail() . '>');
            $mailer->setSubject('[' . $globals->asso('nom') . '] Demande d\'inscription');
            $message = ($user->isFemale() ? 'Chère' : 'Cher') . " Camarade,\n"
                     . "\n"
                     . "  Suite à ta demande d'adhésion à " . $globals->asso('nom')
                     . ", j'ai le plaisir de t'annoncer que ton inscription a été validée !\n"
                     . (is_null($globals->asso('welcome_msg')) ? '' : "\n" . $globals->asso('welcome_msg') . "\n")
                     . "\n"
                     . "Bien cordialement,\n"
                     . "-- \n"
                     . S::user()->fullName() . '.';
            $mailer->setTxtBody(wordwrap($message, 72));
            $mailer->send();
        }
    }

    function handler_subscribe($page, $u = null)
    {
        global $globals;
        $page->changeTpl('xnetgrp/inscrire.tpl');

        if (!$globals->asso('inscriptible'))
                $page->kill("Il n'est pas possible de s'inscire en ligne à ce "
                            ."groupe. Essaie de joindre le contact indiqué "
                            ."sur la page de présentation.");

        if (!is_null($u) && may_update()) {
            $user = User::get($u);
            if (!$user) {
                return PL_NOT_FOUND;
            } else {
                $page->assign('user', $user);
            }

            // Retrieves the subscription status, and the reason.
            $res = XDB::query("SELECT  reason
                                 FROM  group_member_sub_requests
                                WHERE  asso_id = {?} AND uid = {?}",
                              $globals->asso('id'), $user->id());
            $reason = ($res->numRows() ? $res->fetchOneCell() : null);

            $res = XDB::query("SELECT  COUNT(*)
                                 FROM  group_members
                                WHERE  asso_id = {?} AND uid = {?}",
                              $globals->asso('id'), $user->id());
            $already_member = ($res->fetchOneCell() > 0);

            // Handles the membership request.
            if ($already_member) {
                $this->removeSubscriptionRequest($user->id());
                $page->kill($user->fullName() . ' est déjà membre du groupe&nbsp;!');
            } elseif (Env::has('accept')) {
                S::assert_xsrf_token();

                $this->validSubscription($user);
                pl_redirect("member/" . $user->login());
            } elseif (Env::has('refuse')) {
                S::assert_xsrf_token();

                $this->removeSubscriptionRequest($user->id());
                $mailer = new PlMailer();
                $mailer->addTo($user->forlifeEmail());
                $mailer->setFrom('"' . S::user()->fullName() . '" <' . S::user()->forlifeEmail() . '>');
                $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription annulée');
                $mailer->setTxtBody(Env::v('motif'));
                $mailer->send();
                $page->killSuccess("La demande de {$user->fullName()} a bien été refusée.");
            } else {
                $page->assign('show_form', true);
                $page->assign('reason', $reason);
            }
            return;
        }

        if (is_member()) {
            $page->kill("Tu es déjà membre&nbsp;!");
            return;
        }

        $res = XDB::query("SELECT  uid
                             FROM  group_member_sub_requests
                            WHERE  uid = {?} AND asso_id = {?}",
                         S::i('uid'), $globals->asso('id'));
        if ($res->numRows() != 0) {
            $page->kill("Tu as déjà demandé ton inscription à ce groupe. Cette demande est actuellement en attente de validation.");
            return;
        }

        if (Post::has('inscrire')) {
            S::assert_xsrf_token();

            XDB::execute("INSERT INTO  group_member_sub_requests (asso_id, uid, ts, reason)
                               VALUES  ({?}, {?}, NOW(), {?})",
                         $globals->asso('id'), S::i('uid'), Post::v('message'));
            XDB::execute('DELETE FROM  group_former_members
                                WHERE  uid = {?} AND asso_id = {?}',
                         S::i('uid'), $globals->asso('id'));
            $admins = $globals->asso()->iterToNotify();
            $admin = $admins->next();
            $to = $admin->bestEmail();
            while ($admin = $admins->next()) {
                $to .= ', ' . $admin->bestEmail();
            }

            $append = "\n"
                    . "-- \n"
                    . "Ce message a été envoyé suite à la demande d'inscription de\n"
                    . S::user()->fullName(true) . "\n"
                    . "Via le site www.polytechnique.net. Tu peux choisir de valider ou\n"
                    . "de refuser sa demande d'inscription depuis la page :\n"
                    . "http://www.polytechnique.net/" . $globals->asso("diminutif") . "/subscribe/" . S::user()->login() . "\n"
                    . "\n"
                    . "En cas de problème, contacter l'équipe de Polytechnique.org\n"
                    . "à l'adresse : support@polytechnique.org\n";

            if (!$to) {
                $to = ($globals->asso('mail') != '') ? $globals->asso('mail') . ', ' : '';
                $to .= 'support@polytechnique.org';
                $append = "\n-- \nLe groupe ".$globals->asso("nom")
                        ." n'a pas d'administrateur, l'équipe de"
                        ." Polytechnique.org a été prévenue et va rapidement"
                        ." résoudre ce problème.\n";
            }

            $mailer = new PlMailer();
            $mailer->addTo($to);
            $mailer->setFrom('"' . S::user()->fullName() . '" <' . S::user()->forlifeEmail() . '>');
            $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription');
            $mailer->setTxtBody(Post::v('message').$append);
            $mailer->send();
        }
    }

    function handler_subscribe_valid($page)
    {
        global $globals;

        if (Post::has('valid')) {
            S::assert_xsrf_token();
            $subs = Post::v('subs');
            if (is_array($subs)) {
                $users = array();
                foreach ($subs as $hruid => $val) {
                    if ($val == '1') {
                        $user = User::get($hruid);
                        if ($user) {
                            $this->validSubscription($user);
                        }
                    }
                }
            }
        }

        $it = XDB::iterator('SELECT  s.uid, a.hruid, s.ts AS date
                               FROM  group_member_sub_requests AS s
                         INNER JOIN  accounts AS a ON(s.uid = a.uid)
                              WHERE  s.asso_id = {?}
                           ORDER BY  s.ts',  $globals->asso('id'));
        $page->changeTpl('xnetgrp/subscribe-valid.tpl');
        $page->assign('valid', $it);
    }

    function handler_change_rights($page)
    {
        if (Env::has('right') && (may_update() || S::suid())) {
            switch (Env::v('right')) {
              case 'admin':
                Platal::session()->stopSUID();
                break;
              case 'anim':
                Platal::session()->doSelfSuid();
                may_update(true);
                is_member(true);
                break;
              case 'member':
                Platal::session()->doSelfSuid();
                may_update(false, true);
                is_member(true);
                break;
              case 'logged':
                Platal::session()->doSelfSuid();
                may_update(false, true);
                is_member(false, true);
                break;
            }
        }
        http_redirect($_SERVER['HTTP_REFERER']);
    }

    function handler_admin_annuaire($page)
    {
        global $globals;

        $this->load('mail.inc.php');
        $page->changeTpl('xnetgrp/annuaire-admin.tpl');
        $user = S::user();
        $mmlist = new MMList($user, $globals->asso('mail_domain'));
        $lists  = $mmlist->get_lists();
        if (!$lists) $lists = array();
        $listes = array_map(create_function('$arr', 'return $arr["list"];'), $lists);

        $subscribers = array();

        foreach ($listes as $list) {
            list(,$members) = $mmlist->get_members($list);
            $mails = array_map(create_function('$arr', 'return $arr[1];'), $members);
            $subscribers = array_unique(array_merge($subscribers, $mails));
        }

        $not_in_group_x = array();
        $not_in_group_ext = array();

        foreach ($subscribers as $mail) {
            $uf = new UserFilter(new PFC_And(new UFC_Group($globals->asso('id')),
                                             new UFC_Email($mail)));
            if ($uf->getTotalCount() == 0) {
                if (User::isForeignEmailAddress($mail)) {
                    $not_in_group_ext[] = $mail;
                } else {
                    $not_in_group_x[] = $mail;
                }
            }
        }

        $page->assign('not_in_group_ext', $not_in_group_ext);
        $page->assign('not_in_group_x', $not_in_group_x);
        $page->assign('lists', $lists);
    }

    function handler_admin_member_new($page, $email = null)
    {
        global $globals;

        $page->changeTpl('xnetgrp/membres-add.tpl');
        $page->addJsLink('xnet_members.js');

        if (is_null($email)) {
            return;
        }

        S::assert_xsrf_token();
        $suggest_account_activation = false;

        // FS#703 : $_GET is urldecoded twice, hence
        // + (the data) => %2B (in the url) => + (first decoding) => ' ' (second decoding)
        // Since there can be no spaces in emails, we can fix this with :
        $email = str_replace(' ', '+', $email);
        $is_valid_email = isvalid_email($email);

        // X not registered to main site.
        if (Env::v('x') && Env::i('userid') && $is_valid_email) {
            $user = User::getSilentWithUID(Env::i('userid'));
            if (!$user) {
                $page->trigError('Utilisateur invalide.');
                return;
            }

            // User has an account but is not yet registered.
            if ($user->state == 'pending') {
                // Add email in account table.
                XDB::query('UPDATE  accounts
                               SET  email = {?}
                             WHERE  uid = {?} AND email IS NULL',
                           $email, $user->id());
                // Add email for marketing if required.
                if (Env::v('marketing')) {
                    $market = Marketing::get($user->uid, $email);
                    if (!$market) {
                        $market = new Marketing($user->uid, $email, 'group', $globals->asso('nom'),
                                                Env::v('marketing_from'), S::v('uid'));
                        $market->add();
                    }
                }
            } elseif (Env::v('broken')) {
                // Add email for broken if required.
                $valid = new BrokenReq(S::user(), $user, $email, 'Groupe : ' . $globals->asso('nom'));
                $valid->submit();
            }
        } else {
            $user = User::getSilent($email);

            // Wrong email and no user: failure.
            if (is_null($user) && (!$is_valid_email || !User::isForeignEmailAddress($email))) {
                $page->trigError('«&nbsp;<strong>' . $email . '</strong>&nbsp;» n\'est pas une adresse email valide.');
                return;
            }

            // Deals with xnet accounts.
            if (is_null($user) || $user->type == 'xnet') {
                // User is of type xnet. There are 3 possible cases:
                //  * the email is not known yet: we create a new account and
                //      propose to send an email to the user so he can activate
                //      his account,
                //  * the email is known but the user was not contacted in order to
                //      activate yet: we propose to send an email to the user so he
                //      can activate his account,
                //  * the email is known and the user was already contacted or has
                //      an active account: nothing to be done.
                list($mbox, $domain) = explode('@', strtolower($email));
                $hruid = User::makeHrid($mbox, $domain, 'ext');
                // User might already have an account (in another group for example).
                $user = User::getSilent($hruid);

                // If the user has no account yet, creates new account: build names from email address.
                if (empty($user)) {
                    require_once 'name.func.inc.php';
                    $parts = explode('.', $mbox);
                    if (count($parts) == 1) {
                        $lastname = $display_name = capitalize_name($mbox);
                        $firstname = '';
                    } else {
                        $display_name = $firstname = capitalize_name($parts[0]);
                        $lastname = capitalize_name(implode(' ', array_slice($parts, 1)));
                    }
                    $full_name = build_full_name($firstname, $lastname);
                    $directory_name = build_directory_name($firstname, $lastname);
                    $sort_name = build_sort_name($firstname, $lastname);
                    XDB::execute('INSERT INTO  accounts (hruid, display_name, full_name, directory_name, sort_name,
                                                         firstname, lastname, email, type, state)
                                       VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, \'xnet\', \'disabled\')',
                                 $hruid, $display_name, $full_name, $directory_name, $sort_name, $firstname, $lastname, $email);
                    $user = User::getSilent($hruid);
                }

                $suggest_account_activation = $this->suggest($user);
            }
        }

        if ($user) {
            // First check if the user used to be in this group.
            XDB::rawExecute('DELETE FROM  group_former_members
                                   WHERE  remember AND DATE_SUB(NOW(), INTERVAL 1 YEAR) > unsubsciption_date');
            $former_member = XDB::fetchOneCell('SELECT  remember
                                                  FROM  group_former_members
                                                 WHERE  uid = {?} AND asso_id = {?}',
                                               $user->id(), $globals->asso('id'));
            if ($former_member === 1) {
                $page->trigError($user->fullName() . ' est un ancien membre du groupe qui ne souhaite pas y revenir. S\'il souhaite revenir dans le groupe, il faut qu\'il en fasse la demande sur la page d\'accueil du groupe.');
                return;
            } elseif (!is_null($former_member) && Post::i('force_continue') == 0) {
                $page->trigWarning($user->fullName() . ' est un ancien membre du groupe qui s\'est récemment désinscrit. Malgré cela, si tu penses qu\'il souhaite revenir, cliquer sur « Ajouter » l\'ajoutera bien au groupe cette fois.');
                $page->assign('force_continue', 1);
                return;
            }

            Group::subscribe($globals->asso('id'), $user->id());
            $this->removeSubscriptionRequest($user->id());
            if ($suggest_account_activation) {
                pl_redirect('member/suggest/' . $user->login() . '/' . $email . '/' . $globals->asso('nom'));
            } else {
                pl_redirect('member/' . $user->login());
            }
        }
    }

    // Check if the user has a pending or active account, and thus if we should her account's activation.
    private function suggest(PlUser $user)
    {
        $active = XDB::fetchOneCell('SELECT  state = \'active\'
                                       FROM  accounts
                                      WHERE  uid = {?}',
                                    $user->id());
        $pending = XDB::fetchOneCell('SELECT  uid
                                        FROM  register_pending_xnet
                                       WHERE  uid = {?}',
                                     $user->id());
        $requested = AccountReq::isPending($user->id()) || BulkAccountsReq::isPending($user->id());

        if ($active || $pending || $requested) {
            return false;
        }
        return true;
    }

    function handler_admin_member_suggest($page, $hruid, $email)
    {
        $page->changeTpl('xnetgrp/membres-suggest.tpl');

        // FS#703 : $_GET is urldecoded twice, hence
        // + (the data) => %2B (in the url) => + (first decoding) => ' ' (second decoding)
        // Since there can be no spaces in emails, we can fix this with :
        $email = str_replace(' ', '+', $email);

        if (Post::has('suggest')) {
            if (Post::t('suggest') == 'yes') {
                global $globals;

                $user = S::user();
                $request = new AccountReq($user, $hruid, $email, $globals->asso('nom'), $globals->asso('diminutif'));
                $request->submit();
                $page->trigSuccessRedirect('Un email va bien être envoyé à ' . $email . ' pour l\'activation de son compte.',
                                           $globals->asso('diminutif') . '/member/' . $hruid);
            } else {
                pl_redirect('member/' . $hruid);
            }
        }
        $page->assign('email', $email);
        $page->assign('hruid', $hruid);
    }

    function handler_admin_member_reg($page, $uid)
    {
        pl_content_headers('text/plain');

        $user = User::getSilentWithUID($uid);
        if ($user && $user->state != 'pending' && $user->hasProfile()) {
            echo true;
        }
        echo false;
        exit();
    }

    function handler_admin_member_new_ajax($page)
    {
        pl_content_headers("text/html");
        $page->changeTpl('xnetgrp/membres-new-search.tpl', NO_SKIN);
        $users = array();
        $same_email = false;
        if (Env::has('login')) {
            $user = User::getSilent(Env::t('login'));
            if ($user && $user->state != 'pending') {
                $users = array($user->id() => $user);
                $same_email = true;
            }
        }
        if (empty($users)) {
            list($lastname, $firstname) = str_replace(array('-', ' ', "'"), '%', array(Env::t('nom'), Env::t('prenom')));
            $cond = new PFC_And();
            if (!empty($lastname)) {
                $cond->addChild(new UFC_NameTokens($lastname, array(), false, false, Profile::LASTNAME));
            }
            if (!empty($firstname)) {
                $cond->addChild(new UFC_NameTokens($firstname, array(), false, false, Profile::FIRSTNAME));
            }
            if (Env::t('promo')) {
                $cond->addChild(new UFC_Promo('=', UserFilter::DISPLAY, Env::t('promo')));
            }
            $uf = new UserFilter($cond);
            $users = $uf->getUsers(new PlLimit(30));
            if ($uf->getTotalCount() > 30) {
                $page->assign('too_many', true);
                $users = array();
            }
        }

        $page->assign('users', $users);
        $page->assign('same_email', $same_email);
    }

    function unsubscribe(PlUser $user, $remember = false)
    {
        global $globals;
        Group::unsubscribe($globals->asso('id'), $user->id(), $remember);

        if ($globals->asso('notif_unsub')) {
            $mailer = new PlMailer('xnetgrp/unsubscription-notif.mail.tpl');
            $admins = $globals->asso()->iterToNotify();
            while ($admin = $admins->next()) {
                $mailer->addTo($admin);
            }
            $mailer->assign('group', $globals->asso('nom'));
            $mailer->assign('user', $user);
            $mailer->assign('selfdone', $user->id() == S::i('uid'));
            $mailer->send();
        }

        $domain = $globals->asso('mail_domain');
        if (!$domain) {
            return true;
        }

        $mmlist = new MMList(S::user(), $domain);
        $listes = $mmlist->get_lists($user->forlifeEmail());

        $may_update = may_update();
        $warning    = false;
        if (is_array($listes)) {
            foreach ($listes as $liste) {
                if ($liste['sub'] == 2) {
                    if ($may_update) {
                        $mmlist->mass_unsubscribe($liste['list'], Array($user->forlifeEmail()));
                    } else {
                        $mmlist->unsubscribe($liste['list']);
                    }
                } elseif ($liste['sub']) {
                    Platal::page()->trigWarning($user->fullName() . " a une"
                                               ." demande d'inscription en cours sur la"
                                               ." liste {$liste['list']}@ !");
                    $warning = true;
                }
            }
        }

        XDB::execute('DELETE  v
                        FROM  email_virtual         AS v
                  INNER JOIN  email_virtual_domains AS d ON (v.domain = d.id)
                       WHERE  v.redirect = {?} AND d.name = {?}',
                     $user->forlifeEmail(), $domain);
        return !$warning;
    }

    function handler_unsubscribe($page)
    {
        $page->changeTpl('xnetgrp/membres-del.tpl');
        $user = S::user();
        if (empty($user)) {
            return PL_NOT_FOUND;
        }
        $page->assign('self', true);
        $page->assign('user', $user);

        if (!Post::has('confirm')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        $hasSingleGroup = ($user->groupCount() == 1);

        if ($this->unsubscribe($user, Post::b('remember'))) {
            $page->trigSuccess('Tu as été désinscrit du groupe avec succès.');
        } else {
            $page->trigWarning('Tu as été désinscrit du groupe, mais des erreurs se sont produites lors des désinscriptions des alias et des listes de diffusion.');
        }

        // If user is of type xnet account and this was her last group, disable the account.
        if ($user->type == 'xnet' && $hasSingleGroup) {
            $user->clear(true);
        }
        $page->assign('is_member', is_member(true));
    }

    function handler_admin_member_del($page, $user = null)
    {
        $page->changeTpl('xnetgrp/membres-del.tpl');
        $user = User::getSilent($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }

        global $globals;

        if (!$user->inGroup($globals->asso('id'))) {
            pl_redirect('annuaire');
        }

        $page->assign('self', false);
        $page->assign('user', $user);

        if (!Post::has('confirm')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        $hasSingleGroup = ($user->groupCount() == 1);

        if ($this->unsubscribe($user)) {
            $page->trigSuccess("{$user->fullName()} a été désinscrit du groupe&nbsp;!");
        } else {
            $page->trigWarning("{$user->fullName()} a été désinscrit du groupe, mais des erreurs subsistent&nbsp;!");
        }

        // If user is of type xnet account and this was her last group, disable the account.
        if ($user->type == 'xnet' && $hasSingleGroup) {
            $user->clear(true);
        }
    }

    private function changeLogin(PlPage $page, PlUser $user, $login, $req_broken = false, $req_marketing = false, $marketing_from = 'user')
    {
        // Search the user's uid.
        $xuser = User::getSilent($login);
        if (!$xuser) {
            $accounts = User::getPendingAccounts($login);
            if (!$accounts) {
                $page->trigError("L'identifiant $login ne correspond à aucun X.");
                return false;
            } else if (count($accounts) > 1) {
                $page->trigError("L'identifiant $login correspond à plusieurs camarades.");
                return false;
            }
            $xuser = User::getSilent($accounts[0]['uid']);
        }

        if (!$xuser) {
            return false;
        }

        // Market or suggest new redirection if required.
        $email = $user->bestEmail();
        if ($req_broken) {
            $valid = new BrokenReq(S::user(), $xuser, $email, 'Groupe : ' . Platal::globals()->asso('nom'));
            $valid->submit();
        } elseif ($req_marketing) {
            $market = Marketing::get($xuser->uid, $email);
            if (!$market) {
                $market = new Marketing($xuser->uid, $email, 'group', Platal::globals()->asso('nom'), $marketing_from, S::i('uid'));
                $market->add();
            }
        }

        if ($user->mergeIn($xuser)) {
            return $xuser->login();
        }
        return $user->login();
    }

    function handler_admin_member($page, $user)
    {
        global $globals;

        $user = User::getSilent($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }

        if (!$user->inGroup($globals->asso('id'))) {
            pl_redirect('annuaire');
        }

        $page->changeTpl('xnetgrp/membres-edit.tpl');
        $page->addJsLink('xnet_members.js');

        $mmlist = new MMList(S::user(), $globals->asso('mail_domain'));

        if (Post::has('change')) {
            S::assert_xsrf_token();
            require_once 'emails.inc.php';
            require_once 'name.func.inc.php';

            // Convert user status to X
            if (!Post::blank('x')) {
                $forlife = $this->changeLogin($page, $user, Post::i('userid'), Post::b('broken'), Post::b('marketing'), Post::v('marketing_from'));
                if ($forlife) {
                    pl_redirect('member/' . $forlife);
                }
            }

            // Update user info
            if ($user->type == 'virtual' || ($user->type == 'xnet' && !$user->perms)) {
                $lastname = capitalize_name(Post::t('lastname'));
                if (Post::s('type') != 'virtual') {
                    $firstname = capitalize_name(Post::t('firstname'));
                } else {
                    $firstname = '';
                }
                $full_name = build_full_name($firstname, $lastname);
                $directory_name = build_directory_name($firstname, $lastname);
                $sort_name = build_sort_name($firstname, $lastname);
                XDB::query('UPDATE  accounts
                               SET  full_name = {?}, directory_name = {?}, sort_name = {?}, display_name = {?},
                                    firstname = {?}, lastname = {?}, sex = {?}, type = {?}
                             WHERE  uid = {?}',
                           $full_name, $directory_name, $sort_name, Post::t('display_name'), $firstname, $lastname,
                           (Post::t('sex') == 'male') ? 'male' : 'female',
                           (Post::t('type') == 'xnet') ? 'xnet' : 'virtual', $user->id());
            }

            // Updates email.
            $new_email = strtolower(Post::t('email'));
            if (($user->type == 'virtual' || ($user->type == 'xnet' && !$user->perms))
                && require_email_update($user, $new_email)) {
                XDB::query('UPDATE  accounts
                               SET  email = {?}
                             WHERE  uid = {?}',
                           $new_email, $user->id());
                if ($user->forlifeEmail()) {
                    $listClient = new MMList(S::user());
                    $listClient->change_user_email($user->forlifeEmail(), $new_email);
                    update_alias_user($user->forlifeEmail(), $new_email);
                }
                $user = User::getWithUID($user->id());
            }
            if (XDB::affectedRows()) {
                $page->trigSuccess('Données de l\'utilisateur mises à jour.');
            }

            if (($user->type == 'xnet' && !$user->perms)) {
                if (Post::b('suggest')) {
                    $request = new AccountReq(S::user(), $user->hruid, Post::t('email'), $globals->asso('nom'), $globals->asso('diminutif'));
                    $request->submit();
                    $page->trigSuccess('Le compte va bientôt être activé.');
                }
                if (Post::b('again')) {
                    $this->again($user->id());
                    $page->trigSuccess('Relance effectuée avec succès.');
                }
            }

            // Update group params for user
            $perms = Post::v('group_perms');
            $comm  = Post::t('comm');
            $position = (Post::t('group_position') == '') ? null : Post::v('group_position');
            if ($user->group_perms != $perms || $user->group_comm != $comm || $user->group_position != $position) {
                XDB::query('UPDATE  group_members
                               SET  perms = {?}, comm = {?}, position = {?}
                             WHERE  uid = {?} AND asso_id = {?}',
                            ($perms == 'admin') ? 'admin' : 'membre', $comm, $position,
                            $user->id(), $globals->asso('id'));
                if (XDB::affectedRows()) {
                    if ($perms != $user->group_perms) {
                        $page->trigSuccess('Permissions modifiées&nbsp;!');
                    }
                    if ($comm != $user->group_comm) {
                        $page->trigSuccess('Commentaire mis à jour.');
                    }
                    if ($position != $user->group_position) {
                        $page->trigSuccess('Poste mis à jour.');
                    }
                }
            }

            // Gets user info again as they might have change
            $user = User::getSilent($user->id());

            // Update ML subscriptions
            foreach (Env::v('ml1', array()) as $ml => $state) {
                $ask = empty($_REQUEST['ml2'][$ml]) ? 0 : 2;
                if ($ask == $state) {
                    continue;
                }
                if ($state == '1') {
                    $page->trigWarning("{$user->fullName()} a "
                               ."actuellement une demande d'inscription en "
                               ."cours sur <strong>$ml@</strong> !!!");
                } elseif ($ask) {
                    $mmlist->mass_subscribe($ml, Array($user->forlifeEmail()));
                    $page->trigSuccess("{$user->fullName()} a été abonné à $ml@.");
                } else {
                    $mmlist->mass_unsubscribe($ml, Array($user->forlifeEmail()));
                    $page->trigSuccess("{$user->fullName()} a été désabonné de $ml@.");
                }
            }

            // Change subscriptioin to aliases
            foreach (Env::v('ml3', array()) as $ml => $state) {
                require_once 'emails.inc.php';
                $ask = !empty($_REQUEST['ml4'][$ml]);
                list($local_part, ) = explode('@', $ml);
                if ($ask == $state) {
                    continue;
                }
                if ($ask) {
                    add_to_list_alias($user->id(), $local_part, $globals->asso('mail_domain'));
                    $page->trigSuccess("{$user->fullName()} a été abonné à $ml.");
                } else {
                    delete_from_list_alias($user->id(), $local_part, $globals->asso('mail_domain'));
                    $page->trigSuccess("{$user->fullName()} a été désabonné de $ml.");
                }
            }

            if ($globals->asso('has_nl')) {
                $nl = NewsLetter::forGroup($globals->asso('shortname'));
                // Updates group's newsletter subscription.
                if (Post::i('newsletter') == 1) {
                    $nl->subscribe($user);
                } else {
                    $nl->unsubscribe(null, $user->id());
                }
            }
        }

        $res = XDB::rawFetchAllAssoc('SHOW COLUMNS FROM group_members LIKE \'position\'');
        $positions = str_replace(array('enum(', ')', '\''), '', $res[0]['Type']);
        if ($globals->asso('has_nl')) {
            $nl = NewsLetter::forGroup($globals->asso('shortname'));
            $nl_registered = $nl->subscriptionState($user);
        } else {
            $nl_registered = false;
        }

        $page->assign('user', $user);
        $page->assign('suggest', $this->suggest($user));
        $page->assign('listes', $mmlist->get_lists($user->forlifeEmail()));
        $page->assign('alias', $user->emailGroupAliases($globals->asso('mail_domain')));
        $page->assign('positions', explode(',', $positions));
        $page->assign('nl_registered', $nl_registered);
        $page->assign('pending_xnet_account', XDB::fetchOneCell('SELECT  1
                                                                   FROM  register_pending_xnet
                                                                  WHERE  uid = {?}',
                                                                $user->id()));
    }

    function handler_rss(PlPage $page, PlUser $user)
    {
        global $globals;
        $page->assign('asso', $globals->asso());

        $this->load('feed.inc.php');
        $feed = new XnetGrpEventFeed();
        return $feed->run($page, $user, false);
    }

    private function upload_image(PlPage $page, PlUpload $upload)
    {
        if (@!$_FILES['image']['tmp_name'] && !Env::v('image_url')) {
            return true;
        }
        if (!$upload->upload($_FILES['image'])  && !$upload->download(Env::v('image_url'))) {
            $page->trigError('Impossible de télécharger l\'image');
            return false;
        } elseif (!$upload->isType('image')) {
            $page->trigError('Le fichier n\'est pas une image valide au format JPEG, GIF ou PNG.');
            $upload->rm();
            return false;
        } elseif (!$upload->resizeImage(80, 100, 100, 100, 32284)) {
            $page->trigError('Impossible de retraiter l\'image');
            return false;
        }
        return true;
    }

    function handler_photo_announce($page, $eid = null) {
        if ($eid) {
            $res = XDB::query('SELECT  *
                                 FROM  group_announces_photo
                                WHERE  eid = {?}', $eid);
            if ($res->numRows()) {
                $photo = $res->fetchOneAssoc();
                pl_cached_dynamic_content_headers("image/" . $photo['attachmime']);
                echo $photo['attach'];
                exit;
            }
        } else {
            $upload = new PlUpload(S::user()->login(), 'xnetannounce');
            if ($upload->exists() && $upload->isType('image')) {
                pl_cached_dynamic_content_headers($upload->contentType());
                echo $upload->getContents();
                exit;
            }
        }
        global $globals;
        pl_cached_dynamic_content_headers("image/png");
        echo file_get_contents($globals->spoolroot . '/htdocs/images/logo.png');
        exit;
    }

    function handler_edit_announce($page, $aid = null)
    {
        global $globals, $platal;
        $page->changeTpl('xnetgrp/announce-edit.tpl');
        $page->assign('new', is_null($aid));
        $art = array();

        if (Post::v('valid') == 'Visualiser' || Post::v('valid') == 'Enregistrer'
            || Post::v('valid') == 'Supprimer l\'image' || Post::v('valid') == 'Pas d\'image') {
            S::assert_xsrf_token();

            if (!is_null($aid)) {
                $art['id'] = $aid;
            }
            $art['titre']      = Post::v('titre');
            $art['texte']      = Post::v('texte');
            $art['contacts']   = Post::v('contacts');
            $art['promo_min']  = Post::i('promo_min');
            $art['promo_max']  = Post::i('promo_max');
            $art['nom']        = S::v('nom');
            $art['prenom']     = S::v('prenom');
            $art['promo']      = S::v('promo');
            $art['hruid']      = S::user()->login();
            $art['uid']        = S::user()->id();
            $art['expiration'] = Post::v('expiration');
            $art['public']     = Post::has('public');
            $art['xorg']       = Post::has('xorg');
            $art['nl']         = Post::has('nl');
            $art['event']      = Post::v('event');
            $upload     = new PlUpload(S::user()->login(), 'xnetannounce');
            $this->upload_image($page, $upload);

            $art['contact_html'] = $art['contacts'];
            if ($art['event']) {
                $art['contact_html'] .= "\n{$globals->baseurl}/{$platal->ns}events/sub/{$art['event']}";
            }

            if (!$art['public'] &&
                (($art['promo_min'] > $art['promo_max'] && $art['promo_max'] != 0) ||
                 ($art['promo_min'] != 0 && ($art['promo_min'] <= 1900 || $art['promo_min'] >= 2020)) ||
                 ($art['promo_max'] != 0 && ($art['promo_max'] <= 1900 || $art['promo_max'] >= 2020))))
            {
                $page->trigError("L'intervalle de promotions est invalide.");
                Post::kill('valid');
            }

            if (!trim($art['titre']) || !trim($art['texte'])) {
                $page->trigError("L'article doit avoir un titre et un contenu.");
                Post::kill('valid');
            }

            if (Post::v('valid') == 'Supprimer l\'image') {
                $upload->rm();
                Post::kill('valid');
            }
            $art['photo'] = $upload->exists() || Post::i('photo');
            if (Post::v('valid') == 'Pas d\'image' && !is_null($aid)) {
                XDB::query('DELETE FROM  group_announces_photo
                                  WHERE  eid = {?}', $aid);
                $upload->rm();
                Post::kill('valid');
                $art['photo'] = false;
            }
        }

        if (Post::v('valid') == 'Enregistrer') {
            $promo_min = ($art['public'] ? 0 : $art['promo_min']);
            $promo_max = ($art['public'] ? 0 : $art['promo_max']);
            $flags = new PlFlagSet();
            if ($art['public']) {
                $flags->addFlag('public');
            }
            if ($art['photo']) {
                $flags->addFlag('photo');
            }
            if (is_null($aid)) {
                $fulltext = $art['texte'];
                if (!empty($art['contact_html'])) {
                    $fulltext .= "\n\n'''Contacts :'''\\\\\n" . $art['contact_html'];
                }
                $post = null;
                if ($globals->asso('forum')) {
                    require_once 'banana/forum.inc.php';
                    $banana = new ForumsBanana(S::user());
                    $post = $banana->post($globals->asso('forum'), null,
                                          $art['titre'], MiniWiki::wikiToText($fulltext, false, 0, 80));
                }
                XDB::query('INSERT INTO  group_announces (uid, asso_id, create_date, titre, texte, contacts,
                                                          expiration, promo_min, promo_max, flags, post_id)
                                 VALUES  ({?}, {?}, NOW(), {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})',
                           S::i('uid'), $globals->asso('id'), $art['titre'], $art['texte'], $art['contact_html'],
                           $art['expiration'], $promo_min, $promo_max, $flags, $post);
                $aid = XDB::insertId();
                if ($art['photo']) {
                    list($imgx, $imgy, $imgtype) = $upload->imageInfo();
                    XDB::execute('INSERT INTO  group_announces_photo
                                          SET  eid = {?}, attachmime = {?}, x = {?}, y = {?}, attach = {?}',
                                 $aid, $imgtype, $imgx, $imgy, $upload->getContents());
                }
                if ($art['xorg']) {
                    $article = new EvtReq("[{$globals->asso('nom')}] " . $art['titre'], $fulltext,
                                    $art['promo_min'], $art['promo_max'], $art['expiration'], "", S::user(),
                                    $upload);
                    $article->submit();
                    $page->trigWarning("L'affichage sur la page d'accueil de Polytechnique.org est en attente de validation.");
                } else if ($upload && $upload->exists()) {
                    $upload->rm();
                }
                if ($art['nl']) {
                    $article = new NLReq(S::user(), $globals->asso('nom') . " : " .$art['titre'],
                                         $art['texte'], $art['contact_html']);
                    $article->submit();
                    $page->trigWarning("La parution dans la Lettre Mensuelle est en attente de validation.");
                }
            } else {
                XDB::query('UPDATE  group_announces
                               SET  titre = {?}, texte = {?}, contacts = {?}, expiration = {?},
                                    promo_min = {?}, promo_max = {?}, flags = {?}
                             WHERE  id = {?} AND asso_id = {?}',
                           $art['titre'], $art['texte'], $art['contacts'], $art['expiration'],
                           $promo_min, $promo_max,  $flags,
                           $art['id'], $globals->asso('id'));
                if ($art['photo'] && $upload->exists()) {
                    list($imgx, $imgy, $imgtype) = $upload->imageInfo();
                    XDB::execute('INSERT INTO  group_announces_photo (eid, attachmime, attach, x, y)
                                       VALUES  ({?}, {?}, {?}, {?}, {?})
                      ON DUPLICATE KEY UPDATE  attachmime = VALUES(attachmime), attach = VALUES(attach), x = VALUES(x), y = VALUES(y)',
                                 $aid, $imgtype, $upload->getContents(), $imgx, $imgy);
                    $upload->rm();
                }
            }
        }
        if (Post::v('valid') == 'Enregistrer' || Post::v('valid') == 'Annuler') {
            pl_redirect("");
        }

        if (empty($art) && !is_null($aid)) {
            $res = XDB::query("SELECT  *, FIND_IN_SET('public', flags) AS public,
                                       FIND_IN_SET('photo', flags) AS photo
                                 FROM  group_announces
                                WHERE  asso_id = {?} AND id = {?}",
                              $globals->asso('id'), $aid);
            if ($res->numRows()) {
                $art = $res->fetchOneAssoc();
                $art['contact_html'] = $art['contacts'];
            } else {
                $page->kill("Aucun article correspond à l'identifiant indiqué.");
            }
        }

        if (is_null($aid)) {
            $events = XDB::iterator("SELECT *
                                      FROM group_events
                                     WHERE asso_id = {?} AND archive = 0",
                                   $globals->asso('id'));
            if ($events->total()) {
                $page->assign('events', $events);
            }
        }

        $art['contact_html'] = @MiniWiki::WikiToHTML($art['contact_html']);
        $page->assign('art', $art);
        $page->assign_by_ref('upload', $upload);
    }

    function handler_admin_announce($page)
    {
        global $globals;
        $page->changeTpl('xnetgrp/announce-admin.tpl');

        if (Env::has('del')) {
            S::assert_xsrf_token();
            XDB::execute('DELETE FROM  group_announces
                                WHERE  id = {?} AND asso_id = {?}',
                         Env::i('del'), $globals->asso('id'));
        }
        $res = XDB::iterator('SELECT  id, titre, expiration, expiration < CURRENT_DATE() AS perime
                                FROM  group_announces
                               WHERE  asso_id = {?}
                            ORDER BY  expiration DESC',
                             $globals->asso('id'));
        $page->assign('articles', $res);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
