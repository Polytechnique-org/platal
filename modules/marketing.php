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

    function handler_marketing($page)
    {
        $page->changeTpl('marketing/index.tpl');
        $page->setTitle('Marketing');

        $alive = new UserFilter(new PFC_Not(new UFC_Dead()));
        $registered = new UserFilter(new PFC_And(new UFC_Registered(), new PFC_Not(new UFC_Dead())));
        $alive72 = new UserFilter(new PFC_And(new UFC_Promo('>=', UserFilter::GRADE_ING, 1972), new PFC_Not(new UFC_Dead())));
        $registered72 = new UserFilter(new PFC_And(new UFC_Registered(), new UFC_Promo('>=', UserFilter::GRADE_ING, 1972), new PFC_Not(new UFC_Dead())));
        $aliveWomen = new UserFilter(new PFC_And(new UFC_Sex(User::GENDER_FEMALE) , new PFC_Not(new UFC_Dead())));
        $registeredWomen = new UserFilter(new PFC_And(new UFC_Registered(), new UFC_Sex(User::GENDER_FEMALE), new PFC_Not(new UFC_Dead())));
        $statistics = array(
            'alive'           => $alive->getTotalCount(),
            'registered'      => $registered->getTotalCount(),
            'alive72'         => $alive72->getTotalCount(),
            'registered72'    => $registered72->getTotalCount(),
            'womenAlive'      => $aliveWomen->getTotalCount(),
            'womenRegistered' => $registeredWomen->getTotalCount(),
        );
        $statistics['registeredRate']      = $statistics['registered'] / $statistics['alive'] * 100;
        $statistics['registeredRate72']    = $statistics['registered72'] / $statistics['alive72'] * 100;
        $statistics['womenRegisteredRate'] = $statistics['womenRegistered'] / $statistics['womenAlive'] * 100;

        $registeredWeek = new UserFilter(new PFC_And(new UFC_Registered(false, '>=', strtotime('1 week ago')), new PFC_Not(new UFC_Dead())));
        $registrationPending = XDB::fetchOneCell('SELECT  COUNT(*)
                                                    FROM  register_pending');
        $registrations = array(
            'week'    => $registeredWeek->getTotalCount(),
            'pending' => $registrationPending,
        );

        $ok = XDB::fetchOneCell('SELECT  COUNT(*)
                                   FROM  register_mstats
                                  WHERE  success != \'0000-00-00\'');
        $okWeek = XDB::fetchOneCell('SELECT  COUNT(*)
                                       FROM  register_mstats
                                      WHERE  success >= {?}', strtotime('1 week ago'));
        $res = XDB::fetchAllAssoc('SELECT  type, COUNT(*) as count
                                     FROM  register_marketing
                                 GROUP BY  type');
        $no = array();
        foreach ($res as $value) {
            $no[$value['type']] = $value['count'];
        }
        $no['week'] = XDB::fetchOneCell('SELECT  COUNT(*)
                                           FROM  register_marketing
                                          WHERE  last >= {?}', strtotime('1 week ago'));
        $marketings = array(
            'ok'      => $ok,
            'okWeek'  => $okWeek,
            'noPerso' => (isset($no['user']) ? $no['user'] : 0),
            'noXorg'  => (isset($no['staff']) ? $no['staff'] : 0),
            'noAX'    => (isset($no['ax']) ? $no['ax'] : 0),
            'noWeek'  => $no['week'],
        );

        $page->assign('statistics', $statistics);
        $page->assign('registrations', $registrations);
        $page->assign('marketings', $marketings);
    }

    function handler_private($page, $hruid = null,
                             $action = null, $value = null)
    {
        global $globals;
        $page->changeTpl('marketing/private.tpl');

        $user = User::getSilent($hruid);
        if (!$user) {
            return PL_NOT_FOUND;
        }

        // Retrieves marketed user details.
        if ($user->state != 'pending') {
            $page->kill('Cet utilisateur est déjà inscrit');
        }
        if (!$user->hasProfile()) {
            $page->kill('Cet utilisateur n\'est pas concerné par le marketing');
        }
        $matricule = $user->profile()->xorg_id;

        $matricule_X = Profile::getSchoolId($matricule);

        $page->assign('full_name', $user->fullName());
        $page->assign('promo', $user->promo());
        $page->assign('matricule', $matricule);
        $page->assign('matricule_X',$matricule_X);

        // Applies in-parameter action to the user.
        if ($action == 'del') {
            S::assert_xsrf_token();
            Marketing::clear($user->id(), $value);
        }

        if ($action == 'rel') {
            $market = Marketing::get($user->id(), $value);
            if ($market == null) {
                $page->trigWarning("Aucun marketing n'a été effectué vers $value");
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
            S::assert_xsrf_token();

            $market = Marketing::get($user->id(), Post::v('to'));
            if (is_null($market)) {
                $market = new Marketing($user->id(), Post::v('to'), 'default', null, 'staff');
            }
            $market->send(Post::v('title'), Post::v('message'));
            $page->trigSuccess("Email envoyé");
        }

        if ($action == 'insrel') {
            S::assert_xsrf_token();
            if (Marketing::relance($user->id())) {
                $page->trigSuccess('relance faite');
            }
        }

        if ($action == 'add' && Post::has('email') && Post::has('type')) {
            $market = new Marketing($user->id(), Post::v('email'), 'default', null, Post::v('type'), S::v('uid'));
            $market->add(false);
        }

        // Retrieves and display the existing marketing attempts.
        $res = XDB::iterator(
                "SELECT  r.*, s.email AS alias
                   FROM  register_marketing   AS r
              LEFT JOIN  email_source_account AS s ON (r.sender = s.uid AND s.type = 'forlife')
                  WHERE  r.uid = {?}
               ORDER BY  date", $user->id());
        $page->assign('addr', $res);

        $res = XDB::query("SELECT date, relance FROM register_pending
                            WHERE uid = {?}", $user->id());
        if (list($pending, $relance) = $res->fetchOneRow()) {
            $page->assign('pending', $pending);
            $page->assign('relance', $relance);
        }

        $page->assign('path', 'marketing/private/' . $user->login());
    }

    function handler_broken($page, $uid = null)
    {
        $page->changeTpl('marketing/broken.tpl');

        if (is_null($uid)) {
            return PL_NOT_FOUND;
        }

        $user = User::get($uid);
        if (!$user) {
            return PL_NOT_FOUND;
        } elseif ($user->login() == S::user()->login()) {
            pl_redirect('emails/redirect');
        }

        $res = XDB::query('SELECT  p.deathdate IS NULL AS alive, r.last, IF(r.type = \'googleapps\', \'googleapps\', r.redirect) AS active_email
                             FROM  accounts               AS a
                        LEFT JOIN  email_redirect_account AS r  ON (a.uid = r.uid AND r.type IN (\'smtp\', \'googleapps\') AND r.flags = \'active\')
                        LEFT JOIN  account_profiles       AS ap ON (ap.uid = r.uid AND FIND_IN_SET(\'owner\', ap.perms))
                        LEFT JOIN  profiles               AS p  ON (p.pid = ap.pid)
                            WHERE  a.uid = {?}
                         ORDER BY  r.broken_level, r.last',
                          $user->id());
        if (!$res->numRows()) {
            return PL_NOT_FOUND;
        }
        $user->addProperties($res->fetchOneAssoc());
        $page->assign('user', $user);

        $email = null;
        require_once 'emails.inc.php';
        if (Post::has('mail')) {
            $email = valide_email(Post::v('mail'));
        }
        if (Post::has('valide') && isvalid_email_redirection($email)) {
            S::assert_xsrf_token();

            // security stuff
            check_email($email, "Proposition d'une adresse surveillee pour " . $user->login() . " par " . S::user()->login());
            $state = XDB::fetchOneCell('SELECT  flags
                                          FROM  email_redirect_account
                                         WHERE  redirect = {?} AND uid = {?}',
                                       $email, $user->id());
            if ($state == 'broken') {
                $page->trigWarning("L'adresse que tu as fournie est l'adresse actuelle de {$user->fullName()} et est en panne.");
            } elseif ($state == 'active') {
                $page->trigWarning("L'adresse que tu as fournie est l'adresse actuelle de {$user->fullName()}");
            } elseif ($user->email && !Post::t('comment')) {
                $page->trigError("Il faut que tu ajoutes un commentaire à ta proposition pour justifier le "
                               . "besoin de changer la redirection de {$user->fullName()}.");
            } else {
                $valid = new BrokenReq(S::user(), $user, $email, trim(Post::v('comment')));
                $valid->submit();
                $page->assign('sent', true);
            }
        } elseif ($email) {
            $page->trigError("L'adresse proposée n'est pas une adresse acceptable pour une redirection.");
        }
    }

    function handler_promo($page, $promo = null)
    {
        $page->changeTpl('marketing/promo.tpl');

        if (is_null($promo)) {
            $promo = S::v('promo', S::user()->promo());
        }
        $page->assign('promo', $promo);

        $uf = new UserFilter(new PFC_And(new UFC_Promo('=', UserFilter::DISPLAY, $promo),
                                            new PFC_Not(new UFC_Registered())),
                                array(new UFO_Name(Profile::LASTNAME), new UFO_Name(Profile::FIRSTNAME)));
        $users = $uf->getUsers();
        $page->assign('nonins', $users);
    }

    function handler_public($page, $hruid = null)
    {
        $page->changeTpl('marketing/public.tpl');

        // Retrieves the user info, and checks the user is not yet registered.
        $user = User::getSilent($hruid);
        if (!$user || !$user->hasProfile()) {
            return PL_NOT_FOUND;
        }

        if ($user->state != 'pending') {
            $page->kill('Cet utilisateur est déjà inscrit');
        }

        // Displays the page, and handles the eventual user actions.
        $page->assign('full_name', $user->fullName());
        $page->assign('promo', $user->promo());

        if (Post::has('valide')) {
            S::assert_xsrf_token();
            $email = trim(Post::v('mail'));

            require_once 'emails.inc.php';
            if (!isvalid_email_redirection($email)) {
                $page->trigError('Email invalide&nbsp;!');
            } else {
                // On cherche les marketings précédents sur cette adresse
                // email, en se restreignant au dernier mois

                if (Marketing::get($user->id(), $email, true)) {
                    $page->assign('already', true);
                } else {
                    $page->assign('ok', true);
                    check_email($email, "Une adresse surveillée est proposée au marketing par " . S::user()->login());
                    $market = new Marketing($user->id(), $email, 'default', null, Post::v('origine'), S::v('uid'),
                                            Post::v('origine') == 'user' ? Post::v('personal_notes') : null);
                    $market->add();
                }
            }
        } else {
            global $globals;
            require_once 'marketing.inc.php';

            $sender = User::getSilent(S::v('uid'));
            $perso_signature = 'Cordialement,<br />-- <br />' . $sender->fullName();
            $market = new AnnuaireMarketing(null, true);
            $text = $market->getText(array(
                'sexe'           => $user->isFemale(),
                'forlife_email'  => $user->login() . '@' . $globals->mail->domain,
                'forlife_email2' => $user->login() . '@' . $globals->mail->domain2
            ));
            $text = str_replace('%%hash%%', '', $text);
            $text = str_replace('%%personal_notes%%', '<em id="personal_notes_display"></em>', $text);
            $text = str_replace('%%sender%%',
                                '<span id="sender">' . $perso_signature . '</span>', $text);
            $page->assign('text', nl2br($text));
            $page->assign('perso_signature', $perso_signature);
            $page->assign('mail_part', 'escaped_html');
        }
    }

    function handler_week($page, $sorting = 'per_promo')
    {
        $page->changeTpl('marketing/this_week.tpl');

        $sort = $sorting == 'per_promo' ? new UFO_Promo() : new UFO_Registration();

        $uf = new UserFilter(new UFC_Registered(false, '>', strtotime('1 week ago')), $sort);
        $page->assign('users', $uf->getUsers());
    }

    function handler_volontaire($page, $promo = null)
    {
        $page->changeTpl('marketing/volontaire.tpl');

        $res = XDB::query(
                'SELECT DISTINCT  pd.promo
                   FROM  register_marketing AS m
             INNER JOIN  account_profiles AS ap ON (m.uid = ap.uid AND FIND_IN_SET(\'owner\', ap.perms))
             INNER JOIN  profile_display AS pd ON (pd.pid = ap.pid)
               ORDER BY  pd.promo');
        $page->assign('promos', $res->fetchColumn());


        if (!is_null($promo)) {
            $it = XDB::iterator('SELECT  m.uid, m.email, s.email AS forlife
                                   FROM  register_marketing AS m
                             INNER JOIN  account_profiles AS ap ON (m.uid = ap.uid AND FIND_IN_SET(\'owner\', ap.perms))
                             INNER JOIN  profile_display AS pd ON (pd.pid = ap.pid)
                              LEFT JOIN  email_source_account AS s ON (m.sender = s.uid AND s.type = \'forlife\')
                                  WHERE  pd.promo = {?}
                               ORDER BY  pd.sort_name', $promo);
            $page->assign('addr', $it);
        }
    }

    function handler_relance($page)
    {
        $page->changeTpl('marketing/relance.tpl');

        if (Post::has('relancer')) {
            global $globals;
            $nbdix = $globals->core->NbIns;

            $sent  = Array();
            $users = User::getBulkUsersWithUIDs($_POST['relance']);
            foreach ($users as $user) {
                if ($tmp = Marketing::relance($user, $nbdix)) {
                    $sent[] = $tmp . ' a été relancé.';
                }
            }
            $page->assign('sent', $sent);
        }

        $page->assign('relance', XDB::iterator('SELECT  r.date, r.relance, r.uid
                                                  FROM  register_pending AS r
                                                 WHERE  hash != \'INSCRIT\'
                                              ORDER BY  date DESC'));
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
