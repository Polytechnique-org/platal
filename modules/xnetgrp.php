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
            "SELECT  uid, nom, prenom, email, email AS email2, perms='admin', origine
               FROM  groupex.membres
              WHERE  $field = {?} AND asso_id = {?}", $email, $globals->asso('id'));

    if ($res->numRows()) {
        return $res->fetchOneAssoc();
    } elseif ($dom == 'polytechnique.org' || $dom == 'm4x.org') {
        $res = XDB::query(
                "SELECT  user_id AS uid, u.promo,
                         IF(u.nom_usage<>'', u.nom_usage, u.nom) AS nom,
                         u.prenom, b.alias,
                         CONCAT(b.alias, '@m4x.org') AS email,
                         CONCAT(b.alias, '@polytechnique.org') AS email2,
                         m.perms='admin' AS perms, m.origine
                   FROM  auth_user_md5   AS u
             INNER JOIN  aliases         AS a ON ( u.user_id = a.id AND a.type != 'homonyme' )
             INNER JOIN  aliases         AS b ON ( u.user_id = b.id AND b.type = 'a_vie' )
             INNER JOIN  groupex.membres AS m ON ( m.uid = u.user_id AND asso_id={?})
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
            '%grp'            => $this->make_hook('index',     AUTH_PUBLIC),
            '%grp/asso.php'   => $this->make_hook('index',     AUTH_PUBLIC),
            '%grp/logo'       => $this->make_hook('logo',      AUTH_PUBLIC),
            '%grp/edit'       => $this->make_hook('edit',      AUTH_MDP),
            '%grp/mail'       => $this->make_hook('mail',      AUTH_MDP),
            '%grp/annuaire'   => $this->make_hook('annuaire',  AUTH_MDP),
            '%grp/subscribe'  => $this->make_hook('subscribe', AUTH_MDP),
            '%grp/paiement'   => $this->make_hook('paiement',  AUTH_MDP),

            '%grp/admin/annuaire'
                 => $this->make_hook('admin_annuaire', AUTH_MDP),

            '%grp/member'
                 => $this->make_hook('admin_member', AUTH_MDP),
            '%grp/member/new'
                 => $this->make_hook('admin_member_new', AUTH_MDP),
            '%grp/member/del'
                 => $this->make_hook('admin_member_del', AUTH_MDP),
        );
    }

    function handler_index(&$page, $arg = null)
    {
        global $globals;

        if (!is_null($arg)) {
            return PL_NOT_FOUND;
        }

        $page->changeTpl('xnet/groupe/asso.tpl');
        $page->useMenu();
        $page->setType($globals->asso('cat'));
        $page->assign('is_member', is_member());
        $page->assign('logged', S::logged());

        $page->assign('asso', $globals->asso());
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

    function handler_edit(&$page)
    {
        global $globals;

        new_groupadmin_page('xnet/groupe/edit.tpl');

        if (Post::has('submit')) {
            if (S::has_perms()) {
                if (Post::v('mail_domain') && (strstr(Post::v('mail_domain'), '.') === false)) {
                    $page->trig("le domaine doit être un FQDN (aucune modif effectuée) !!!");
                    return;
                }
                XDB::execute(
                    "UPDATE  groupex.asso
                        SET  nom={?}, diminutif={?}, cat={?}, dom={?},
                             descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, mail_domain={?}, ax={?}, pub={?},
                             sub_url={?}, inscriptible={?}
                      WHERE  id={?}",
                      Post::v('nom'), Post::v('diminutif'),
                      Post::v('cat'), Post::i('dom'),
                      Post::v('descr'), Post::v('site'),
                      Post::v('mail'), Post::v('resp'),
                      Post::v('forum'), Post::v('mail_domain'),
                      Post::has('ax'), Post::has('pub')?'private':'public',
                      Post::v('sub_url'), Post::v('inscriptible'),
                      $globals->asso('id'));
                if (Post::v('mail_domain')) {
                    XDB::execute('INSERT INTO virtual_domains (domain) VALUES({?})',
                                           Post::v('mail_domain'));
                }
            } else {
                XDB::execute(
                    "UPDATE  groupex.asso
                        SET  descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, ax={?}, pub= {?}, sub_url={?}
                      WHERE  id={?}",
                      Post::v('descr'), Post::v('site'),
                      Post::v('mail'), Post::v('resp'),
                      Post::v('forum'), Post::has('ax'),
                      Post::has('pub')?'private':'public',
                      Post::v('sub_url'), $globals->asso('id'));
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
    }

    function handler_mail(&$page)
    {
        global $globals;

        require_once 'lists.inc.php';

        new_groupadmin_page('xnet/groupe/mail.tpl');
        $client =& lists_xmlrpc(S::v('uid'),
                                S::v('password'),
                                $globals->asso('mail_domain'));
        $page->assign('listes', $client->get_lists());

        if (Post::has('send')) {
            $from  = Post::v('from');
            $sujet = Post::v('sujet');
            $body  = Post::v('body');

            $mls = array_keys(Env::v('ml', array()));

            require_once 'xnet/mail.inc.php';
            $tos = get_all_redirects(Post::has('membres'), $mls, $client);
            send_xnet_mails($from, $sujet, $body, $tos, Post::v('replyto'));
            $page->kill("Mail envoyé !");
            $page->assign('sent', true);
        }
    }

    function handler_annuaire(&$page)
    {
        global $globals;

        define('NB_PER_PAGE', 25);

        if ($globals->asso('pub') == 'public') {
            new_group_page('xnet/groupe/annuaire.tpl');
        } else {
            new_groupadmin_page('xnet/groupe/annuaire.tpl');
        }

        $page->assign('admin', may_update());

        switch (Env::v('order')) {
            case 'promo'    : $group = 'promo';    $tri = 'promo_o DESC, nom, prenom'; break;
            case 'promo_inv': $group = 'promo';    $tri = 'promo_o, nom, prenom'; break;
            case 'alpha_inv': $group = 'initiale'; $tri = 'nom DESC, prenom DESC, promo'; break;
            default         : $group = 'initiale'; $tri = 'nom, prenom, promo';
        }

        if ($group == 'initiale')
            $res = XDB::iterRow(
                        'SELECT  UPPER(SUBSTRING(
                                     IF(m.origine="X", IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom),
                                     1, 1)) as letter, COUNT(*)
                           FROM  groupex.membres AS m
                      LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                          WHERE  asso_id = {?}
                       GROUP BY  letter
                       ORDER BY  letter', $globals->asso('id'));
        else
            $res = XDB::iterRow(
                        'SELECT  IF(m.origine="X",u.promo,"extérieur") AS promo,
                                 COUNT(*), IF(m.origine="X",u.promo,"") AS promo_o
                           FROM  groupex.membres AS m
                      LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                          WHERE  asso_id = {?}
                       GROUP BY  promo
                       ORDER BY  promo_o DESC', $globals->asso('id'));

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
            $ini = 'AND IF(m.origine="X", u.promo, "extérieur") = "'
                 .addslashes(Env::v('promo')).'"';
        }

        $ann = XDB::iterator(
                  "SELECT  IF(m.origine='X',IF(u.nom_usage<>'', u.nom_usage, u.nom) ,m.nom) AS nom,
                           IF(m.origine='X',u.prenom,m.prenom) AS prenom,
                           IF(m.origine='X',u.promo,'extérieur') AS promo,
                           IF(m.origine='X',u.promo,'') AS promo_o,
                           IF(m.origine='X',a.alias,m.email) AS email,
                           IF(m.origine='X',FIND_IN_SET('femme', u.flags), m.sexe) AS femme,
                           m.perms='admin' AS admin,
                           m.origine='X' AS x,
                           m.uid
                     FROM  groupex.membres AS m
                LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type='a_vie' )
                    WHERE  m.asso_id = {?} $ini
                           AND (m.origine = 'ext' OR u.perms != 'pending')
                 ORDER BY  $tri
                    LIMIT  {?},{?}", $globals->asso('id'), $ofs*NB_PER_PAGE, NB_PER_PAGE);

        $page->assign('ann', $ann);
    }

    function handler_subscribe(&$page, $u = null)
    {
        global $globals;

        $page->changeTpl('xnet/groupe/inscrire.tpl');

        $page->useMenu();
        $page->setType($globals->asso('cat'));
        $page->assign('asso', $globals->asso());
        $page->assign('admin', may_update());

        if (!$globals->asso('inscriptible'))
                $page->kill("Il n'est pas possible de s'inscire en ligne à ce "
                            ."groupe. Essaie de joindre le contact indiqué "
                            ."sur la page de présentation.");

        if (!is_null($u) && may_update()) {
            $page->assign('u', $u);
            $res = XDB::query("SELECT nom, prenom, promo, user_id
                                           FROM auth_user_md5 AS u
                                     INNER JOIN aliases AS al ON (al.id = u.user_id
                                                                  AND al.type != 'liste')
                                          WHERE al.alias = {?}", $u);

            if (list($nom, $prenom, $promo, $uid) = $res->fetchOneRow()) {
                $res = XDB::query("SELECT  COUNT(*)
                                               FROM  groupex.membres AS m
                                         INNER JOIN  aliases  AS a ON (m.uid = a.id
                                                                       AND a.type != 'homonyme')
                                              WHERE  a.alias = {?} AND m.asso_id = {?}",
                                            $u, $globals->asso('id'));
                $n   = $res->fetchOneCell();
                if ($n) {
                    $page->kill("$prenom $nom est déjà membre du groupe !");
                    return;
                }
                elseif (Env::has('accept'))
                {
                    XDB::execute("INSERT INTO groupex.membres
                                            VALUES ({?}, {?}, 'membre', 'X', NULL, NULL, NULL, NULL, NULL)",
                                            $globals->asso('id'), $uid);
                    require_once 'diogenes/diogenes.hermes.inc.php';
                    $mailer = new HermesMailer();
                    $mailer->addTo("$u@polytechnique.org");
                    $mailer->setFrom('"'.S::v('prenom').' '.S::v('nom')
                                     .'" <'.S::v('forlife').'@polytechnique.org>');
                    $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription');
                    $message = "Cher Camarade,\n"
                             . "\n"
                             . "  Suite à ta demande d'adhésion à ".$globals->asso('nom').",\n"
                             . "j'ai le plaisir de t'annoncer que ton inscription a été validée !\n"
                             . "\n"
                             . "Bien cordialement,\n"
                             . "{$_SESSION["prenom"]} {$_SESSION["nom"]}.";
                    $mailer->setTxtBody($message);
                    $mailer->send();
                    $page->kill("$prenom $nom a bien été inscrit");
                }
                elseif (Env::has('refuse'))
                {
                    require_once 'diogenes/diogenes.hermes.inc.php';
                    $mailer = new HermesMailer();
                    $mailer->addTo("$u@polytechnique.org");
                    $mailer->setFrom('"'.S::v('prenom').' '.S::v('nom')
                                     .'" <'.S::v('forlife').'@polytechnique.org>');
                    $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription annulée');
                    $mailer->setTxtBody(Env::v('motif'));
                    $mailer->send();
                    $page->kill("la demande $prenom $nom a bien été refusée");
                } else {
                    $page->assign('show_form', true);
                    $page->assign('prenom', $prenom);
                    $page->assign('nom', $nom);
                    $page->assign('promo', $promo);
                    $page->assign('uid', $uid);
                }
                return;
            }
            return PL_NOT_FOUND;
        }

        if (is_member()) {
            $page->kill("tu es déjà membre !");
            return;
        }

        if (Post::has('inscrire')) {
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

            require_once 'diogenes/diogenes.hermes.inc.php';
            $mailer = new HermesMailer();
            $mailer->addTo($to);
            $mailer->setFrom('"'.S::v('prenom').' '.S::v('nom')
                             .'" <'.S::v('forlife').'@polytechnique.org>');
            $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription');
            $mailer->setTxtBody(Post::v('message').$append);
            $mailer->send();
        }
    }

    function handler_paiement(&$page)
    {
        global $globals;

        new_group_page('xnet/groupe/telepaiement.tpl');

        $res = XDB::query(
                "SELECT id, text
                  FROM {$globals->money->mpay_tprefix}paiements
                 WHERE asso_id = {?} AND NOT FIND_IN_SET(flags, 'old')
              ORDER BY id DESC", $globals->asso('id'));
        $tit = $res->fetchAllAssoc();
        $page->assign('titres', $tit);

        $order = Env::v('order', 'timestamp');
        $orders = array('timestamp', 'nom', 'promo', 'montant');
        if (!in_array($order, $orders)) {
            $order = 'timestamp';
        }
        $inv_order = Env::v('order_inv', 0);
        $page->assign('order', $order);
        $page->assign('order_inv', !$inv_order);

        if ($order == 'timestamp') {
            $inv_order = !$inv_order;
        }

        if ($inv_order) {
            $inv_order = ' DESC';
        } else {
            $inv_order = '';
        }
        if ($order == 'montant') {
            $order = 'LENGTH(montant) '.$inv_order.', montant';
        }

        $orderby = 'ORDER BY '.$order.$inv_order;
        if ($order != 'nom') {
            $orderby .= ', nom'; $inv_order = '';
        }
        $orderby .= ', prenom'.$inv_order;
        if ($order != 'timestamp') {
            $orderby .= ', timestamp DESC';
        }

        if (may_update()) {
            $trans = array();
            foreach($tit as $foo) {
                $pid = $foo['id'];
                $res = XDB::query(
                        "SELECT  IF(u.nom_usage<>'', u.nom_usage, u.nom) AS nom,
                                 u.prenom, u.promo, a.alias, timestamp AS `date`, montant
                           FROM  {$globals->money->mpay_tprefix}transactions AS t
                     INNER JOIN  auth_user_md5  AS u ON ( t.uid = u.user_id )
                     INNER JOIN  aliases        AS a ON ( t.uid = a.id AND a.type='a_vie' )
                          WHERE  ref = {?} ".$orderby, $pid);
                $trans[$pid] = $res->fetchAllAssoc();
                $sum = 0;
                foreach ($trans[$pid] as $i => $t) {
                    $sum += strtr(substr($t['montant'], 0, strpos($t['montant'], 'EUR')), ',', '.');
                }
                $trans[$pid][] = array('nom' => 'somme totale',
                                       'montant' => strtr($sum, '.', ',').' EUR');
            }
            $page->assign('trans', $trans);
        }
    }

    function handler_admin_annuaire(&$page)
    {
        global $globals;

        require_once 'lists.inc.php';
        require_once 'xnet/mail.inc.php';

        new_groupadmin_page('xnet/groupe/annuaire-admin.tpl');
        $client =& lists_xmlrpc(S::v('uid'), S::v('password'),
                                $globals->asso('mail_domain'));
        $lists  = $client->get_lists();
        if (!$lists) $lists = array();
        $listes = array_map(create_function('$arr', 'return $arr["list"];'), $lists);

        $subscribers = array();

        foreach ($listes as $list) {
            list(,$members) = $client->get_members($list);
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

        new_groupadmin_page('xnet/groupe/membres-add.tpl');

        if (is_null($email)) {
            return;
        }

        list(,$fqdn) = explode('@', $email);
        $fqdn = strtolower($fqdn);
        $x = ($fqdn == 'polytechnique.org' || $fqdn == 'melix.org' ||
              $fqdn == 'm4x.org' || $fqdn == 'melix.net');

        if ($x) {
            require_once 'user.func.inc.php';
            if ($forlife = get_user_forlife($email)) {
                XDB::execute(
                    'INSERT INTO  groupex.membres (uid,asso_id,origine)
                          SELECT  user_id,{?},"X"
                            FROM  auth_user_md5 AS u
                      INNER JOIN  aliases       AS a ON (u.user_id = a.id)
                           WHERE  a.alias={?}', $globals->asso('id'), $forlife);
                pl_redirect("member/$email");
            } else {
                $page->trig($email." n'est pas un alias polytechnique.org valide");
            }
        } else {
            if (isvalid_email($email)) {
                $res = XDB::query('SELECT MAX(uid)+1 FROM groupex.membres');
                $uid = max(intval($res->fetchOneCell()), 50001);
                XDB::execute('INSERT INTO  groupex.membres (uid,asso_id,origine,email)
                                        VALUES({?},{?},"ext",{?})', $uid,
                                        $globals->asso('id'), $email);
                pl_redirect("member/$email");
            } else {
                $page->trig("« <strong>$email</strong> » n'est pas une adresse mail valide");
            }
        }
    }

    function handler_admin_member_del(&$page, $user = null)
    {
        global $globals;

        new_groupadmin_page('xnet/groupe/membres-del.tpl');
        $user = get_infos($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }
        $page->assign('user', $user);

        if (!Post::has('confirm')) {
            return;
        }

        XDB::execute(
                "DELETE FROM  groupex.membres WHERE uid={?} AND asso_id={?}",
                $user['uid'], $globals->asso('id'));

        // don't unsubscribe email from list if other user use same email
        $user_same_email = get_infos($user['email']);

        if (($domain = $globals->asso('mail_domain')) && empty($user_same_email)) {

            require 'lists.inc.php';
            $client =& lists_xmlrpc(S::v('uid'), S::v('password'), $domain);
            $listes = $client->get_lists($user['email2']);

            foreach ($listes as $liste) {
                if ($liste['sub'] == 2) {
                    $client->mass_unsubscribe($liste['list'], Array($user['email2']));
                    $page->trig("{$user['prenom']} {$user['nom']} a été"
                                ." désinscrit de {$liste['list']}");
                } elseif ($liste['sub']) {
                    $page->trig("{$user['prenom']} {$user['nom']} a une"
                                ." demande d'inscription en cours sur la"
                                ." liste {$liste['list']}@ !");
                }
            }

            XDB::execute(
                    "DELETE FROM  virtual_redirect
                           USING  virtual_redirect
                      INNER JOIN  virtual USING(vid)
                           WHERE  redirect={?} AND alias LIKE {?}", $user['email'], '%@'.$domain);
            if (mysql_affected_rows()) {
                $page->trig("{$user['prenom']} {$user['nom']} a été désabonné des alias du groupe !");
            }
        }

        $page->trig("{$user['prenom']} {$user['nom']} a été retiré du groupe !");
    }

    function handler_admin_member(&$page, $user)
    {
        global $globals;

        new_groupadmin_page('xnet/groupe/membres-edit.tpl');

        $user = get_infos($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }

        require 'lists.inc.php';
        $client =& lists_xmlrpc(S::v('uid'), S::v('password'),
                                $globals->asso('mail_domain'));

        if (Post::has('change')) {
            if ($user['origine'] != 'X') {
                XDB::query('UPDATE groupex.membres
                               SET prenom={?}, nom={?}, email={?}, sexe={?}
                             WHERE uid={?} AND asso_id={?}',
                           Post::v('prenom'), Post::v('nom'),
                           Post::v('email'), Post::v('sexe'),
                           $user['uid'], $globals->asso('id'));
                $user['nom']    = Post::v('nom');
                $user['prenom'] = Post::v('prenom');
                $user['sexe']   = Post::v('sexe');
                $user['email']  = Post::v('email');
                $user['email2'] = Post::v('email');
            }

            $perms = Post::i('is_admin');
            if ($user['perms'] != $perms) {
                XDB::query('UPDATE groupex.membres SET perms={?}
                             WHERE uid={?} AND asso_id={?}',
                            $perms ? 'admin' : 'membre',
                            $user['uid'], $globals->asso('id'));
                $user['perms'] = $perms;
                $page->trig('permissions modifiées');
            }

            foreach (Env::v('ml1', array()) as $ml => $state) {
                $ask = empty($_REQUEST['ml2'][$ml]) ? 0 : 2;
                if ($ask == $state) continue;
                if ($state == '1') {
                    $page->trig("{$user['prenom']} {$user['nom']} a "
                               ."actuellement une demande d'inscription en "
                               ."cours sur <strong>$ml@</strong> !!!");
                } elseif ($ask) {
                    $client->mass_subscribe($ml, Array($user['email2']));
                    $page->trig("{$user['prenom']} {$user['nom']} a été abonné à $ml@");
                } else {
                    $client->mass_unsubscribe($ml, Array($user['email2']));
                    $page->trig("{$user['prenom']} {$user['nom']} a été désabonné de $ml@");
                }
            }

            foreach (Env::v('ml3', array()) as $ml => $state) {
                $ask = !empty($_REQUEST['ml4'][$ml]);
                if($state == $ask) continue;
                if($ask) {
                    XDB::query("INSERT INTO  virtual_redirect (vid,redirect)
                                               SELECT  vid,{?} FROM virtual WHERE alias={?}",
                                         $user['email'], $ml);
                    $page->trig("{$user['prenom']} {$user['nom']} a été abonné à $ml");
                } else {
                    XDB::query("DELETE FROM  virtual_redirect
                                                USING  virtual_redirect
                                           INNER JOIN  virtual USING(vid)
                                                WHERE  redirect={?} AND alias={?}",
                                         $user['email'], $ml);
                    $page->trig("{$user['prenom']} {$user['nom']} a été désabonné de $ml");
                }
            }
        }

        $page->assign('user', $user);
        $listes = $client->get_lists($user['email2']);
        $page->assign('listes', $listes);

        $res = XDB::query(
                'SELECT  alias, redirect IS NOT NULL as sub
                   FROM  virtual          AS v
              LEFT JOIN  virtual_redirect AS vr ON(v.vid=vr.vid AND redirect={?})
                  WHERE  alias LIKE {?} AND type="user"',
                $user['email'], '%@'.$globals->asso('mail_domain'));
        $page->assign('alias', $res->fetchAllAssoc());
    }
}

?>
