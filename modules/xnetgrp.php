<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
            '%grp/edit'            => $this->make_hook('edit',                  AUTH_MDP,    'groupadmin'),
            '%grp/mail'            => $this->make_hook('mail',                  AUTH_MDP,    'groupadmin'),
            '%grp/forum'           => $this->make_hook('forum',                 AUTH_MDP,    'groupmember'),
            '%grp/annuaire'        => $this->make_hook('annuaire',              AUTH_MDP,    'groupannu'),
            '%grp/annuaire/vcard'  => $this->make_hook('vcard',                 AUTH_MDP,    'groupmember:groupannu'),
            '%grp/annuaire/csv'    => $this->make_hook('csv',                   AUTH_MDP,    'groupmember:groupannu'),
            '%grp/trombi'          => $this->make_hook('trombi',                AUTH_MDP,    'groupannu'),
            '%grp/geoloc'          => $this->make_hook('geoloc',                AUTH_MDP,    'groupannu'),
            '%grp/subscribe'       => $this->make_hook('subscribe',             AUTH_MDP),
            '%grp/subscribe/valid' => $this->make_hook('subscribe_valid',       AUTH_MDP,    'groupadmin'),
            '%grp/unsubscribe'     => $this->make_hook('unsubscribe',           AUTH_MDP,    'groupmember'),

            '%grp/change_rights'   => $this->make_hook('change_rights',         AUTH_MDP),
            '%grp/admin/annuaire'  => $this->make_hook('admin_annuaire',        AUTH_MDP,    'groupadmin'),
            '%grp/member'          => $this->make_hook('admin_member',          AUTH_MDP,    'groupadmin'),
            '%grp/member/new'      => $this->make_hook('admin_member_new',      AUTH_MDP,    'groupadmin'),
            '%grp/member/new/ajax' => $this->make_hook('admin_member_new_ajax', AUTH_MDP,    'user', NO_AUTH),
            '%grp/member/del'      => $this->make_hook('admin_member_del',      AUTH_MDP,    'groupadmin'),

            '%grp/rss'             => $this->make_hook('rss',                   AUTH_PUBLIC, 'user', NO_HTTPS),
            '%grp/announce/new'    => $this->make_hook('edit_announce',         AUTH_MDP,    'groupadmin'),
            '%grp/announce/edit'   => $this->make_hook('edit_announce',         AUTH_MDP,    'groupadmin'),
            '%grp/announce/photo'  => $this->make_hook('photo_announce',        AUTH_PUBLIC),
            '%grp/admin/announces' => $this->make_hook('admin_announce',        AUTH_MDP,    'groupadmin'),
        );
    }

    function handler_index(&$page, $arg = null)
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
            // XXX: Fix promo_min; promo_max
            $arts = XDB::iterator("SELECT  a.*, FIND_IN_SET('photo', a.flags) AS photo
                                     FROM  group_announces      AS a
                                LEFT JOIN  group_announces_read AS r ON (r.uid = {?} AND r.announce_id = a.id)
                                    WHERE  asso_id = {?} AND expiration >= CURRENT_DATE()
                                           AND (promo_min = 0 OR promo_min <= {?})
                                           AND (promo_max = 0 OR promo_max >= {?})
                                           AND r.announce_id IS NULL
                                 ORDER BY  a.expiration",
                                   S::i('uid'), $globals->asso('id'), S::i('promo'), S::i('promo'));
            $index = XDB::iterator("SELECT  a.id, a.titre, r.uid IS NULL AS nonlu
                                      FROM  group_announces      AS a
                                 LEFT JOIN  group_announces_read AS r ON (a.id = r.announce_id AND r.uid = {?})
                                     WHERE  asso_id = {?} AND expiration >= CURRENT_DATE()
                                            AND (promo_min = 0 OR promo_min <= {?})
                                            AND (promo_max = 0 OR promo_max >= {?})
                                  ORDER BY  a.expiration",
                                   S::i('uid'), $globals->asso('id'), S::i('promo'), S::i('promo'));
            $page->assign('article_index', $index);
        } else {
            $arts = XDB::iterator("SELECT  *, FIND_IN_SET('photo', flags) AS photo
                                     FROM  group_announces
                                    WHERE  asso_id = {?} AND expiration >= CURRENT_DATE()
                                           AND FIND_IN_SET('public', flags)",
                                  $globals->asso('id'));
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
                              $platal->ns . 'rss/'.S::v('hruid') .'/'.S::v('token').'/rss.xml');
        }

        $page->assign('articles', $arts);
    }

    function handler_logo(&$page)
    {
        global $globals;
        $globals->asso()->getLogo()->send();
    }

    function handler_site(&$page)
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

    function handler_edit(&$page)
    {
        global $globals;
        $page->changeTpl('xnetgrp/edit.tpl');

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
            if (S::admin()) {
                $page->assign('super', true);

                if (Post::v('mail_domain') && (strstr(Post::v('mail_domain'), '.') === false)) {
                    $page->trigError('Le domaine doit être un FQDN (aucune modification effectuée)&nbsp;!!!');
                    return;
                }
                if (Post::t('nom') == '' || Post::t('diminutif') == '') {
                    $page->trigError('Ni le nom ni le diminutif du groupe ne peuvent être vide.');
                    return;
                }
                XDB::execute(
                    "UPDATE  groups
                        SET  nom={?}, diminutif={?}, cat={?}, dom={?},
                             descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, mail_domain={?}, ax={?}, pub={?},
                             sub_url={?}, inscriptible={?}, unsub_url={?},
                             flags={?}
                      WHERE  id={?}",
                      Post::v('nom'), Post::v('diminutif'),
                      Post::v('cat'), Post::i('dom'),
                      Post::v('descr'), $site,
                      Post::v('mail'), Post::v('resp'),
                      Post::v('forum'), Post::v('mail_domain'),
                      Post::has('ax'), Post::v('pub'),
                      Post::v('sub_url'), Post::v('inscriptible'),
                      Post::v('unsub_url'), $flags, $globals->asso('id'));
                if (Post::v('mail_domain')) {
                    XDB::execute('INSERT IGNORE INTO virtual_domains (domain) VALUES({?})',
                                           Post::v('mail_domain'));
                }
            } else {
                XDB::execute(
                    "UPDATE  groups
                        SET  descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, pub= {?}, sub_url={?},
                             unsub_url={?},flags={?}
                      WHERE  id={?}",
                      Post::v('descr'), $site,
                      Post::v('mail'), Post::v('resp'),
                      Post::v('forum'), Post::v('pub'),
                      Post::v('sub_url'), Post::v('unsub_url'),
                      $flags, $globals->asso('id'));
            }


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

            pl_redirect('../' . Post::v('diminutif', $globals->asso('diminutif')) . '/edit');
        }

        if (S::admin()) {
            $dom = XDB::iterator('SELECT  *
                                    FROM  group_dom
                                ORDER BY  nom');
            $page->assign('dom', $dom);
            $page->assign('super', true);
        }
    }

    function handler_mail(&$page)
    {
        global $globals;

        $page->changeTpl('xnetgrp/mail.tpl');
        $mmlist = new MMList(S::v('uid'), S::v('password'),
                           $globals->asso('mail_domain'));
        $page->assign('listes', $mmlist->get_lists());
        $page->assign('user', S::user());
        $page->addJsLink('ajax.js');

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

    function handler_forum(&$page, $group = null, $artid = null)
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

    function handler_annuaire(&$page, $action = null, $subaction = null)
    {
        global $globals;

        if ($action == 'trombi') {
            __autoload('userset');
            if ($action == 'trombi') {
                $view = new ProfileSet(new UFC_Group($globals->asso('id')));
            } else {
                $view = new UserSet(new UFC_Group($globals->asso('id')));
            }
            $view->addMod('trombi', 'Trombinoscope');
            $view->apply('annuaire', $page, $action, $subaction);
            $page->changeTpl('xnetgrp/annuaire.tpl');
            return;
        }

        $page->changeTpl('xnetgrp/annuaire.tpl');
        $sort = Env::s('order', 'directory_name');
        $ofs  = Env::i('offset');
        if ($ofs < 0) {
            $ofs = 0;
        }

        $sdesc = $sort{0} == '-';
        $sf    = $sdesc ? substr($sort, 1) : $sort;
        if ($sf == 'promo') {
            $se = new UFO_Promo(null, $sdesc);
        } else {
            $se = new UFO_Name($sf, null, null, $sdesc);
        }

        if (Env::b('admin')) {
            $uf = $globals->asso()->getAdminsFilter(null, $se);
        } else {
            $uf = $globals->asso()->getMembersFilter(null, $se);
        }
        $users = $uf->getUsers(new PlLimit(NB_PER_PAGE, $ofs * NB_PER_PAGE));
        $count = $uf->getTotalCount();

        $page->assign('pages', floor(($count + NB_PER_PAGE - 1) / NB_PER_PAGE));
        $page->assign('current', $ofs);
        $page->assign('order', $sort);
        $page->assign('users', $users);
        $page->assign('only_admin', Env::b('admin'));
    }

    function handler_trombi(&$page)
    {
        pl_redirect('annuaire/trombi');
    }

    function handler_geoloc(&$page)
    {
        pl_redirect('annuaire/geoloc');
    }

    function handler_vcard(&$page, $photos = null)
    {
        global $globals;
        $vcard = new VCard($photos == 'photos', 'Membre du groupe ' . $globals->asso('nom'));
        $vcard->addProfiles($globals->asso()->getMembersFilter()->getProfiles());
        $vcard->show();
    }

    function handler_csv(&$page, $filename = null)
    {
        global $globals;
        if (is_null($filename)) {
            $filename = $globals->asso('diminutif') . '.csv';
        }
        $users = $globals->asso()->getMembersFilter(null, new UFO_Name('directory_name'))->getUsers();
        pl_content_headers("text/x-csv");
        $page->changeTpl('xnetgrp/annuaire-csv.tpl', NO_SKIN);
        $page->assign('users', $users);
    }

    private function removeSubscriptionRequest($uid)
    {
        global $globals;
        XDB::execute("DELETE FROM group_member_sub_requests
                            WHERE asso_id = {?} AND uid = {?}",
                     $globals->asso('id'), $uid);
    }

    private function validSubscription(User &$user)
    {
        global $globals;
        $this->removeSubscriptionRequest($user->id());
        XDB::execute("INSERT IGNORE INTO  group_members (asso_id, uid)
                                  VALUES  ({?}, {?})",
                     $globals->asso('id'), $user->id());
        if (XDB::affectedRows() == 1) {
            $mailer = new PlMailer();
            $mailer->addTo($user->forlifeEmail());
            $mailer->setFrom('"' . S::user()->fullName() . '" <' . S::user()->forlifeEmail() . '>');
            $mailer->setSubject('[' . $globals->asso('nom') . '] Demande d\'inscription');
            $message = ($user->isFemale() ? 'Chère' : 'Cher') . " Camarade,\n"
                     . "\n"
                     . "  Suite à ta demande d'adhésion à " . $globals->asso('nom') . ",\n"
                     . "j'ai le plaisir de t'annoncer que ton inscription a été validée !\n"
                     . "\n"
                     . "Bien cordialement,\n"
                     . "-- \n"
                     . S::user()->fullName() . '.';
            $mailer->setTxtBody($message);
            $mailer->send();
        }
    }

    function handler_subscribe(&$page, $u = null)
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
            $uf = New UserFilter(New UFC_Group($globals->asso('id'), true));
            $admins = $uf->iterUsers();
            $admin = $admins->next();
            $to = $admin->bestalias;
            while ($admin = $admins->next()) {
                $to .= ', ' . $admin->bestalias;
            }

            $append = "\n"
                    . "-- \n"
                    . "Ce message a été envoyé suite à la demande d'inscription de\n"
                    . S::user()->fullName() . ' (X' . S::v('promo') . ")\n"
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

    function handler_subscribe_valid(&$page)
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

    function handler_change_rights(&$page)
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

    function handler_admin_annuaire(&$page)
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

    function handler_admin_member_new(&$page, $email = null)
    {
        global $globals;

        $page->changeTpl('xnetgrp/membres-add.tpl');
        $page->addJsLink('ajax.js');

        if (is_null($email)) {
            return;
        }

        S::assert_xsrf_token();

        // Finds or creates account
        if (!User::isForeignEmailAddress($email)) {
            // standard x account
            $user = User::get($email);
        } else if (!isvalid_email($email)) {
            // email might not be a regular email but an alias or a hruid
            $user = User::get($email);
            if (!$user) {
                // need a valid email address
                $page->trigError("«&nbsp;<strong>$email</strong>&nbsp;» n'est pas une adresse email valide.");
                return;
            }
        } else if (Env::v('x') && Env::i('userid')) {
            // user is an x but might not yet be registered
            $user = User::getWithUID(Env::i('userid'));
            if (!$user) {
                $page->trigError("Utilisateur invalide");
                return;
            }
            // add email for marketing if unknown
            if ($user->state == 'pending' && Env::v('market')) {
                $market = Marketing::get($user->uid, $email);
                if (!$market) {
                    $market = new Marketing($user->uid, $email, 'group', $globals->asso('nom'),
                                            Env::v('market_from'), S::v('uid'));
                    $market->add();
                }
            }
        } else {
            // user is not an x
            $hruid = strtolower(str_replace('@','.',$email).'.ext');
            // might already exists (in another group for example)
            $user = User::get($hruid);
            if (empty($user)) {
                // creates new account: build names from email address
                $display_name = ucwords(strtolower(substr($hruid, strpos('.', $hruid))));
                $full_name = ucwords(strtolower(str_replace('.', ' ',substr($email, strpos('@', $email)))));
                XDB::execute("INSERT INTO accounts (hruid, display_name, full_name, email, type)
                    VALUES({?}, {?}, {?}, {?}, 'xnet')",
                    $hruid, $display_name, $full_name, $email);
                $user = User::get($hruid);
            }
        }
        if ($user) {
            XDB::execute("REPLACE INTO  group_members (uid, asso_id)
                                VALUES  ({?}, {?})",
                         $user->id(), $globals->asso('id'));
            $this->removeSubscriptionRequest($user->id());
            pl_redirect("member/" . $user->login());
        }
    }

    function handler_admin_member_new_ajax(&$page)
    {
        pl_content_headers("text/html");
        $page->changeTpl('xnetgrp/membres-new-search.tpl', NO_SKIN);
        $users = array();
        if (Env::has('login')) {
            $user = User::getSilent(Env::t('login'));
            if ($user && $user->state != 'pending') {
                $users = array($user);
            }
        }
        if (empty($users)) {
            list($nom, $prenom) = str_replace(array('-', ' ', "'"), '%', array(Env::t('nom'), Env::t('prenom')));
            $cond = new PFC_And(new PFC_Not(new UFC_Registered()));
            if (!empty($nom)) {
                $cond->addChild(new UFC_Name(Profile::LASTNAME, $nom, UFC_Name::CONTAINS));
            }
            if (!empty($prenom)) {
                $cond->addChild(new UFC_Name(Profile::FIRSTNAME, $prenom, UFC_Name::CONTAINS));
            }
            if (Env::i('promo')) {
                $cond->addChild(new UFC_Promo('=', UserFilter::GRADE_ING, Env::i('promo')));
            }
            $uf = new UserFilter($cond);
            $users = $uf->getUsers(new PlLimit(30));
            if ($uf->getTotalCount() > 30) {
                $page->assign('too_many', true);
                $users = array();
            }
        }
        $page->assign('users', $users);
    }

    function unsubscribe(PlUser &$user)
    {
        global $globals;
        XDB::execute("DELETE FROM  group_members
                            WHERE  uid = {?} AND asso_id = {?}",
                     $user->id(), $globals->asso('id'));

        if ($globals->asso('notif_unsub')) {
            $mailer = new PlMailer('xnetgrp/unsubscription-notif.mail.tpl');
            $admins = $globals->asso()->iterAdmins();
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

        $mmlist = new MMList($user, $domain);
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

        XDB::execute("DELETE FROM  virtual_redirect
                            USING  virtual_redirect
                       INNER JOIN  virtual USING(vid)
                            WHERE  redirect={?} AND alias LIKE {?}",
                       $user->forlifeEmail(), '%@'.$domain);
        return !$warning;
    }

    function handler_unsubscribe(&$page)
    {
        $page->changeTpl('xnetgrp/membres-del.tpl');
        $user = S::user();
        $uid  = S::user()->id();
        if (empty($uid)) {
            return PL_NOT_FOUND;
        }
        $page->assign('self', true);
        $page->assign('user', $uid);

        if (!Post::has('confirm')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        if ($this->unsubscribe($user)) {
            $page->trigSuccess('Vous avez été désinscrit du groupe avec succès.');
        } else {
            $page->trigWarning('Vous avez été désinscrit du groupe, mais des erreurs se sont produites lors des désinscriptions des alias et des listes de diffusion.');
        }
        $page->assign('is_member', is_member(true));
    }

    function handler_admin_member_del(&$page, $user = null)
    {
        $page->changeTpl('xnetgrp/membres-del.tpl');
        $user = User::getSilent($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }
        $page->assign('user', $user);

        if (!Post::has('confirm')) {
            return;
        } else {
            S::assert_xsrf_token();
        }

        if ($this->unsubscribe($user)) {
            $page->trigSuccess("{$user->fullName()} a été désinscrit du groupe&nbsp;!");
        } else {
            $page->trigWarning("{$user->fullName()} a été désinscrit du groupe, mais des erreurs subsistent&nbsp;!");
        }
    }

    private function changeLogin(PlPage &$page, PlUser &$user, MMList &$mmlist, $login)
    {
        // Search the uid of the user...
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
            $sub = false;
        }

        if (!$xuser) {
            return false;
        }

        $user->mergeIn($xuser);

        return $xuser->login();
    }

    function handler_admin_member(&$page, $user)
    {
        global $globals;

        $page->changeTpl('xnetgrp/membres-edit.tpl');

        $user = User::getSilent($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }

        $mmlist = new MMList($user, $globals->asso('mail_domain'));

        if (Post::has('change')) {
            S::assert_xsrf_token();

            // Convert user status to X
            if (!Post::blank('login_X')) {
                $forlife = $this->changeLogin($page, $user, $mmlist, Post::t('login_X'));
                if ($forlife) {
                    pl_redirect('member/' . $forlife);
                }
            }

            // Update user info
            $email_changed = (!$user->profile() && strtolower($user->forlifeEmail()) != strtolower(Post::v('email')));
            $from_email = $user->forlifeEmail();
            if (!$user->profile()) {
                XDB::query('UPDATE  accounts
                               SET  full_name = {?}, display_name = {?}, sex = {?}, email = {?}, type = {?}
                             WHERE  uid = {?}',
                            Post::v('full_name'), Post::v('display_name'), (Post::v('sex') == 'male')?'male':'female', Post::v('email'), (Post::v('type') == 'xnet')?'xnet':'virtual',
                            $user->id());
                if (XDB::affectedRows()) {
                    $page->trigSuccess('Données de l\'utilisateur mise à jour.');
                }
            }

            // Update group params for user
            $perms = Post::v('group_perms');
            $comm  = Post::t('comm');
            if ($user->group_perms != $perms || $user->group_comm != $comm) {
                XDB::query('UPDATE  group_members
                               SET  perms = {?}, comm = {?}
                             WHERE  uid = {?} AND asso_id = {?}',
                            ($perms == 'admin') ? 'admin' : 'membre', $comm,
                            $user->id(), $globals->asso('id'));
                if ($perms != $user->group_perms) {
                    $page->trigSuccess('Permissions modifiées&nbsp;!');
                }
                if ($comm != $user->group_comm) {
                    $page->trigSuccess('Commentaire mis à jour.');
                }
            }

            // Gets user info again as they might have change
            $user = User::getSilent($user->id());

            // Update ML subscriptions
            foreach (Env::v('ml1', array()) as $ml => $state) {
                $ask = empty($_REQUEST['ml2'][$ml]) ? 0 : 2;
                if ($ask == $state) {
                    if ($state && $email_changed) {
                        $mmlist->replace_email($ml, $from_email, $user->forlifeEmail());
                        $page->trigSuccess("L'abonnement de {$user->fullName()} à $ml@ a été mis à jour.");
                    }
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
                    if ($email_changed) {
                        $mmlist->mass_unsubscribe($ml, Array($from_email));
                    } else {
                        $mmlist->mass_unsubscribe($ml, Array($user->forlifeEmail()));
                    }
                    $page->trigSuccess("{$user->fullName()} a été désabonné de $ml@.");
                }
            }

            // Change subscriptioin to aliases
            foreach (Env::v('ml3', array()) as $ml => $state) {
                $ask = !empty($_REQUEST['ml4'][$ml]);
                if($state == $ask) {
                    if ($state && $email_changed) {
                        XDB::query("UPDATE  virtual_redirect AS vr, virtual AS v
                                       SET  vr.redirect = {?}
                                     WHERE  vr.vid = v.vid AND v.alias = {?} AND vr.redirect = {?}",
                                     $user->forlifeEmail(), $ml, $from_email);
                        $page->trigSuccess("L'abonnement de {$user->fullName()} à $ml a été mis à jour.");
                    }
                } else if($ask) {
                    XDB::query("INSERT INTO  virtual_redirect (vid,redirect)
                                     SELECT  vid,{?} FROM virtual WHERE alias={?}",
                               $user->forlifeEmail(), $ml);
                    $page->trigSuccess("{$user->fullName()} a été abonné à $ml.");
                } else {
                    XDB::query("DELETE FROM  virtual_redirect
                                      USING  virtual_redirect
                                 INNER JOIN  virtual USING(vid)
                                      WHERE  redirect={?} AND alias={?}",
                               $from_email, $ml);
                    $page->trigSuccess("{$user->fullName()} a été désabonné de $ml.");
                }
            }
        }

        $page->assign('user', $user);
        $page->assign('listes', $mmlist->get_lists($user->forlifeEmail()));
        $page->assign('alias', $user->emailAliases($globals->asso('mail_domain'), 'user', true));
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {
        global $globals;
        $page->assign('asso', $globals->asso());

        $this->load('feed.inc.php');
        $feed = new XnetGrpEventFeed();
        return $feed->run($page, $user, $hash, false);
    }

    private function upload_image(PlPage &$page, PlUpload &$upload)
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
        } elseif (!$upload->resizeImage(200, 300, 100, 100, 32284)) {
            $page->trigError('Impossible de retraiter l\'image');
            return false;
        }
        return true;
    }

    function handler_photo_announce(&$page, $eid = null) {
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

    function handler_edit_announce(&$page, $aid = null)
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
                $post = null;/*
                if ($globals->asso('forum')) {
                    require_once 'banana/forum.inc.php';
                    $banana = new ForumsBanana(S::user());
                    $post = $banana->post($globals->asso('forum'), null,
                                          $art['titre'], MiniWiki::wikiToText($fulltext, false, 0, 80));
                }*/
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
                    require_once('validations.inc.php');
                    $article = new EvtReq("[{$globals->asso('nom')}] " . $art['titre'], $fulltext,
                                    $art['promo_min'], $art['promo_max'], $art['expiration'], "", S::user(),
                                    $upload);
                    $article->submit();
                    $page->trigWarning("L'affichage sur la page d'accueil de Polytechnique.org est en attente de validation.");
                } else if ($upload && $upload->exists()) {
                    $upload->rm();
                }
                if ($art['nl']) {
                    require_once('validations.inc.php');
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
                    XDB::execute("REPLACE INTO  group_announces_photo
                                           SET  eid = {?}, attachmime = {?}, x = {?}, y = {?}, attach = {?}",
                                 $aid, $imgtype, $imgx, $imgy, $upload->getContents());
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

    function handler_admin_announce(&$page)
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
