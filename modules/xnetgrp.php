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

class XnetGrpModule extends PLModule
{
    function handlers()
    {
        return array(
            'grp'            => $this->make_hook('index',     AUTH_PUBLIC),
            'grp/asso.php'   => $this->make_hook('index',     AUTH_PUBLIC),
            'grp/logo'       => $this->make_hook('logo',      AUTH_PUBLIC),
            'grp/edit'       => $this->make_hook('edit',      AUTH_MDP),
            'grp/mail'       => $this->make_hook('mail',      AUTH_MDP),
            'grp/annuaire'   => $this->make_hook('annuaire',  AUTH_MDP),
            'grp/subscribe'  => $this->make_hook('subscribe', AUTH_MDP),

            'grp/admin/annuaire'
                 => $this->make_hook('admin_annuaire', AUTH_MDP),
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
        $page->assign('logged', logged());

        $page->assign('asso', $globals->asso());
    }

    function handler_logo(&$page)
    {
        global $globals;

        $res = $globals->xdb->query("SELECT logo, logo_mime
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
            readfile(dirname(__FILE__).'/../htdocs.net/images/dflt_carre.jpg');
        }

        exit;
    }

    function handler_edit(&$page)
    {
        global $globals;

        new_groupadmin_page('xnet/groupe/edit.tpl');

        if (Post::has('submit')) {
            if (has_perms()) {
                if (Post::get('mail_domain') && (strstr(Post::get('mail_domain'), '.') === false)) {
                    $page->trig_run("le domaine doit être un FQDN (aucune modif effectuée) !!!");
                }
                $globals->xdb->execute(
                    "UPDATE  groupex.asso
                        SET  nom={?}, diminutif={?}, cat={?}, dom={?},
                             descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, mail_domain={?}, ax={?}, pub={?},
                             sub_url={?}, inscriptible={?}
                      WHERE  id={?}",
                      Post::get('nom'), Post::get('diminutif'),
                      Post::get('cat'), Post::getInt('dom'),
                      Post::get('descr'), Post::get('site'),
                      Post::get('mail'), Post::get('resp'),
                      Post::get('forum'), Post::get('mail_domain'),
                      Post::has('ax'), Post::has('pub')?'private':'public',
                      Post::get('sub_url'), Post::get('inscriptible'),
                      $globals->asso('id'));
                if (Post::get('mail_domain')) {
                    $globals->xdb->execute('INSERT INTO virtual_domains (domain) VALUES({?})',
                                           Post::get('mail_domain'));
                }
            } else {
                $globals->xdb->execute(
                    "UPDATE  groupex.asso
                        SET  descr={?}, site={?}, mail={?}, resp={?},
                             forum={?}, ax={?}, pub= {?}, sub_url={?}
                      WHERE  id={?}",
                      Post::get('descr'), Post::get('site'),
                      Post::get('mail'), Post::get('resp'),
                      Post::get('forum'), Post::has('ax'),
                      Post::has('pub')?'private':'public',
                      Post::get('sub_url'), $globals->asso('id'));
            }

            if ($_FILES['logo']['name']) {
                $logo = file_get_contents($_FILES['logo']['tmp_name']);
                $mime = $_FILES['logo']['type'];
                $globals->xdb->execute('UPDATE groupex.asso
                                           SET logo={?}, logo_mime={?}
                                         WHERE id={?}', $logo, $mime,
                                        $globals->asso('id'));
            }

            redirect('../'.Post::get('diminutif', $globals->asso('diminutif')).'/edit');
        }

        if (has_perms()) {
            $dom = $globals->xdb->iterator('SELECT * FROM groupex.dom ORDER BY nom');
            $page->assign('dom', $dom);
            $page->assign('super', true);
        }
    }

    function handler_mail(&$page)
    {
        global $globals;

        require_once 'lists.inc.php';

        new_groupadmin_page('xnet/groupe/mail.tpl');
        $client =& lists_xmlrpc(Session::getInt('uid'),
                                Session::get('password'),
                                $globals->asso('mail_domain'));
        $page->assign('listes', $client->get_lists());

        if (Post::has('send')) {
            $from  = Post::get('from');
            $sujet = Post::get('sujet');
            $body  = Post::get('body');

            $mls = array_keys(Env::getMixed('ml', array()));

            require_once 'xnet/mail.inc.php';
            $tos = get_all_redirects(Post::has('membres'), $mls, $client);
            send_xnet_mails($from, $sujet, $body, $tos, Post::get('replyto'));
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

        switch (Env::get('order')) {
            case 'promo'    : $group = 'promo';    $tri = 'promo_o DESC, nom, prenom'; break;
            case 'promo_inv': $group = 'promo';    $tri = 'promo_o, nom, prenom'; break;
            case 'alpha_inv': $group = 'initiale'; $tri = 'nom DESC, prenom DESC, promo'; break;
            default         : $group = 'initiale'; $tri = 'nom, prenom, promo';
        }

        if ($group == 'initiale')
            $res = $globals->xdb->iterRow(
                        'SELECT  UPPER(SUBSTRING(
                                     IF(m.origine="X", IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom),
                                     1, 1)) as letter, COUNT(*)
                           FROM  groupex.membres AS m
                      LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                          WHERE  asso_id = {?}
                       GROUP BY  letter
                       ORDER BY  letter', $globals->asso('id'));
        else
            $res = $globals->xdb->iterRow(
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
            if (Env::has($group) && $char == strtoupper(Env::get($group))) {
                $tot = $nb;
            }
        }
        $page->assign('group', $group);
        $page->assign('request_group', Env::get($group));
        $page->assign('alphabet', $alphabet);
        $page->assign('nb_tot',   $nb_tot);

        $ofs   = Env::getInt('offset');
        $tot   = Env::get($group) ? $tot : $nb_tot;
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
                           m.nom) LIKE "'.addslashes(Env::get('initiale')).'%"';
        } elseif (Env::has('promo')) {
            $ini = 'AND IF(m.origine="X", u.promo, "extérieur") = "'
                 .addslashes(Env::get('promo')).'"';
        }

