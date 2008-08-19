<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

function get_infos($email)
{
    global $globals;
    // look for uid instead of email if numeric
    $field = is_numeric($email) ? 'uid' : 'email';

    if ($field == 'email') {
        $email = strtolower($email);
        if (strpos($email, '@') === false) {
            $email .= '@m4x.org';
        }
        list($mbox,$dom) = explode('@', $email);
    }

    $res = XDB::query(
            "SELECT  uid, nom, prenom, email, email AS email2, perms='admin', origine, comm, sexe
               FROM  groupex.membres
              WHERE  $field = {?} AND asso_id = {?}", $email, $globals->asso('id'));

    if ($res->numRows()) {
        $user = $res->fetchOneAssoc();
        if ($user['origine'] == 'X') {
            $res = XDB::query("SELECT nom, prenom, promo, FIND_IN_SET('femme', flags) AS sexe
                                 FROM auth_user_md5
                                WHERE user_id = {?}", $user['uid']);
            $user = array_merge($user, $res->fetchOneAssoc());
        }
        return $user;
    } elseif ($dom == 'polytechnique.org' || $dom == 'm4x.org') {
        $res = XDB::query(
                "SELECT  user_id AS uid, u.promo,
                         IF(u.nom_usage<>'', u.nom_usage, u.nom) AS nom,
                         u.prenom, b.alias,
                         CONCAT(b.alias, '@m4x.org') AS email,
                         CONCAT(b.alias, '@polytechnique.org') AS email2,
                         m.perms = 'admin' AS perms, m.origine, m.comm,
                         FIND_IN_SET('femme', u.flags) AS sexe
                   FROM  auth_user_md5   AS u
             INNER JOIN  aliases         AS a ON ( u.user_id = a.id AND a.type != 'homonyme' )
             INNER JOIN  aliases         AS b ON ( u.user_id = b.id AND b.type = 'a_vie' )
              LEFT JOIN  groupex.membres AS m ON ( m.uid = u.user_id AND asso_id={?})
                  WHERE  a.alias = {?} AND u.user_id < 50000", $globals->asso('id'), $mbox);
        return $res->fetchOneAssoc();
    }

    return null;
}


class XnetGrpModule extends PLModule
{
    function handlers()
    {
        return array(
            '%grp'                => $this->make_hook('index',     AUTH_PUBLIC),
            '%grp/asso.php'       => $this->make_hook('index',     AUTH_PUBLIC),
            '%grp/logo'           => $this->make_hook('logo',      AUTH_PUBLIC),
            '%grp/site'           => $this->make_hook('site',      AUTH_PUBLIC),
            '%grp/edit'           => $this->make_hook('edit',      AUTH_MDP, 'groupadmin'),
            '%grp/mail'           => $this->make_hook('mail',      AUTH_MDP, 'groupadmin'),
            '%grp/forum'          => $this->make_hook('forum',     AUTH_MDP, 'groupmember'),
            '%grp/annuaire'       => $this->make_hook('annuaire',  AUTH_MDP, 'groupannu'),
            '%grp/annuaire/vcard' => $this->make_hook('vcard',     AUTH_MDP, 'groupmember:groupannu'),
            '%grp/annuaire/csv'   => $this->make_hook('csv',       AUTH_MDP, 'groupmember:groupannu'),
            '%grp/trombi'         => $this->make_hook('trombi',    AUTH_MDP, 'groupannu'),
            '%grp/geoloc'         => $this->make_hook('geoloc',    AUTH_MDP, 'groupannu'),
            '%grp/subscribe'      => $this->make_hook('subscribe', AUTH_MDP),
            '%grp/subscribe/valid' => $this->make_hook('subscribe_valid', AUTH_MDP, 'groupadmin'),
            '%grp/unsubscribe'    => $this->make_hook('unsubscribe', AUTH_MDP, 'groupmember'),

            '%grp/change_rights'  => $this->make_hook('change_rights', AUTH_MDP),

            '%grp/admin/annuaire'
                 => $this->make_hook('admin_annuaire', AUTH_MDP, 'groupadmin'),

            '%grp/member'
                 => $this->make_hook('admin_member', AUTH_MDP, 'groupadmin'),
            '%grp/member/new'
                 => $this->make_hook('admin_member_new', AUTH_MDP, 'groupadmin'),
            '%grp/member/new/ajax'
                 => $this->make_hook('admin_member_new_ajax', AUTH_MDP, 'user', NO_AUTH),
            '%grp/member/del'
                 => $this->make_hook('admin_member_del', AUTH_MDP, 'groupadmin'),

            '%grp/rss'             => $this->make_hook('rss', AUTH_PUBLIC, 'user', NO_HTTPS),
            '%grp/announce/new'    => $this->make_hook('edit_announce', AUTH_MDP,  'groupadmin'),
            '%grp/announce/edit'   => $this->make_hook('edit_announce', AUTH_MDP,  'groupadmin'),
            '%grp/announce/photo'  => $this->make_hook('photo_announce', AUTH_PUBLIC),
            '%grp/admin/announces' => $this->make_hook('admin_announce', AUTH_MDP, 'groupadmin'),
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
                XDB::query('DELETE r.*
                              FROM groupex.announces_read AS r
                        INNER JOIN groupex.announces AS a ON a.id = r.announce_id
                             WHERE peremption < CURRENT_DATE()');
                XDB::query('INSERT INTO groupex.announces_read
                                 VALUES ({?}, {?})',
                            Env::i('read'), S::i('uid'));
                pl_redirect("");
            }
            if (Env::has('unread')) {
                XDB::query('DELETE FROM groupex.announces_read
                                  WHERE announce_id={?} AND user_id={?}',
                            Env::i('unread'), S::i('uid'));
                pl_redirect("#art" . Env::i('unread'));
            }
            $arts = XDB::iterator("SELECT a.*, u.nom, u.prenom, u.promo, l.alias AS forlife,
                                          FIND_IN_SET('photo', a.flags) AS photo
                                     FROM groupex.announces AS a
                               INNER JOIN auth_user_md5 AS u USING(user_id)
                               INNER JOIN aliases AS l ON (u.user_id = l.id AND l.type = 'a_vie')
                                LEFT JOIN groupex.announces_read AS r ON (r.user_id = {?} AND r.announce_id = a.id)
                                    WHERE asso_id = {?} AND peremption >= CURRENT_DATE()
                                          AND (promo_min = 0 OR promo_min <= {?})
                                          AND (promo_max = 0 OR promo_max >= {?})
                                          AND r.announce_id IS NULL
                                 ORDER BY a.peremption",
                                   S::i('uid'), $globals->asso('id'), S::i('promo'), S::i('promo'));
            $index = XDB::iterator("SELECT a.id, a.titre, r.user_id IS NULL AS nonlu
                                      FROM groupex.announces AS a
                                 LEFT JOIN groupex.announces_read AS r ON (a.id = r.announce_id AND r.user_id = {?})
                                     WHERE asso_id = {?} AND peremption >= CURRENT_DATE()
                                           AND (promo_min = 0 OR promo_min <= {?})
                                           AND (promo_max = 0 OR promo_max >= {?})
                                  ORDER BY a.peremption",
                                   S::i('uid'), $globals->asso('id'), S::i('promo'), S::i('promo'));
            $page->assign('article_index', $index);
        } else {
            $arts = XDB::iterator("SELECT a.*, u.nom, u.prenom, u.promo, FIND_IN_SET('photo', a.flags) AS photo
                                     FROM groupex.announces AS a
                               INNER JOIN auth_user_md5 AS u USING(user_id)
                                    WHERE asso_id = {?} AND peremption >= CURRENT_DATE()
                                          AND FIND_IN_SET('public', u.flags)",
                                  $globals->asso('id'));
        }
        if (may_update()) {
            $subs_valid = XDB::query("SELECT  uid
                                        FROM  groupex.membres_sub_requests
                                       WHERE  asso_id = {?}",
                                     $globals->asso('id'));
            $page->assign('requests', $subs_valid->numRows());
        }

        if (!S::has('core_rss_hash')) {
            $page->setRssLink("Polytechnique.net :: {$globals->asso("nom")} :: News publiques",
                              $platal->ns . "rss/rss.xml");
        } else {
            $page->setRssLink("Polytechnique.net :: {$globals->asso("nom")} :: News",
                              $platal->ns . 'rss/'.S::v('forlife') .'/'.S::v('core_rss_hash').'/rss.xml');
        }

        $page->assign('articles', $arts);
    }

    function handler_logo(&$page)
    {
        global $globals;

        $res = XDB::query("SELECT logo, logo_mime
                             FROM groupex.asso WHERE id = {?}",
                          $globals->asso('id'));
        list($logo, $logo_mime) = $res->fetchOneRow();

        if (!empty($logo)) {
            header("Content-type: $mime");
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified:' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            echo $logo;
        } else {
            header('Content-type: image/jpeg');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified:' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            readfile(dirname(__FILE__).'/../htdocs/images/dflt_carre.jpg');
        }

        exit;
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
            if (Post::has('notif_unsub') && Post::i('notif_unsub') == 1) {
                $flags->addFlag('notif_unsub');
            }
            if (S::has_perms()) {
                if (Post::v('mail_domain') && (strstr(Post::v('mail_domain'), '.') === false)) {
                    $page->trigError("le domaine doit être un FQDN (aucune modif effectuée) !!!");
                    return;
                }
                XDB::execute(
                    "UPDATE  groupex.asso
                        SET  nom={?}, diminutif={?}, cat={?}, dom={?},
                             descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, mail_domain={?}, ax={?}, pub={?},
                             sub_url={?}, inscriptible={?}, unsub_url={?},
                             flags={?}
                      WHERE  id={?}",
                      Post::v('nom'), Post::v('diminutif'),
                      Post::v('cat'), Post::i('dom'),
                      Post::v('descr'), Post::v('site'),
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
                    "UPDATE  groupex.asso
                        SET  descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, ax={?}, pub= {?}, sub_url={?},
                             unsub_url={?},flags={?}
                      WHERE  id={?}",
                      Post::v('descr'), Post::v('site'),
                      Post::v('mail'), Post::v('resp'),
                      Post::v('forum'), Post::has('ax'),
                      Post::v('pub'),
                      Post::v('sub_url'), Post::v('unsub_url'),
                      $flags, $globals->asso('id'));
            }

            if ($_FILES['logo']['name']) {
                $logo = file_get_contents($_FILES['logo']['tmp_name']);
                $mime = $_FILES['logo']['type'];
                XDB::execute('UPDATE groupex.asso
                                 SET logo={?}, logo_mime={?}
                               WHERE id={?}', $logo, $mime,
                             $globals->asso('id'));
            }

            pl_redirect('../'.Post::v('diminutif', $globals->asso('diminutif')).'/edit');
        }

        if (S::has_perms()) {
            $dom = XDB::iterator('SELECT * FROM groupex.dom ORDER BY nom');
            $page->assign('dom', $dom);
            $page->assign('super', true);
        }
        if (!$globals->asso('wiki_desc') && $globals->asso('descr')) {
            $page->trigWarning("Attention, le format de la description a changé et utilise désormais la syntaxe wiki "
                      . "intégrée au site. Il te faudra probablement adapter le formatage du texte actuel pour "
                      . "qu'il s'affiche correctement avec cette nouvelle syntaxe.");
        }
    }

    function handler_mail(&$page)
    {
        global $globals;

        $page->changeTpl('xnetgrp/mail.tpl');
        $mmlist = new MMList(S::v('uid'), S::v('password'),
                           $globals->asso('mail_domain'));
        $page->assign('listes', $mmlist->get_lists());
        $page->addJsLink('ajax.js');

        if (Post::has('send')) {
            S::assert_xsrf_token();
            $from  = Post::v('from');
            $sujet = Post::v('sujet');
            $body  = Post::v('body');

            $mls = array_keys(Env::v('ml', array()));
            $mbr = array_keys(Env::v('membres', array()));

            require_once dirname(__FILE__) . '/xnetgrp/mail.inc.php';
            set_time_limit(120);
            $tos = get_all_redirects($mbr,  $mls, $mmlist);
            $upload = PlUpload::get($_FILES['uploaded'], S::v('forlife'), 'xnet.emails', true);
            send_xnet_mails($from, $sujet, $body, Env::v('wiki'), $tos, Post::v('replyto'), $upload, @$_FILES['uploaded']['name']);
            if ($upload) {
                $upload->rm();
            }
            $page->kill("Email envoyé !");
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

        if ($action == 'search') {
            http_redirect("https://www.polytechnique.org/search/adv?rechercher=Chercher&groupex={$globals->asso('id')}"
                        . "&cityid=" . Env::v('cityid') . "&mapid=" . Env::v('mapid'));
        } else if ($action == 'geoloc' || $action == 'trombi') {
            $view = new UserSet();
            $view->addMod('trombi', 'Trombinoscope');
            $view->addMod('geoloc', 'Planisphère', false, array('with_annu' => 'annuaire/search'));
            $view->apply('annuaire', $page, $action, $subaction);
            if ($action == 'geoloc' && $subaction) {
                return;
            }
        }
        $page->changeTpl('xnetgrp/annuaire.tpl');

        $sort = Env::v('order');
        switch (Env::v('order')) {
            case 'promo'    : $group = 'promo';    $tri = 'promo_o DESC, nom, prenom'; break;
            case 'promo_inv': $group = 'promo';    $tri = 'promo_o, nom, prenom'; break;
            case 'alpha_inv': $group = 'initiale'; $tri = 'nom DESC, prenom DESC, promo'; break;
            default         : $group = 'initiale'; $tri = 'nom, prenom, promo'; $sort = 'alpha';
        }
        $page->assign('sort', $sort);

        if ($group == 'initiale') {
            $res = XDB::iterRow(
                        'SELECT  UPPER(SUBSTRING(
                                    IF(m.origine="X", IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom),
                                     1, 1)) as letter, COUNT(*)
                           FROM  groupex.membres AS m
                      LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid)
                          WHERE  asso_id = {?} and (u.perms != \'pending\' OR m.email IS NOT NULL)
                       GROUP BY  letter
                       ORDER BY  letter', $globals->asso('id'));
        } else {
            $res = XDB::iterRow(
                        'SELECT  IF(m.origine="X",u.promo,
                                    IF(m.origine="ext", "extérieur", "personne morale")) AS promo,
                                 COUNT(*), IF(m.origine="X",u.promo,"") AS promo_o
                           FROM  groupex.membres AS m
                      LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                          WHERE  asso_id = {?}
                       GROUP BY  promo
                       ORDER BY  promo_o DESC', $globals->asso('id'));
        }
        $alphabet = array();
        $nb_tot = 0;
        while (list($char, $nb) = $res->next()) {
            $alphabet[] = $char;
            $nb_tot += $nb;
            if (Env::has($group) && $char == strtoupper(Env::v($group))) {
                $tot = $nb;
            }
        }
        $page->assign('group', $group);
        $page->assign('request_group', Env::v($group));
        $page->assign('only_admin', Env::has('admin'));
        $page->assign('alphabet', $alphabet);
        $page->assign('nb_tot',   $nb_tot);

        $ofs   = Env::i('offset');
        $tot   = Env::v($group) ? $tot : $nb_tot;
        $nbp   = intval(($tot-1)/NB_PER_PAGE);
        $links = array();
        if ($ofs) {
            $links['précédent'] = $ofs-1;
        }
        for ($i = 0; $i <= $nbp; $i++) {
            $links[(string)($i+1)] = $i;
        }
        if ($ofs < $nbp) {
            $links['suivant'] = $ofs+1;
        }
        if (count($links)>1) {
            $page->assign('links', $links);
        }

        $ini = '';
        if (Env::has('initiale')) {
            $ini = 'AND IF(m.origine="X",
                           IF(u.nom_usage<>"", u.nom_usage, u.nom),
                           m.nom) LIKE "'.addslashes(Env::v('initiale')).'%"';
        } elseif (Env::has('promo')) {
            $ini = 'AND IF(m.origine="X", u.promo, IF(m.origine="ext", "extérieur", "personne morale")) = "'
                 .addslashes(Env::v('promo')).'"';
        } elseif (Env::has('admin')) {
            $ini = 'AND m.perms = "admin"';
        }

        $ann = XDB::iterator(
                  "SELECT  IF(m.origine='X',IF(u.nom_usage<>'', u.nom_usage, u.nom) ,m.nom) AS nom,
                           IF(m.origine='X',u.prenom,m.prenom) AS prenom,
                           IF(m.origine='X', u.promo, IF(m.origine='ext', 'extérieur', 'personne morale')) AS promo,
                           IF(m.origine='X',u.promo,'') AS promo_o,
                           IF(m.origine='X' AND u.perms != 'pending',a.alias,m.email) AS email,
                           IF(m.origine='X',FIND_IN_SET('femme', u.flags), m.sexe) AS femme,
                           m.perms='admin' AS admin,
                           m.origine='X' AS x,
                           u.perms!='pending' AS inscrit,
                           m.comm as comm,
                           m.uid, IF(e.email IS NULL AND FIND_IN_SET('googleapps', u.mail_storage) = 0, NULL, 1) AS actif
                     FROM  groupex.membres AS m
                LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type='a_vie' )
                LEFT JOIN  emails          AS e ON ( e.flags = 'active' AND e.uid = m.uid)
                    WHERE  m.asso_id = {?} $ini
                           AND (m.origine != 'X' OR u.perms != 'pending' OR m.email IS NOT NULL)
                 GROUP BY  m.uid
                 ORDER BY  $tri
                    LIMIT  {?},{?}", $globals->asso('id'), $ofs*NB_PER_PAGE, NB_PER_PAGE);
        $page->assign('ann', $ann);
        $page->jsonAssign('ann', $ann);
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
        $res = XDB::query('SELECT  uid
                             FROM  groupex.membres
                            WHERE  asso_id = {?}', $globals->asso('id'));
        $vcard = new VCard($res->fetchColumn(), $photos == 'photos', 'Membre du groupe ' . $globals->asso('nom'));
        $vcard->do_page($page);
    }

    function handler_csv(&$page, $filename = null)
    {
        global $globals;
        if (is_null($filename)) {
            $filename = $globals->asso('diminutif') . '.csv';
        }
        $ann = XDB::iterator(
                  "SELECT  IF(m.origine='X',IF(u.nom_usage<>'', u.nom_usage, u.nom) ,m.nom) AS nom,
                           IF(m.origine='X',u.prenom,m.prenom) AS prenom,
                           IF(m.origine='X', u.promo, IF(m.origine='ext', 'extérieur', 'personne morale')) AS promo,
                           IF(m.origine='X' AND u.perms != 'pending',CONCAT(a.alias, '@', {?}), m.email) AS email,
                           IF(m.origine='X',FIND_IN_SET('femme', u.flags), m.sexe) AS femme,
                           m.comm as comm
                     FROM  groupex.membres AS m
                LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type = 'a_vie' )
                    WHERE  m.asso_id = {?}
                           AND (m.origine != 'X' OR u.perms != 'pending' OR m.email IS NOT NULL)
                 GROUP BY  m.uid
                 ORDER BY  nom, prenom",
                 $globals->mail->domain, $globals->asso('id'));
        header('Content-Type: text/x-csv; charset=utf-8;');
        header('Pragma: ');
        header('Cache-Control: ');
        $page->changeTpl('xnetgrp/annuaire-csv.tpl', NO_SKIN);
        $page->assign('ann', $ann);
    }

    private function removeSubscriptionRequest($uid)
    {
        global $globals;
        XDB::execute("DELETE FROM groupex.membres_sub_requests
                            WHERE asso_id = {?} AND uid = {?}",
                     $globals->asso('id'), $uid);
    }

    private function validSubscription($nom, $prenom, $sexe, $uid, $forlife)
    {
        global $globals;
        $this->removeSubscriptionRequest($uid);
        XDB::execute("INSERT INTO  groupex.membres (asso_id, uid)
                           VALUES  ({?}, {?})",
                     $globals->asso('id'), $uid);
        $mailer = new PlMailer();
        $mailer->addTo("$forlife@polytechnique.org");
        $mailer->setFrom('"' . S::v('prenom') . ' ' . S::v('nom')
                         . '" <' . S::v('forlife') . '@polytechnique.org>');
        $mailer->setSubject('[' . $globals->asso('nom') . '] Demande d\'inscription');
        $message = ($sexe ? 'Chère' : 'Cher') . " Camarade,\n"
                 . "\n"
                 . "  Suite à ta demande d'adhésion à " . $globals->asso('nom') . ",\n"
                 . "j'ai le plaisir de t'annoncer que ton inscription a été validée !\n"
                 . "\n"
                 . "Bien cordialement,\n"
                 . "-- \n"
                 . S::s('prenom') . ' ' . S::s('nom') . '.';
        $mailer->setTxtBody($message);
        $mailer->send();
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
            $page->assign('u', $u);
            $res = XDB::query("SELECT  u.nom, u.prenom, u.promo, u.user_id, FIND_IN_SET('femme', u.flags), s.reason
                                 FROM  auth_user_md5 AS u
                           INNER JOIN  aliases AS al ON (al.id = u.user_id AND al.type != 'liste')
                            LEFT JOIN  groupex.membres_sub_requests AS s ON (u.user_id = s.uid AND s.asso_id = {?})
                                WHERE  al.alias = {?}", $globals->asso('id'), $u);

            if (list($nom, $prenom, $promo, $uid, $sexe, $reason) = $res->fetchOneRow()) {
                $res = XDB::query("SELECT  COUNT(*)
                                     FROM  groupex.membres AS m
                               INNER JOIN  aliases  AS a ON (m.uid = a.id AND a.type != 'homonyme')
                                    WHERE  a.alias = {?} AND m.asso_id = {?}",
                                  $u, $globals->asso('id'));
                $n   = $res->fetchOneCell();
                if ($n) {
                    $this->removeSubscriptionRequest($uid);
                    $page->kill("$prenom $nom est déjà membre du groupe !");
                    return;
                } elseif (Env::has('accept')) {
                    S::assert_xsrf_token();

                    $this->validSubscription($nom, $prenom, $sexe, $uid, $u);
                    pl_redirect("member/$u");
                } elseif (Env::has('refuse')) {
                    S::assert_xsrf_token();

                    $this->removeSubscriptionRequest($uid);
                    $mailer = new PlMailer();
                    $mailer->addTo("$u@polytechnique.org");
                    $mailer->setFrom('"'.S::v('prenom').' '.S::v('nom')
                                     .'" <'.S::v('forlife').'@polytechnique.org>');
                    $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription annulée');
                    $mailer->setTxtBody(Env::v('motif'));
                    $mailer->send();
                    $page->kill("La demande de $prenom $nom a bien été refusée.");
                } else {
                    $page->assign('show_form', true);
                    $page->assign('prenom', $prenom);
                    $page->assign('nom', $nom);
                    $page->assign('promo', $promo);
                    $page->assign('uid', $uid);
                    $page->assign('reason', $reason);
                }
                return;
            }
            return PL_NOT_FOUND;
        }

        if (is_member()) {
            $page->kill("Tu es déjà membre !");
            return;
        }

        $res = XDB::query("SELECT  uid
                             FROM  groupex.membres_sub_requests
                            WHERE  uid = {?} AND asso_id = {?}",
                         S::i('uid'), $globals->asso('id'));
        if ($res->numRows() != 0) {
            $page->kill("Tu as déjà demandé ton inscription à ce groupe. Cette demande est actuellement en attente de validation.");
            return;
        }

        if (Post::has('inscrire')) {
            S::assert_xsrf_token();

            XDB::execute("INSERT INTO  groupex.membres_sub_requests (asso_id, uid, ts, reason)
                               VALUES  ({?}, {?}, NOW(), {?})",
                         $globals->asso('id'), S::i('uid'), Post::v('message'));
            $res = XDB::query('SELECT  IF(m.email IS NULL,
                                          CONCAT(al.alias,"@polytechnique.org"),
                                           m.email)
                                 FROM  groupex.membres AS m
                           INNER JOIN  aliases         AS al ON (al.type = "a_vie"
                                                                 AND al.id = m.uid)
                                WHERE  perms="admin" AND m.asso_id = {?}',
                             $globals->asso('id'));
            $emails = $res->fetchColumn();
            $to     = implode(',', $emails);

            $append = "\n"
                    . "-- \n"
                    . "Ce message a été envoyé suite à la demande d'inscription de\n"
                    . S::v('prenom').' '.S::v('nom').' (X'.S::v('promo').")\n"
                    . "Via le site www.polytechnique.net. Tu peux choisir de valider ou\n"
                    . "de refuser sa demande d'inscription depuis la page :\n"
                    .
                    "http://www.polytechnique.net/".$globals->asso("diminutif")."/subscribe/"
                        .S::v('forlife')."\n"
                    . "\n"
                    . "En cas de problème, contacter l'équipe de Polytechnique.org\n"
                    . "à l'adresse : support@polytechnique.org\n";

            if (!$to) {
                $to = $globals->asso("mail").", support@polytechnique.org";
                $append = "\n-- \nLe groupe ".$globals->asso("nom")
                        ." n'a pas d'administrateur, l'équipe de"
                        ." Polytechnique.org a été prévenue et va rapidement"
                        ." résoudre ce problème.\n";
            }

            $mailer = new PlMailer();
            $mailer->addTo($to);
            $mailer->setFrom('"'.S::v('prenom').' '.S::v('nom')
                             .'" <'.S::v('forlife').'@polytechnique.org>');
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
                foreach ($subs as $forlife => $val) {
                    if ($val == '1') {
                        $res = XDB::query("SELECT  IF(u.nom_usage != '', u.nom_usage, u.nom) AS u,
                                                   u.prenom, FIND_IN_SET('femme', u.flags) AS sexe,
                                                   u.user_id
                                             FROM  auth_user_md5 AS u
                                       INNER JOIN  aliases AS a ON (a.id = u.user_id)
                                            WHERE  a.alias = {?}", $forlife);
                        if ($res->numRows() == 1) {
                            list($nom, $prenom, $sexe, $uid) = $res->fetchOneRow();
                            $this->validSubscription($nom, $prenom, $sexe, $uid, $forlife);
                        }
                    }
                }
            }
        }

        $it = XDB::iterator("SELECT  IF(u.nom_usage != '', u.nom_usage, u.nom) AS nom,
                                     u.prenom, u.promo, a.alias AS forlife, s.ts AS date
                               FROM  groupex.membres_sub_requests AS s
                         INNER JOIN  auth_user_md5 AS u ON (s.uid = u.user_id)
                         INNER JOIN  aliases AS a ON (a.id = s.uid AND a.type = 'a_vie')
                              WHERE  asso_id = {?}
                           ORDER BY  nom, prenom",
                           $globals->asso('id'));

        $page->changeTpl('xnetgrp/subscribe-valid.tpl');
        $page->assign('valid', $it);
    }

    function handler_change_rights(&$page)
    {
        if (Env::has('right') && (may_update() || S::has('suid'))) {
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
//        var_dump($_SESSION);
        http_redirect($_SERVER['HTTP_REFERER']);
    }

    function handler_admin_annuaire(&$page)
    {
        global $globals;

        require_once dirname(__FILE__) . '/xnetgrp/mail.inc.php';
        $page->changeTpl('xnetgrp/annuaire-admin.tpl');
        $mmlist = new MMList(S::v('uid'), S::v('password'),
                             $globals->asso('mail_domain'));
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
            $res = XDB::query(
                       'SELECT  COUNT(*)
                          FROM  groupex.membres AS m
                     LEFT JOIN  auth_user_md5   AS u ON (m.uid=u.user_id AND m.uid<50000)
                     LEFT JOIN  aliases         AS a ON (a.id=u.user_id and a.type="a_vie")
                         WHERE  asso_id = {?} AND
                                (m.email = {?} OR CONCAT(a.alias, "@polytechnique.org") = {?})',
                        $globals->asso('id'), $mail, $mail);
            if ($res->fetchOneCell() == 0) {
                if (strstr($mail, '@polytechnique.org') === false) {
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
        } else {
            S::assert_xsrf_token();
        }

        if (strpos($email, '@') === false) {
            $x = true;
        } else {
            list(,$fqdn) = explode('@', $email, 2);
            $fqdn = strtolower($fqdn);
            $x = ($fqdn == 'polytechnique.org' || $fqdn == 'melix.org' ||
                  $fqdn == 'm4x.org' || $fqdn == 'melix.net');
        }
        if ($x) {
            require_once 'user.func.inc.php';
            if ($forlife = get_user_forlife($email)) {
                XDB::execute(
                    'INSERT INTO  groupex.membres (uid,asso_id,origine)
                          SELECT  user_id,{?},"X"
                            FROM  auth_user_md5 AS u
                      INNER JOIN  aliases       AS a ON (u.user_id = a.id)
                           WHERE  a.alias={?}', $globals->asso('id'), $forlife);
                pl_redirect("member/$forlife");
            } else {
                $page->trigError($email." n'est pas un alias polytechnique.org valide.");
            }
        } else {
            if (isvalid_email($email)) {
                if (Env::v('x') && Env::has('userid') && Env::i('userid')) {
                    $uid = Env::i('userid');
                    $res = XDB::query("SELECT *
                                         FROM auth_user_md5
                                        WHERE user_id = {?} AND perms = 'pending'", $uid);
                    if ($res->numRows() == 1) {
                        if (Env::v('market')) {
                            $market = Marketing::get($uid, $email);
                            if (!$market) {
                                $market = new Marketing($uid, $email, 'group', $globals->asso('nom'),
                                                        Env::v('market_from'), S::v('uid'));
                                $market->add();
                            }
                        }
                        XDB::execute('INSERT INTO groupex.membres (uid, asso_id, origine, email)
                                           VALUES ({?}, {?}, "X", {?})',
                                     $uid, $globals->asso('id'), $email);
                        $this->removeSubscriptionRequest($uid);
                        pl_redirect("member/$email");
                    }
                    $page->trigError("Utilisateur invalide");
                } else {
                    $res = XDB::query('SELECT MAX(uid)+1 FROM groupex.membres');
                    $uid = max(intval($res->fetchOneCell()), 50001);
                    XDB::execute('INSERT INTO  groupex.membres (uid,asso_id,origine,email)
                                            VALUES({?},{?},"ext",{?})', $uid,
                                            $globals->asso('id'), $email);
                    pl_redirect("member/$email");
                }
            } else {
                $page->trigError("« <strong>$email</strong> » n'est pas une adresse email valide.");
            }
        }
    }

    function handler_admin_member_new_ajax(&$page)
    {
        header('Content-Type: text/html; charset="UTF-8"');
        $page->changeTpl('xnetgrp/membres-new-search.tpl', NO_SKIN);
        $res = null;
        if (Env::has('login')) {
            require_once 'user.func.inc.php';
            $res = get_not_registered_user(Env::v('login'), true);
        }
        if (is_null($res)) {
            list($nom, $prenom) = str_replace(array('-', ' ', "'"), '%', array(Env::v('nom'), Env::v('prenom')));
            $where = "perms = 'pending'";
            if (!empty($nom)) {
                $where .= " AND nom LIKE '%$nom%'";
            }
            if (!empty($prenom)) {
                $where .= " AND prenom LIKE '%$prenom%'";
            }
            if (preg_match('/^[0-9]{4}$/', Env::v('promo'))) {
                $where .= " AND promo = " . Env::i('promo');
            } elseif (preg_match('/^[0-9]{2}$/', Env::v('promo'))) {
                $where .= " AND MOD(promo, 100) = " . Env::i('promo');
            } elseif (Env::has('promo')) {
                return;
            }
            $res = XDB::iterator("SELECT user_id, nom, prenom, promo
                                    FROM auth_user_md5
                                   WHERE $where");
        }
        if ($res && $res->total() < 30) {
            $page->assign("choix", $res);
        }
    }

    function unsubscribe(&$user)
    {
        global $globals;
        XDB::execute(
                "DELETE FROM  groupex.membres WHERE uid={?} AND asso_id={?}",
                $user['uid'], $globals->asso('id'));

        if ($globals->asso('notif_unsub')) {
            $mailer = new PlMailer('xnetgrp/unsubscription-notif.mail.tpl');
            $res = XDB::iterRow("SELECT  a.alias, u.prenom, IF(u.nom_usage != '', u.nom_usage, u.nom) AS nom
                                   FROM  groupex.membres AS m
                             INNER JOIN  aliases AS a ON (m.uid = a.id AND FIND_IN_SET('bestalias', a.flags))
                             INNER JOIn  auth_user_md5 AS u ON (u.user_id = a.id)
                                  WHERE  m.asso_id = {?} AND m.perms = 'admin'",
                                  $globals->asso('id'));
            while (list($alias, $prenom, $nom) = $res->next()) {
                $mailer->addTo("\"$prenom $nom\" <$alias@{$globals->mail->domain}>");
            }
            $mailer->assign('group', $globals->asso('nom'));
            $mailer->assign('prenom', $user['prenom']);
            $mailer->assign('nom', $user['nom']);
            $mailer->assign('mail', $user['email2']);
            $mailer->assign('selfdone', $user['uid'] == S::i('uid'));
            $mailer->send();
        }

        $user_same_email = get_infos($user['email']);
        $domain = $globals->asso('mail_domain');

        if (!$domain || (!empty($user_same_email) && $user_same_email['uid'] != $user['uid'])) {
            return true;
        }

        $mmlist = new MMList(S::v('uid'), S::v('password'), $domain);
        $listes = $mmlist->get_lists($user['email2']);

        $may_update = may_update();
        $warning    = false;
        foreach ($listes as $liste) {
            if ($liste['sub'] == 2) {
                if ($may_update) {
                    $mmlist->mass_unsubscribe($liste['list'], Array($user['email2']));
                } else {
                    $mmlist->unsubscribe($liste['list']);
                }
            } elseif ($liste['sub']) {
                Platal::page()->trigWarning("{$user['prenom']} {$user['nom']} a une"
                                           ." demande d'inscription en cours sur la"
                                           ." liste {$liste['list']}@ !");
                $warning = true;
            }
        }

        XDB::execute(
                "DELETE FROM  virtual_redirect
                       USING  virtual_redirect
                  INNER JOIN  virtual USING(vid)
                       WHERE  redirect={?} AND alias LIKE {?}", $user['email'], '%@'.$domain);
        return !$warning;
    }

    function handler_unsubscribe(&$page)
    {
        $page->changeTpl('xnetgrp/membres-del.tpl');
        $user = get_infos(S::v('forlife'));
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
        $user = get_infos($user);
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
            $page->trigSuccess("{$user['prenom']} {$user['nom']} a été désabonné du groupe !");
        } else {
            $page->trigWarning("{$user['prenom']} {$user['nom']} a été désabonné du groupe, mais des erreurs subsistent !");
        }
    }

    private function changeLogin(PlPage &$page, array &$user, MMList &$mmlist, $login)
    {
        require_once 'user.func.inc.php';
        // Search the uid of the user...
        $res = XDB::query("SELECT  f.id, f.alias
                             FROM  aliases AS a
                       INNER JOIN  aliases AS f ON (f.id = a.id AND f.type = 'a_vie')
                            WHERE  a.alias = {?}",
                          $login);
        if ($res->numRows() == 0) {
            $x = get_not_registered_user($login);
            if (!$x) {
                $page->trigError("Le login $login ne correspond à aucun X.");
                return false;
            } else if (count($x) > 1) {
                $page->trigError("Le login $login correspond a plusieurs camarades.");
                return false;
            }
            $uid = $x[0]['user_id'];
            $sub = false;
        } else {
            list($uid, $login) = $res->fetchOneRow();
            $sub = true;
        }

        // Check if the user is already in the group
        global $globals;
        $res = XDB::query("SELECT  uid, email
                             FROM  groupex.membres
                            WHERE  uid = {?} AND asso_id = {?}",
                          $uid, $globals->asso('id'));
        if ($res->numRows()) {
            list($uid, $email) = $res->fetchOneRow();
            XDB::execute("DELETE FROM groupex.membres
                                WHERE uid = {?}",
                         $user['uid']);
        } else {
            $email = $user['email'];
            XDB::execute("UPDATE  groupex.membres
                             SET  uid = {?}, origine = 'X'
                           WHERE  uid = {?} AND asso_id = {?}",
                         $uid, $user['uid'], $globals->asso('id'));
        }
        if ($sub) {
            $email = $login . '@' . $globals->mail->domain;
        }

        // Update subscription to aliases
        if ($email != $user['email']) {
            XDB::execute("UPDATE IGNORE  virtual_redirect AS vr
                             INNER JOIN  virtual AS v ON(vr.vid = v.vid AND SUBSTRING_INDEX(alias, '@', -1) = {?})
                                    SET  vr.redirect = {?}
                                  WHERE  vr.redirect = {?}",
                         $globals->asso('mail_domain'), $email, $user['email']);
            XDB::execute("DELETE  vr.*
                            FROM  virtual_redirect AS vr
                      INNER JOIN  virtual AS v ON(vr.vid = v.vid AND SUBSTRING_INDEX(alias, '@', -1) = {?})
                           WHERE  vr.redirect = {?}",
                         $globals->asso('mail_domain'), $user['email']);
            foreach (Env::v('ml1', array()) as $ml => $state) {
                $mmlist->replace_email($ml, $user['email'], $email);
            }
        }
        if ($sub) {
            return $login;
        }
        return $user['email'];
    }

    function handler_admin_member(&$page, $user)
    {
        global $globals;

        $page->changeTpl('xnetgrp/membres-edit.tpl');

        $user = get_infos($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }

        $mmlist = new MMList(S::v('uid'), S::v('password'),
                             $globals->asso('mail_domain'));

        if (Post::has('change')) {
            S::assert_xsrf_token();

            // Convert user status to X
            if ($user['origine'] == 'ext' && trim(Post::v('login_X'))) {
                $forlife = $this->changeLogin($page, $user, $mmlist, trim(Post::v('login_X')));
                if ($forlife) {
                    pl_redirect('member/' . $forlife);
                }
            }

            // Update user info
            $email_changed = ($user['origine'] != 'X' && strtolower($user['email']) != strtolower(Post::v('email')));
            $from_email = $user['email'];
            if ($user['origine'] != 'X') {
                $user['nom']     = Post::v('nom');
                $user['prenom']  = (Post::v('origine') == 'ext') ? Post::v('prenom') : '';
                $user['sexe']    = (Post::v('origine') == 'ext') ? Post::v('sexe') : 0;
                $user['origine'] = Post::v('origine');
                XDB::query('UPDATE groupex.membres
                               SET prenom={?}, nom={?}, email={?}, sexe={?}, origine={?}
                             WHERE uid={?} AND asso_id={?}',
                           $user['prenom'], $user['nom'], Post::v('email'),
                           $user['sexe'], $user['origine'],
                           $user['uid'], $globals->asso('id'));
                $user['email']   = Post::v('email');
                $user['email2']  = Post::v('email');
                $page->trigSuccess('Données de l\'utilisateur mise à jour.');
            }

            $perms = Post::i('is_admin');
            $comm  = trim(Post::s('comm'));
            if ($user['perms'] != $perms || $user['comm'] != $comm) {
                XDB::query('UPDATE groupex.membres
                               SET perms={?}, comm={?}
                             WHERE uid={?} AND asso_id={?}',
                            $perms ? 'admin' : 'membre', $comm,
                            $user['uid'], $globals->asso('id'));
                if ($perms != $user['perms']) {
                    $page->trigSuccess('Permissions modifiées !');
                }
                if ($comm != $user['comm']) {
                    $page->trigSuccess('Commentaire mis à jour.');
                }
                $user['perms'] = $perms;
                $user['comm'] = $comm;
            }

            // Update ML subscriptions
            foreach (Env::v('ml1', array()) as $ml => $state) {
                $ask = empty($_REQUEST['ml2'][$ml]) ? 0 : 2;
                if ($ask == $state) {
                    if ($state && $email_changed) {
                        $mmlist->replace_email($ml, $from_email, $user['email2']);
                        $page->trigSuccess("L'abonnement de {$user['prenom']} {$user['nom']} à $ml@ a été mis à jour.");
                    }
                    continue;
                }
                if ($state == '1') {
                    $page->trigWarning("{$user['prenom']} {$user['nom']} a "
                               ."actuellement une demande d'inscription en "
                               ."cours sur <strong>$ml@</strong> !!!");
                } elseif ($ask) {
                    $mmlist->mass_subscribe($ml, Array($user['email2']));
                    $page->trigSuccess("{$user['prenom']} {$user['nom']} a été abonné à $ml@.");
                } else {
                    if ($email_changed) {
                        $mmlist->mass_unsubscribe($ml, Array($from_email));
                    } else {
                        $mmlist->mass_unsubscribe($ml, Array($user['email2']));
                    }
                    $page->trigSuccess("{$user['prenom']} {$user['nom']} a été désabonné de $ml@.");
                }
            }

            // Change subscriptioin to aliases
            foreach (Env::v('ml3', array()) as $ml => $state) {
                $ask = !empty($_REQUEST['ml4'][$ml]);
                if($state == $ask) continue;
                if($ask) {
                    XDB::query("INSERT INTO  virtual_redirect (vid,redirect)
                                     SELECT  vid,{?} FROM virtual WHERE alias={?}",
                               $user['email'], $ml);
                    $page->trigSuccess("{$user['prenom']} {$user['nom']} a été abonné à $ml.");
                } else {
                    XDB::query("DELETE FROM  virtual_redirect
                                      USING  virtual_redirect
                                 INNER JOIN  virtual USING(vid)
                                      WHERE  redirect={?} AND alias={?}",
                               $user['email'], $ml);
                    $page->trigSuccess("{$user['prenom']} {$user['nom']} a été désabonné de $ml.");
                }
            }
        }

        $page->assign('user', $user);
        $listes = $mmlist->get_lists($user['email2']);
        $page->assign('listes', $listes);

        $res = XDB::query(
                'SELECT  alias, redirect IS NOT NULL as sub
                   FROM  virtual          AS v
              LEFT JOIN  virtual_redirect AS vr ON(v.vid=vr.vid AND (redirect = {?} OR redirect = {?}))
                  WHERE  alias LIKE {?} AND type="user"',
                $user['email'], $user['email2'], '%@'.$globals->asso('mail_domain'));
        $page->assign('alias', $res->fetchAllAssoc());
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {
        global $globals;
        $page->assign('asso', $globals->asso());

        require_once dirname(__FILE__) . '/xnetgrp/feed.inc.php';
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
            $res = XDB::query("SELECT * FROM groupex.announces_photo WHERE eid = {?}", $eid);
            if ($res->numRows()) {
                $photo = $res->fetchOneAssoc();
                header('Content-Type: image/' . $photo['attachmime']);
                echo $photo['attach'];
                exit;
            }
        } else {
            $upload = new PlUpload(S::v('forlife'), 'xnetannounce');
            if ($upload->exists() && $upload->isType('image')) {
                header('Content-Type: ' . $upload->contentType());
                echo $upload->getContents();
                exit;
            }
        }
        global $globals;
        header('Content-Type: image/png');
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
            $art['forlife']    = S::v('forlife');
            $art['peremption'] = Post::v('peremption');
            $art['public']     = Post::has('public');
            $art['xorg']       = Post::has('xorg');
            $art['nl']         = Post::has('nl');
            $art['event']      = Post::v('event');
            $upload     = new PlUpload(S::v('forlife'), 'xnetannounce');
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
                XDB::query("DELETE FROM groupex.announces_photo WHERE eid = {?}", $aid);
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
                    $banana = new ForumsBanana(S::v('forlife'));
                    $post = $banana->post($globals->asso('forum'), null,
                                          $art['titre'], MiniWiki::wikiToText($fulltext, false, 0, 80));
                }*/
                XDB::query("INSERT INTO groupex.announces
                                 (user_id, asso_id, create_date, titre, texte, contacts,
                                   peremption, promo_min, promo_max, flags, post_id)
                            VALUES ({?}, {?}, NOW(), {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?})",
                           S::i('uid'), $globals->asso('id'), $art['titre'], $art['texte'], $art['contact_html'],
                           $art['peremption'], $promo_min, $promo_max, $flags, $post);
                $aid = XDB::insertId();
                if ($art['photo']) {
                    list($imgx, $imgy, $imgtype) = $upload->imageInfo();
                    XDB::execute("INSERT INTO groupex.announces_photo
                                          SET eid = {?}, attachmime = {?}, x = {?}, y = {?}, attach = {?}",
                                 $aid, $imgtype, $imgx, $imgy, $upload->getContents());
                }
                if ($art['xorg']) {
                    require_once('validations.inc.php');
                    $article = new EvtReq("[{$globals->asso('nom')}] " . $art['titre'], $fulltext,
                                    $art['promo_min'], $art['promo_max'], $art['peremption'], "", S::v('uid'),
                                    $upload);
                    $article->submit();
                    $page->trigWarning("L'affichage sur la page d'accueil de Polytechnique.org est en attente de validation.");
                } else if ($upload && $upload->exists()) {
                    $upload->rm();
                }
                if ($art['nl']) {
                    require_once('validations.inc.php');
                    $article = new NLReq(S::v('uid'), $globals->asso('nom') . " : " .$art['titre'],
                                         $art['texte'], $art['contact_html']);
                    $article->submit();
                    $page->trigWarning("La parution dans la Lettre Mensuelle est en attente de validation.");
                }
            } else {
                XDB::query("UPDATE groupex.announces
                               SET titre={?}, texte={?}, contacts={?}, peremption={?},
                                   promo_min={?}, promo_max={?}, flags={?}
                             WHERE id={?} AND asso_id={?}",
                           $art['titre'], $art['texte'], $art['contacts'], $art['peremption'],
                           $promo_min, $promo_max,  $flags,
                           $art['id'], $globals->asso('id'));
                if ($art['photo'] && $upload->exists()) {
                    list($imgx, $imgy, $imgtype) = $upload->imageInfo();
                    XDB::execute("REPLACE INTO groupex.announces_photo
                                          SET eid = {?}, attachmime = {?}, x = {?}, y = {?}, attach = {?}",
                                 $aid, $imgtype, $imgx, $imgy, $upload->getContents());
                    $upload->rm();
                }
            }
        }
        if (Post::v('valid') == 'Enregistrer' || Post::v('valid') == 'Annuler') {
            pl_redirect("");
        }

        if (empty($art) && !is_null($aid)) {
            $res = XDB::query("SELECT a.*, u.nom, u.prenom, u.promo, l.alias AS forlife,
                                      FIND_IN_SET('public', a.flags) AS public,
                                      FIND_IN_SET('photo', a.flags) AS photo
                                 FROM groupex.announces AS a
                           INNER JOIN auth_user_md5 AS u USING(user_id)
                           INNER JOIN aliases AS l ON (l.id = u.user_id AND l.type = 'a_vie')
                                WHERE asso_id = {?} AND a.id = {?}",
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
                                      FROM groupex.evenements
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
            XDB::execute("DELETE  FROM groupex.announces
                           WHERE  id = {?} AND asso_id = {?}",
                         Env::i('del'), $globals->asso('id'));
        }
        $res = XDB::iterator("SELECT  a.id, a.titre, a.peremption, a.peremption < CURRENT_DATE() AS perime
                                FROM  groupex.announces AS a
                               WHERE  a.asso_id = {?}
                            ORDER BY  a.peremption DESC",
                             $globals->asso('id'));
        $page->assign('articles', $res);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
