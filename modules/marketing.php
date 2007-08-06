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

class MarketingModule extends PLModule
{
    function handlers()
    {
        return array(
            'marketing'            => $this->make_hook('marketing',  AUTH_MDP, 'admin'),
            'marketing/promo'      => $this->make_hook('promo',      AUTH_MDP, 'admin'),
            'marketing/relance'    => $this->make_hook('relance',    AUTH_MDP, 'admin'),
            'marketing/this_week'  => $this->make_hook('week',       AUTH_MDP, 'admin'),
            'marketing/volontaire' => $this->make_hook('volontaire', AUTH_MDP, 'admin'),

            'marketing/private'    => $this->make_hook('private',    AUTH_MDP, 'admin'),
            'marketing/public'     => $this->make_hook('public',     AUTH_COOKIE),
            'marketing/broken'     => $this->make_hook('broken',     AUTH_COOKIE),
        );
    }

    function handler_marketing(&$page)
    {
        $page->changeTpl('marketing/index.tpl');

        $page->assign('xorg_title','Polytechnique.org - Marketing');

        // Quelques statistiques

        $res   = XDB::query(
                  "SELECT COUNT(*) AS vivants,
                          COUNT(NULLIF(perms='admin' OR perms='user', 0)) AS inscrits,
                          100*COUNT(NULLIF(perms='admin' OR perms='user', 0))/COUNT(*) AS ins_rate,
                          COUNT(NULLIF(promo >= 1972, 0)) AS vivants72,
                          COUNT(NULLIF(promo >= 1972 AND (perms='admin' OR perms='user'), 0)) AS inscrits72,
                          100 * COUNT(NULLIF(promo >= 1972 AND (perms='admin' OR perms='user'), 0)) /
                              COUNT(NULLIF(promo >= 1972, 0)) AS ins72_rate,
                          COUNT(NULLIF(FIND_IN_SET('femme', flags), 0)) AS vivantes,
                          COUNT(NULLIF(FIND_IN_SET('femme', flags) AND (perms='admin' OR perms='user'), 0)) AS inscrites,
                          100 * COUNT(NULLIF(FIND_IN_SET('femme', flags) AND (perms='admin' OR perms='user'), 0)) /
                              COUNT(NULLIF(FIND_IN_SET('femme', flags), 0)) AS inse_rate
                     FROM auth_user_md5
                    WHERE deces = 0");
        $stats = $res->fetchOneAssoc();
        $page->assign('stats', $stats);

        $res   = XDB::query("SELECT count(*) FROM auth_user_md5 WHERE date_ins > ".
                                      date('Ymd000000', strtotime('1 week ago')));
        $page->assign('nbInsSem', $res->fetchOneCell());

        $res = XDB::query("SELECT count(*) FROM register_pending WHERE hash != 'INSCRIT'");
        $page->assign('nbInsEnCours', $res->fetchOneCell());

        $res = XDB::query("SELECT count(*) FROM register_marketing");
        $page->assign('nbInsMarket', $res->fetchOneCell());

        $res = XDB::query("SELECT count(*) FROM register_mstats
                                      WHERE TO_DAYS(NOW()) - TO_DAYS(success) <= 7");
        $page->assign('nbInsMarkOK', $res->fetchOneCell());
    }

    function handler_private(&$page, $uid = null,
                             $action = null, $value = null)
    {
        $page->changeTpl('marketing/private.tpl');

        if (is_null($uid)) {
            return PL_NOT_FOUND;
        }

        $page->assign('path', 'marketing/private/'.$uid);

        $res = XDB::query("SELECT  nom, prenom, promo, matricule
                             FROM  auth_user_md5
                            WHERE  user_id={?} AND perms='pending'", $uid);

        if (list($nom, $prenom, $promo, $matricule) = $res->fetchOneRow()) {
            require_once('user.func.inc.php');
            $matricule_X = get_X_mat($matricule);
            $page->assign('nom', $nom);
            $page->assign('prenom', $prenom);
            $page->assign('promo', $promo);
            $page->assign('matricule', $matricule);
            $page->assign('matricule_X',$matricule_X);
        } else {
            $page->kill('uid invalide');
        }

        if ($action == 'del') {
            Marketing::clear($uid, $value);
        }

        if ($action == 'rel') {
            $market = Marketing::get($uid, $value);
            if ($market == null) {
                $page->trig("Aucun marketing n'a été effectué vers $value");
            } else {
                $to    = $market->user['to'];
                $title = $market->getTitle();
                $text  = $market->getText();
                $from  = $market->sender_mail;
                $page->assign('rel_from_user', $from);
                $page->assign('rel_from_staff',
                              '"Equipe Polytechnique.org" <register@' . $globals->mail->domain . '>');
                $page->assign('rel_to', $to);
                $page->assign('rel_title', $title);
                $page->assign('rel_text', $text);
                $page->assign('rel_email', $value);
            }
        }

        if ($action == 'relforce') {
            $market = Marketing::get($uid, Post::v('to'));
            if (is_null($market)) {
                $market = new Marketing($uid, Post::v('to'), 'default', null, 'staff');
            }
            $market->send(Post::v('title'), Post::v('message'));
            $page->trig("Mail envoyé");
        }

        if ($action == 'insrel') {
            if (Marketing::relance($uid)) {
                $page->trig('relance faite');
            }
        }

        if ($action == 'add' && Post::has('email') && Post::has('type')) {
            $market = new Marketing($uid, Post::v('email'), 'default', null, Post::v('type'), S::v('uid'));
            $market->add(false);
        }

        $res = XDB::iterator(
                "SELECT  r.*, a.alias
                   FROM  register_marketing AS r
             INNER JOIN  aliases            AS a ON (r.sender=a.id AND a.type = 'a_vie')
                  WHERE  uid={?}
               ORDER BY  date", $uid);
        $page->assign('addr', $res);

        $res = XDB::query("SELECT date, relance FROM register_pending
                            WHERE uid = {?}", $uid);
        if (list($pending, $relance) = $res->fetchOneRow()) {
            $page->assign('pending', $pending);
            $page->assign('relance', $relance);
        }
    }

    function handler_broken(&$page, $uid = null)
    {
        require_once('user.func.inc.php');
        $page->changeTpl('marketing/broken.tpl');

        if (is_null($uid)) {
            return PL_NOT_FOUND;
        }
        $forlife = get_user_forlife($uid);
        if (!$forlife) {
            return PL_NOT_FOUND;
        } elseif ($forlife == S::v('forlife')) {
            pl_redirect('emails/redirect');
        }

        $res = Xdb::query("SELECT  u.nom, u.prenom, u.promo, FIND_IN_SET('femme', u.flags) AS sexe,
                                   a.alias AS forlife, b.alias AS bestalias, e.email, e.last
                             FROM  auth_user_md5 AS u
                       INNER JOIN  aliases       AS a ON (a.id = u.user_id AND a.type = 'a_vie')
                       INNER JOIN  aliases       AS b ON (b.id = u.user_id AND FIND_IN_SET('bestalias', b.flags))
                        LEFT JOIN  emails        AS e ON (e.flags = 'active' AND e.uid = u.user_id)
                            WHERE  a.alias = {?}
                         ORDER BY  e.panne_level, e.last", $forlife);
        if (!$res->numRows()) {
            return PL_NOT_FOUND;
        }
        $user = $res->fetchOneAssoc();
        $page->assign('user', $user);

        $email = null;
        if (Post::has('mail')) {
            require_once 'emails.inc.php';
            $email = valide_email(Post::v('mail'));
        }
        if (Post::has('valide') && isvalid_email_redirection($email)) {
            // security stuff
            check_email($email, "Proposition d'une adresse surveillee pour " . $user['forlife'] . " par " . S::v('forlife'));
            $res = XDB::query("SELECT  state
                                 FROM  emails   AS e
                           INNER JOIN  aliases  AS a ON (a.id = e.uid)
                                WHERE  e.email = {?} AND a.alias = {?}", $email, $user['forlife']);
            $state = $res->numRows() ? $res->fetchOneCell() : null;
            if ($state == 'panne') {
                $page->trig("L'adresse que tu as fournie est l'adresse actuelle de {$user['prenom']} et est en panne.");
            } elseif ($state == 'active') {
                $page->trig("L'adresse que tu as fournie est l'adresse actuelle de {$user['prenom']}");
            } elseif ($user['email'] && !trim(Post::v('comment'))) {
                $page->trig("Il faut que tu ajoutes un commentaire à ta proposition pour justifier le "
                           ."besoin de changer la redirection de " . $user['prenom']);
            } else {
                require_once 'validations.inc.php';
                $valid = new BrokenReq(S::i('uid'), $user, $email, trim(Post::v('comment')));
                $valid->submit();
                $page->assign('sent', true);
            }
        } elseif ($email) {
            $page->trig("L'adresse proposée n'est pas une adresse acceptable pour une redirection");
        }
    }

    function handler_promo(&$page, $promo = null)
    {
        $page->changeTpl('marketing/promo.tpl');

        if (is_null($promo)) {
            $promo = S::v('promo');
        }
        $page->assign('promo', $promo);

        $sql = "SELECT  u.user_id, u.nom, u.prenom, u.last_known_email, u.matricule_ax,
                        IF(MAX(m.last) > p.relance, MAX(m.last), p.relance) AS dern_rel, p.email
                  FROM  auth_user_md5      AS u
             LEFT JOIN  register_pending   AS p ON p.uid = u.user_id
             LEFT JOIN  register_marketing AS m ON m.uid = u.user_id
                 WHERE  u.promo = {?} AND u.deces = 0 AND u.perms='pending'
              GROUP BY  u.user_id
              ORDER BY  nom, prenom";
        $page->assign('nonins', XDB::iterator($sql, $promo));
    }

    function handler_public(&$page, $uid = null)
    {
        $page->changeTpl('marketing/public.tpl');

        if (is_null($uid)) {
            return PL_NOT_FOUND;
        }

        $res = XDB::query("SELECT nom, prenom, promo FROM auth_user_md5
                                      WHERE user_id={?} AND perms='pending'", $uid);

        if (list($nom, $prenom, $promo) = $res->fetchOneRow()) {
            $page->assign('prenom', $prenom);
            $page->assign('nom', $nom);
            $page->assign('promo', $promo);

            if (Post::has('valide')) {
                require_once('xorg.misc.inc.php');
                $email = trim(Post::v('mail'));

                if (!isvalid_email_redirection($email)) {
                    $page->trig("Email invalide !");
                } else {
                    // On cherche les marketings précédents sur cette adresse
                    // email, en se restreignant au dernier mois
                    
                    if (Marketing::get($uid, $email, true)) {
                        $page->assign('already', true);
                    } else {
                        $page->assign('ok', true);
                        check_email($email, "Une adresse surveillée est proposée au marketing par " . S::v('forlife'));
                        $market = new Marketing($uid, $email, 'default', null, Post::v('origine'), S::v('uid'));
                        $market->add();
                    }
                }
            }
        }
    }

    function handler_week(&$page, $sorting = 'per_promo')
    {
        $page->changeTpl('marketing/this_week.tpl');

        $sort = $sorting == 'per_promo' ? 'promo' : 'date_ins';

        $sql = "SELECT  a.alias AS forlife, u.date_ins, u.promo, u.nom, u.prenom
                  FROM  auth_user_md5  AS u
            INNER JOIN  aliases        AS a ON (u.user_id = a.id AND a.type='a_vie')
                 WHERE  u.date_ins > ".date("Ymd000000", strtotime ('1 week ago'))."
              ORDER BY  u.$sort DESC";
        $page->assign('ins', XDB::iterator($sql));
    }

    function handler_volontaire(&$page, $promo = null)
    {
        $page->changeTpl('marketing/volontaire.tpl');

        $res = XDB::query(
                "SELECT
               DISTINCT  a.promo
                   FROM  register_marketing AS m
             INNER JOIN  auth_user_md5      AS a  ON a.user_id = m.uid
               ORDER BY  a.promo");
        $page->assign('promos', $res->fetchColumn());


        if (!is_null($promo)) {
            $sql = "SELECT  a.nom, a.prenom, a.user_id,
                            m.email, sa.alias AS forlife
                      FROM  register_marketing AS m
                INNER JOIN  auth_user_md5      AS a  ON a.user_id = m.uid AND a.promo = {?}
                INNER JOIN  aliases            AS sa ON (m.sender = sa.id AND sa.type='a_vie')
                  ORDER BY  a.nom";
            $page->assign('addr', XDB::iterator($sql, $promo));
        }
    }

    function handler_relance(&$page)
    {
        $page->changeTpl('marketing/relance.tpl');

        if (Post::has('relancer')) {
            $res   = XDB::query("SELECT COUNT(*) FROM auth_user_md5 WHERE deces=0");
            $nbdix = $res->fetchOneCell();

            $sent  = Array();
            foreach (array_keys($_POST['relance']) as $uid) {
                if ($tmp = Marketing::relance($uid, $nbdix)) {
                    $sent[] = $tmp.' a été relancé';
                }
            }
            $page->assign('sent', $sent);
        }

        $sql = "SELECT  r.date, r.relance, r.uid, u.promo, u.nom, u.prenom
                  FROM  register_pending AS r
            INNER JOIN  auth_user_md5    AS u ON r. uid = u.user_id
                 WHERE  hash!='INSCRIT'
              ORDER BY  date DESC";
        $page->assign('relance', XDB::iterator($sql));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
