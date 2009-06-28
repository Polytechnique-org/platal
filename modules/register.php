<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

class RegisterModule extends PLModule
{
    function handlers()
    {
        return array(
            'register'         => $this->make_hook('register', AUTH_PUBLIC),
            'register/end'     => $this->make_hook('end',      AUTH_PUBLIC),
        );
    }

    function handler_register(&$page, $hash = null)
    {
        $alert     = null;
        $sub_state = S::v('sub_state', Array());
        if (!isset($sub_state['step'])) {
            $sub_state['step'] = 0;
        }
        if (!isset($sub_state['backs'])) {
            $sub_state['backs'] = array();
        }
        if (Get::has('back') && Get::i('back') < $sub_state['step']) {
            $sub_state['step'] = max(0,Get::i('back'));
            $state = $sub_state;
            unset($state['backs']);
            $sub_state['backs'][] = $state;
            if (count($sub_state['backs']) == 3) {
                $alert .= "Tentative d'inscription très hésitante - ";
            }
        }

        if ($hash) {
            $res = XDB::query(
                    "SELECT  m.uid, u.promo, u.nom, u.prenom, u.matricule, u.naissance_ini, FIND_IN_SET('watch', u.flags)
                       FROM  register_marketing AS m
                 INNER JOIN  auth_user_md5      AS u ON u.user_id = m.uid
                      WHERE  m.hash={?}", $hash);
            if (list($uid, $promo, $nom, $prenom, $ourmat, $naiss, $watch) = $res->fetchOneRow()) {
                $sub_state['uid']    = $uid;
                $sub_state['hash']   = $hash;
                $sub_state['promo']  = $promo;
                $sub_state['nom']    = $nom;
                $sub_state['prenom'] = $prenom;
                $sub_state['ourmat'] = $ourmat;
                $sub_state['watch']  = $watch;
                $sub_state['naissance_ini'] = $naiss;

                XDB::execute(
                        "REPLACE INTO  register_mstats (uid,sender,success)
                               SELECT  m.uid, m.sender, 0
                                 FROM  register_marketing AS m
                                WHERE  m.hash", $sub_state['hash']);
            }
        }

        switch ($sub_state['step']) {
            case 0:
                $wp = new PlWikiPage('Reference.Charte');
                $wp->buildCache();
                if (Post::has('step1')) {
                    $sub_state['step'] = 1;
                    if (isset($sub_state['hash'])) {
                        $sub_state['step'] = 3;
                        $this->load('register.inc.php');
                        create_aliases($sub_state);
                    }
                }
                break;

            case 1:
                if (Post::has('promo')) {
                    $promo = Post::i('promo');
                    $res = XDB::query("SELECT COUNT(*)
                                         FROM auth_user_md5
                                        WHERE  perms='pending' AND deces = '0000-00-00'
                                               AND promo = {?}",
                                      $promo);
                    if (!$res->fetchOneCell()) {
                        $err = "La promotion saisie est incorrecte ou tous les camarades de cette promotion sont inscrits !";
                    } else {
                        $sub_state['step']  = 2;
                        $sub_state['promo'] = $promo;
                        if ($promo >= 1996 && $promo<2000) {
                            $sub_state['mat'] = ($promo % 100)*10 . '???';
                        } elseif($promo >= 2000) {
                            $sub_state['mat'] = 100 + ($promo % 100) . '???';
                        }
                    }
                }
                break;

            case 2:
                if (count($_POST)) {
                    $this->load('register.inc.php');
                    $sub_state['prenom'] = Post::v('prenom');
                    $sub_state['nom']    = Post::v('nom');
                    $sub_state['mat']    = Post::v('mat');
                    $err = check_new_user($sub_state);

                    if ($err !== true) { break; }
                    $err = create_aliases($sub_state);
                    if ($err === true) {
                        unset($err);
                        $sub_state['step'] = 3;
                    }
                }
                break;

            case 3:
                if (count($_POST)) {
                    $this->load('register.inc.php');

                    // Validate the email address format and domain.
                    require_once 'emails.inc.php';
                    if (!isvalid_email(Post::v('email'))) {
                        $err[] = "Le champ 'Email' n'est pas valide.";
                    } elseif (!isvalid_email_redirection(Post::v('email'))) {
                        $err[] = $sub_state['forlife']." doit renvoyer vers un email existant ".
                            "valide, en particulier, il ne peut pas être renvoyé vers lui-même.";
                    }

                    // Validate the birthday format and range.
                    $birth = trim(Env::v('naissance'));
                    if (!preg_match('@^[0-3]?\d/[01]?\d/(19|20)?\d{2}$@', $birth)) {
                        $err[] = "La 'Date de naissance' n'est pas correcte.";
                    } else {
                        $birth = explode('/', $birth, 3);
                        for ($i = 0; $i < 3; $i++)
                            $birth[$i] = intval($birth[$i]);
                        if ($birth[2] < 100) $birth[2] += 1900;
                        $year  = $birth[2];
                        $promo = (int)$sub_state['promo'];
                        if ($year > $promo - 15 || $year < $promo - 30) {
                            $err[] = "La 'Date de naissance' n'est pas correcte.";
                            $alert = "Date de naissance incorrecte à l'inscription - ";
                            $sub_state['wrong_naissance'] = $birth;
                        }
                    }

                    // Validate the password.
                    if (!Post::v('response2', false)) {
                        $err[] = "Le mot de passe n'est pas valide.";
                    }

                    // Check if the given email is known as dangerous.
                    $res = XDB::query("SELECT  w.state, w.description
                                         FROM  emails_watch AS w
                                        WHERE  w.email = {?} AND w.state != 'safe'",
                                        Post::v('email'));
                    $email_banned = false;
                    if ($res->numRows()) {
                        list($state, $description) = $res->fetchOneRow();
                        $alert .= "Email surveillé proposé à l'inscription - ";
                        $sub_state['email_desc'] = $description;
                        if ($state == 'dangerous') {
                            $email_banned = true;
                        }
                    }
                    if ($sub_state['watch']) {
                        $alert .= "Inscription d'un utilisateur surveillé - ";
                    }

                    if (($ip_banned = check_ip('unsafe'))) {
                        unset($err);
                    }

                    if (isset($err)) {
                        $err = join('<br />', $err);
                    } else {
                        $sub_state['naissance'] = sprintf("%04d-%02d-%02d",
                                                          intval($birth[2]), intval($birth[1]), intval($birth[0]));
                        $sub_state['email']     = Post::v('email');
                        $sub_state['password']  = Post::v('response2');

                        // Update the current alert if the birthdate is incorrect,
                        // or if the IP address of the user has been banned.
                        if ($sub_state['naissance_ini'] != '0000-00-00' && $sub_state['naissance'] != $sub_state['naissance_ini']) {
                            $alert .= "Date de naissance incorrecte à l'inscription - ";
                        }
                        if ($ip_banned) {
                            $alert .= "Tentative d'inscription depuis une IP surveillée";
                        }

                        // Prevent banned user from actually registering; save the current state for others.
                        if ($email_banned || $ip_banned) {
                            global $globals;
                            $err = "Une erreur s'est produite lors de l'inscription."
                                 . " Merci de contacter <a href='mailto:register@{$globals->mail->domain}>"
                                 . " register@{$globals->mail->domain}</a>"
                                 . " pour nous faire part de cette erreur";
                        } else {
                            $sub_state['step'] = 4;
                            if (count($sub_state['backs']) >= 3) {
                                $alert .= "Fin d'une inscription hésitante";
                            }
                            finish_ins($sub_state);
                        }
                    }
                }
                break;
        }

        $_SESSION['sub_state'] = $sub_state;
        if (!empty($alert)) {
            send_warning_mail($alert);
        }

        $page->changeTpl('register/step'.intval($sub_state['step']).'.tpl');
        $page->addJsLink('motdepasse.js');
        if (isset($err)) {
            $page->trigError($err);
        }
    }

    function handler_end(&$page, $hash = null)
    {
        global $globals;
        $_SESSION['sub_state'] = array('step' => 5);

        // Reject registration requests from unsafe IP addresses (and remove the
        // registration information from the database, to prevent IP changes).
        if (check_ip('unsafe')) {
            send_warning_mail('Une IP surveillée a tenté de finaliser son inscription');
            XDB::execute("DELETE FROM  register_pending
                                WHERE  hash = {?} AND hash != 'INSCRIT'", $hash);
            return PL_FORBIDDEN;
        }

        require_once('user.func.inc.php');

        // Retrieve the pre-registration information using the url-provided
        // authentication token.
        if ($hash) {
            $res = XDB::query(
                    "SELECT  r.uid, r.forlife, r.bestalias, r.mailorg2,
                             r.password, r.email, r.naissance, u.nom, u.prenom,
                             u.promo, FIND_IN_SET('femme', u.flags), u.naissance_ini
                       FROM  register_pending AS r
                 INNER JOIN  auth_user_md5    AS u ON r.uid = u.user_id
                      WHERE  hash = {?} AND hash != 'INSCRIT'", $hash);
        }
        if (!$hash || $res->numRows() == 0) {
            $page->kill("<p>Cette adresse n'existe pas, ou plus, sur le serveur.</p>
                         <p>Causes probables&nbsp;:</p>
                         <ol>
                           <li>Vérifie que tu visites l'adresse du dernier
                               email reçu s'il y en a eu plusieurs.</li>
                           <li>Tu as peut-être mal copié l'adresse reçue par
                               email, vérifie-la à la main.</li>
                           <li>Tu as peut-être attendu trop longtemps pour
                               confirmer.  Les pré-inscriptions sont annulées
                               tous les 30 jours.</li>
                           <li>Tu es en fait déjà inscrit.</li>
                        </ol>");
        }

        list($uid, $forlife, $bestalias, $mailorg2, $password, $email,
             $naissance, $nom, $prenom, $promo, $femme, $naiss_ini) = $res->fetchOneRow();

        // Prepare the template for display.
        $page->changeTpl('register/end.tpl');
        $page->addJsLink('do_challenge_response_logged.js');
        $page->assign('forlife', $forlife);
        $page->assign('prenom', $prenom);
        $page->assign('femme', $femme);

        // Check if the user did enter a valid password; if not (or if none is found),
        // get her an information page.
        if (Env::has('response')) {
            require_once 'secure_hash.inc.php';
            $expected_response = hash_encrypt("$forlife:$password:" . S::v('challenge'));
            if (Env::v('response') != $expected_response) {
                $page->trigError("Mot de passe invalide.");
                S::logger($uid)->log('auth_fail', 'bad password (register/end)');
                return;
            }
        } else {
            return;
        }

        //
        // Create the user account.
        //
        XDB::execute("UPDATE  auth_user_md5
                         SET  password = {?}, perms = 'user',
                              date = NOW(), naissance = {?}, date_ins = NOW()
                       WHERE  user_id = {?}", $password, $naissance, $uid);
        XDB::execute("REPLACE INTO auth_user_quick (user_id) VALUES ({?})", $uid);
        XDB::execute("INSERT INTO  aliases (id, alias, type)
                           VALUES  ({?}, {?}, 'a_vie')", $uid, $forlife);
        XDB::execute("INSERT INTO  aliases (id, alias, type, flags)
                           VALUES  ({?}, {?}, 'alias', 'bestalias')", $uid, $bestalias);
        if ($mailorg2) {
            XDB::execute("INSERT INTO  aliases (id, alias, type)
                               VALUES  ({?}, {?}, 'alias')", $uid, $mailorg2);
        }

        // Add the registration email address as first and only redirection.
        require_once('emails.inc.php');
        $user = User::getSilent($uid);
        $redirect = new Redirect($user);
        $redirect->add_email($email);

        // Log the registration in the user session.
        S::logger($uid)->log('inscription', $email);
        XDB::execute("UPDATE  register_pending
                         SET  hash = 'INSCRIT'
                       WHERE  uid = {?}", $uid);

        // Congratulate our newly registered user by email.
        $mymail = new PlMailer('register/inscription.reussie.tpl');
        $mymail->assign('forlife', $forlife);
        $mymail->assign('prenom', $prenom);
        $mymail->send();

        // Index the user, to allow her to appear in searches.
        require_once('user.func.inc.php');
        user_reindex($uid);

        // Notify other users which were watching for her arrival.
        require_once 'notifs.inc.php';
        register_watch_op($uid, WATCH_INSCR);
        inscription_notifs_base($uid);

        // Forcibly register the new user on default forums.
        $promo_forum = 'xorg.promo.x' . $promo;
        $registered_forums = array('xorg.general', 'xorg.pa.divers', 'xorg.pa.logements', $promo_forum);
        foreach ($registered_forums as $forum) {
            XDB::execute("INSERT INTO  forums.abos (fid,uid)
                               SELECT  fid, {?}
                                 FROM   forums.list
                                WHERE  nom = {?}",
                                $uid, $val);

            // Notify the newsgroup admin of the promotion forum needs be created.
            if (XDB::affectedRows() == 0 && $forum == $promo_forum) {
                $res = XDB::query("SELECT  SUM(perms IN ('admin','user') AND deces = 0), COUNT(*)
                                     FROM  auth_user_md5
                                    WHERE  promo = {?}", $promo);
                list($promo_registered_count, $promo_count) = $res->fetchOneRow();
                if ($promo_registered_count > 0.2 * $promo_count) {
                    $mymail = new PlMailer('admin/forums-promo.mail.tpl');
                    $mymail->assign('promo', $promo);
                    $mymail->send();
                }
            }
        }

        // Update the global registration count stats.
        $globals->updateNbIns();

        // Try to start a session (so the user don't have to log in); we will use
        // the password available in Post:: to authenticate the user.
        Platal::session()->start(AUTH_MDP);

        //
        // Update collateral data sources, and inform watchers by email.
        //

        // Email the referrer(s) of this new user.
        $res = XDB::iterRow(
                "SELECT  sa.alias, IF(s.nom_usage,s.nom_usage,s.nom) AS nom,
                         s.prenom, FIND_IN_SET('femme', s.flags) AS femme,
                         GROUP_CONCAT(m.email) AS mails, MAX(m.last) AS dateDernier
                   FROM  register_marketing AS m
             INNER JOIN  auth_user_md5      AS s  ON (m.sender = s.user_id)
             INNER JOIN  aliases            AS sa ON (sa.id = m.sender
                                                      AND FIND_IN_SET('bestalias', sa.flags))
                  WHERE  m.uid = {?}
               GROUP BY  m.sender
               ORDER BY  dateDernier DESC", $uid);
        XDB::execute("UPDATE  register_mstats
                         SET  success = NOW()
                       WHERE  uid = {?}", $uid);

        $market = array();
        while (list($salias, $snom, $sprenom, $sfemme, $mails, $dateDernier) = $res->next()) {
            $market[] = " - par $snom $sprenom sur $mails (le plus récemment le $dateDernier)";
            $mymail = new PlMailer();
            $mymail->setSubject("$prenom $nom s'est inscrit à Polytechnique.org !");
            $mymail->setFrom('"Marketing Polytechnique.org" <register@' . $globals->mail->domain . '>');
            $mymail->addTo("\"$sprenom $snom\" <$salias@{$globals->mail->domain}>");
            $msg = ($sfemme?'Chère':'Cher')." $sprenom,\n\n"
                 . "Nous t'écrivons pour t'informer que $prenom $nom (X$promo), "
                 . "que tu avais incité".($femme?'e':'')." à s'inscrire à Polytechnique.org, "
                 . "vient à l'instant de terminer son inscription.\n\n"
                 . "Merci de ta participation active à la reconnaissance de ce site !!!\n\n"
                 . "Bien cordialement,\n"
                 . "-- \n"
                 . "L'équipe Polytechnique.org";
            $mymail->setTxtBody(wordwrap($msg, 72));
            $mymail->send();
        }

        // Email the plat/al administrators about the registration.
        if ($globals->register->notif) {
            $mymail = new PlMailer();
            $mymail->setSubject("Inscription de $prenom $nom (X$promo)");
            $mymail->setFrom('"Webmaster Polytechnique.org" <web@' . $globals->mail->domain . '>');
            $mymail->addTo($globals->register->notif);
            $mymail->addHeader('Reply-To', $globals->register->notif);
            $msg = "$prenom $nom (X$promo) a terminé son inscription avec les données suivantes :\n"
                 . " - nom       : $nom\n"
                 . " - prenom    : $prenom\n"
                 . " - promo     : $promo\n"
                 . " - naissance : $naissance (date connue : $naiss_ini)\n"
                 . " - forlife   : $forlife\n"
                 . " - email     : $email\n"
                 . " - sexe      : $femme\n"
                 . " - ip        : " . S::logger()->ip . " (" . S::logger()->host . ")\n"
                 . (S::logger()->proxy_ip ? " - proxy     : " . S::logger()->proxy_ip . " (" . S::logger()->proxy_host . ")\n" : "")
                 . "\n\n";
            if (count($market) > 0) {
                $msg .= "Les marketings suivants avaient été effectués :\n"
                     . implode("\n", $market);
            } else {
                $msg .= "$prenom $nom n'a jamais reçu d'email de marketing.";
            }
            $mymail->setTxtBody($msg);
            $mymail->send();
        }

        // Remove old pending marketing requests for the new user.
        Marketing::clear($uid);

        pl_redirect('profile/edit');
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