        $ann = $globals->xdb->iterator(
                  "SELECT  IF(m.origine='X',IF(u.nom_usage<>'', u.nom_usage, u.nom) ,m.nom) AS nom,
                           IF(m.origine='X',u.prenom,m.prenom) AS prenom,
                           IF(m.origine='X',u.promo,'extérieur') AS promo,
                           IF(m.origine='X',u.promo,'') AS promo_o,
                           IF(m.origine='X',a.alias,m.email) AS email,
                           IF(m.origine='X',FIND_IN_SET('femme', u.flags),0) AS femme,
                           m.perms='admin' AS admin,
                           m.origine='X' AS x,
                           m.uid
                     FROM  groupex.membres AS m
                LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type='a_vie' )
                    WHERE  m.asso_id = {?} $ini
                 ORDER BY  $tri
                    LIMIT  {?},{?}", $globals->asso('id'), $ofs*NB_PER_PAGE, NB_PER_PAGE);

        $page->assign('ann', $ann);
    }

    function handler_subscribe(&$page, $u = null)
    {
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
            $res = $globals->xdb->query("SELECT nom, prenom, promo, user_id
                                           FROM auth_user_md5 AS u
                                     INNER JOIN aliases AS al ON (al.id = u.user_id
                                                                  AND al.type != 'liste')
                                          WHERE al.alias = {?}", $u);

            if (list($nom, $prenom, $promo, $uid) = $res->fetchOneRow()) {
                $res = $globals->xdb->query("SELECT  COUNT(*)
                                               FROM  groupex.membres AS m
                                         INNER JOIN  aliases  AS a ON (m.uid = a.id
                                                                       AND a.type != 'homonyme')
                                              WHERE  a.alias = {?} AND m.asso_id = {?}",
                                            $u, $globals->asso('id'));
                $n   = $res->fetchOneCell();
                if ($n) {
                    $page->trig_run("$prenom $nom est déjà membre du groupe !");
                }
                elseif (Env::has('accept'))
                {
                    $globals->xdb->execute("INSERT INTO groupex.membres
                                            VALUES ({?}, {?}, 'membre', 'X', NULL, NULL, NULL, NULL)",
                                            $globals->asso('id'), $uid);
                    require_once 'diogenes/diogenes.hermes.inc.php';
                    $mailer = new HermesMailer();
                    $mailer->addTo("$u@polytechnique.org");
                    $mailer->setFrom('"'.Session::get('prenom').' '.Session::get('nom')
                                     .'" <'.Session::get('forlife').'@polytechnique.org>');
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
                    $mailer->setFrom('"'.Session::get('prenom').' '.Session::get('nom')
                                     .'" <'.Session::get('forlife').'@polytechnique.org>');
                    $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription annulée');
                    $mailer->setTxtBody(Env::get('motif'));
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
            $res = $globals->xdb->query('SELECT  IF(m.email IS NULL,
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
                    . Session::get('prenom').' '.Session::get('nom').' (X'.Session::get('promo').")\n"
                    . "Via le site www.polytechnique.net. Tu peux choisir de valider ou\n"
                    . "de refuser sa demande d'inscription depuis la page :\n"
                    .
                    "http://www.polytechnique.net/".$globals->asso("diminutif")."/subscribe/"
                        .Session::get('forlife')."\n"
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
            $mailer->setFrom('"'.Session::get('prenom').' '.Session::get('nom')
                             .'" <'.Session::get('forlife').'@polytechnique.org>');
            $mailer->setSubject('['.$globals->asso('nom').'] Demande d\'inscription');
            $mailer->setTxtBody(Post::get('message').$append);
            $mailer->send();
        }
    }

    function handler_admin_annuaire(&$page)
    {
        global $globals;

        require_once 'lists.inc.php';
        require_once 'xnet/mail.inc.php';

        new_groupadmin_page('xnet/groupe/annuaire-admin.tpl');
        $client =& lists_xmlrpc(Session::getInt('uid'),
                                Session::get('password'),
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
            $res = $globals->xdb->query(
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
}

?>
