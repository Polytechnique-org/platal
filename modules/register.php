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

class RegisterModule extends PLModule
{
    function handlers()
    {
        return array(
            'register'     => $this->make_hook('register', AUTH_PUBLIC),
            'register/end' => $this->make_hook('end',      AUTH_PUBLIC),
        );
    }

    function handler_register($page, $hash = null)
    {
        $alert = null;
        $subState = new PlDict(S::v('subState', array()));
        if (!$subState->has('step')) {
            $subState->set('step', 0);
        }
        if (!$subState->has('backs')) {
            $subState->set('backs', new PlDict());
        }
        if (Get::has('back') && Get::i('back') < $subState->i('step')) {
            $subState->set('step', max(0, Get::i('back')));
            $subState->v('backs')->set($subState->v('backs')->count() + 1, $subState->dict());
            $subState->v('backs')->kill('backs');
            if ($subState->v('backs')->count() == 3) {
                $alert .= "Tentative d'inscription très hésitante - ";
            }
        }

        if ($hash) {
            $nameTypes = DirEnum::getOptions(DirEnum::NAMETYPES);
            $nameTypes = array_flip($nameTypes);
            $res = XDB::query("SELECT  a.uid, pd.promo, pnl.name AS lastname, pnf.name AS firstname, p.xorg_id AS xorgid,
                                       p.birthdate_ref AS birthdateRef, FIND_IN_SET('watch', a.flags) AS watch, m.hash, a.type
                                 FROM  register_marketing AS m
                           INNER JOIN  accounts           AS a   ON (m.uid = a.uid)
                           INNER JOIN  account_profiles   AS ap  ON (a.uid = ap.uid AND FIND_IN_SET('owner', ap.perms))
                           INNER JOIN  profiles           AS p   ON (p.pid = ap.pid)
                           INNER JOIN  profile_display    AS pd  ON (p.pid = pd.pid)
                           INNER JOIN  profile_name       AS pnl ON (p.pid = pnl.pid AND pnl.typeid = {?})
                           INNER JOIN  profile_name       AS pnf ON (p.pid = pnf.pid AND pnf.typeid = {?})
                                WHERE  m.hash = {?} AND a.state = 'pending'",
                              $nameTypes['name_ini'], $nameTypes['firstname_ini'], $hash);

            if ($res->numRows() == 1) {
                $subState->merge($res->fetchOneRow());
                $subState->set('main_mail_domain', User::$sub_mail_domains[$subState->v('type')]);
                $subState->set('yearpromo', substr($subState->s('promo'), 1, 4));

                XDB::execute('INSERT INTO  register_mstats (uid, sender, success)
                                   SELECT  m.uid, m.sender, 0
                                     FROM  register_marketing AS m
                                    WHERE  m.hash
                  ON DUPLICATE KEY UPDATE  sender = VALUES(sender), success = VALUES(success)',
                             $subState->s('hash'));
            }
        }

        switch ($subState->i('step')) {
            case 0:
                $wp = new PlWikiPage('Reference.Charte');
                $wp->buildCache();
                if (Post::has('step1')) {
                    $subState->set('step', 1);
                    if ($subState->has('hash')) {
                        $subState->set('step', 3);
                        $this->load('register.inc.php');
                        createAliases($subState);
                    }
                }
                break;

            case 1:
                if (Post::has('yearpromo')) {
                    $edu_type = Post::t('edu_type');
                    $yearpromo = Post::i('yearpromo');
                    $promo = $edu_type . $yearpromo;
                    $res = XDB::query("SELECT  COUNT(*)
                                         FROM  accounts         AS a
                                   INNER JOIN  account_profiles AS ap ON (a.uid = ap.uid AND FIND_IN_SET('owner', ap.perms))
                                   INNER JOIN  profiles         AS p  ON (p.pid = ap.pid)
                                   INNER JOIN  profile_display  AS pd ON (p.pid = pd.pid)
                                        WHERE  a.state = 'pending' AND p.deathdate IS NULL AND pd.promo = {?}",
                                      $promo);

                    if (!$res->fetchOneCell()) {
                        $error = 'La promotion saisie est incorrecte ou tous les camarades de cette promotion sont inscrits !';
                    } else {
                        $subState->set('step', 2);
                        $subState->set('promo', $promo);
                        $subState->set('yearpromo', $yearpromo);
                        $subState->set('edu_type', $edu_type);
                        if ($edu_type == 'X') {
                            if ($yearpromo >= 1996 && $yearpromo < 2000) {
                                $subState->set('schoolid', ($yearpromo % 100) * 10 . '???');
                            } elseif($yearpromo >= 2000) {
                                $subState->set('schoolid', 100 + ($yearpromo % 100) . '???');
                            }
                        } else {
                            $subState->set('schoolid', '');
                        }
                    }
                }
                break;

            case 2:
                if (count($_POST)) {
                    $this->load('register.inc.php');
                    $subState->set('firstname', Post::t('firstname'));
                    $subState->set('lastname', Post::t('lastname'));
                    $subState->set('schoolid', Post::i('schoolid'));
                    $error = checkNewUser($subState);

                    if ($error !== true) {
                        break;
                    }
                    $error = createAliases($subState);
                    if ($error === true) {
                        unset($error);
                        $subState->set('step', 3);
                    }
                }
                break;

            case 3:
                if (count($_POST)) {
                    $this->load('register.inc.php');

                    // Validate the email address format and domain.
                    require_once 'emails.inc.php';

                    if (!isvalid_email(Post::v('email'))) {
                        $error[] = "Le champ 'Email' n'est pas valide.";
                    } elseif (!isvalid_email_redirection(Post::v('email'))) {
                        $error[] = $subState->s('forlife') . ' doit renvoyer vers un email existant '
                                 . 'valide, en particulier, il ne peut pas être renvoyé vers lui-même.';
                    }

                    // Validate the birthday format and range.
                    $birth = Post::t('birthdate');
                    if (!preg_match('@^[0-3]?\d/[01]?\d/(19|20)?\d{2}$@', $birth)) {
                        $error[] = "La 'Date de naissance' n'est pas correcte.";
                    } else {
                        $birth = explode('/', $birth, 3);
                        for ($i = 0; $i < 3; ++$i)
                            $birth[$i] = intval($birth[$i]);
                        if ($birth[2] < 100) {
                            $birth[2] += 1900;
                        }
                        $year  = $birth[2];
                        $promo = $subState->i('yearpromo');
                        if ($year > $promo - 15 || $year < $promo - 30) {
                            $error[] = "La 'Date de naissance' n'est pas correcte.";
                            $alert = "Date de naissance incorrecte à l'inscription - ";
                            $subState->set('wrong_birthdate', $birth);
                        }
                    }

                    // Register the optional services requested by the user.
                    $services = array();
                    foreach (array('ax_letter', 'imap', 'ml_promo', 'nl') as $service) {
                        if (Post::b($service)) {
                            $services[] = $service;
                        }
                    }
                    $subState->set('services', $services);

                    // Validate the password.
                    if (!Post::v('pwhash', false)) {
                        $error[] = "Le mot de passe n'est pas valide.";
                    }

                    // Check if the given email is known as dangerous.
                    $res = XDB::query("SELECT  state, description
                                         FROM  email_watch
                                        WHERE  email = {?} AND state != 'safe'",
                                      Post::v('email'));
                    $bannedEmail = false;
                    if ($res->numRows()) {
                        list($state, $description) = $res->fetchOneRow();
                        $alert .= "Email surveillé proposé à l'inscription - ";
                        $subState->set('email_desc', $description);
                        if ($state == 'dangerous') {
                            $bannedEmail = true;
                        }
                    }
                    if ($subState->i('watch') != 0) {
                        $alert .= "Inscription d'un utilisateur surveillé - ";
                    }

                    if (($bannedIp = check_ip('unsafe'))) {
                        unset($error);
                    }

                    if (isset($error)) {
                        $error = join('<br />', $error);
                    } else {
                        $subState->set('birthdate', sprintf("%04d-%02d-%02d",
                                                            intval($birth[2]), intval($birth[1]), intval($birth[0])));
                        $subState->set('email', Post::t('email'));
                        $subState->set('password', Post::t('pwhash'));

                        // Update the current alert if the birthdate is incorrect,
                        // or if the IP address of the user has been banned.
                        if ($subState->s('birthdateRef') != '0000-00-00'
                            && $subState->s('birthdateRef') != $subState->s('birthdate')) {
                            $alert .= "Date de naissance incorrecte à l'inscription - ";
                        }
                        if ($bannedIp) {
                            $alert .= "Tentative d'inscription depuis une IP surveillée";
                        }

                        // Prevent banned user from actually registering; save the current state for others.
                        if ($bannedEmail || $bannedIp) {
                            global $globals;
                            $error = "Une erreur s'est produite lors de l'inscription."
                                 . " Merci de contacter <a href='mailto:register@{$globals->mail->domain}>"
                                 . " register@{$globals->mail->domain}</a>"
                                 . " pour nous faire part de cette erreur.";
                        } else {
                            $subState->set('step', 4);
                            if ($subState->v('backs')->count() >= 3) {
                                $alert .= "Fin d'une inscription hésitante.";
                            }
                            finishRegistration($subState);
                        }
                    }
                }
                break;
        }

        $_SESSION['subState'] = $subState->dict();
        if (!empty($alert)) {
            send_warning_mail($alert);
        }

        $page->changeTpl('register/step' . $subState->i('step') . '.tpl');
        if (isset($error)) {
            $page->trigError($error);
        }
    }

    function handler_end($page, $hash = null)
    {
        global $globals;
        $_SESSION['subState'] = array('step' => 5);

        // Reject registration requests from unsafe IP addresses (and remove the
        // registration information from the database, to prevent IP changes).
        if (check_ip('unsafe')) {
            send_warning_mail('Une IP surveillée a tenté de finaliser son inscription.');
            XDB::execute("DELETE FROM  register_pending
                                WHERE  hash = {?} AND hash != 'INSCRIT'", $hash);
            return PL_FORBIDDEN;
        }

        $nameTypes = DirEnum::getOptions(DirEnum::NAMETYPES);
        $nameTypes = array_flip($nameTypes);

        // Retrieve the pre-registration information using the url-provided
        // authentication token.
        $res = XDB::query("SELECT  r.uid, p.pid, r.forlife, r.bestalias, r.mailorg2,
                                   r.password, r.email, r.services, r.naissance,
                                   pnl.name AS lastname, pnf.name AS firstname,
                                   pd.promo, p.sex, p.birthdate_ref, a.type AS eduType
                             FROM  register_pending AS r
                       INNER JOIN  accounts         AS a   ON (r.uid = a.uid)
                       INNER JOIN  account_profiles AS ap  ON (a.uid = ap.uid AND FIND_IN_SET('owner', ap.perms))
                       INNER JOIN  profiles         AS p   ON (p.pid = ap.pid)
                       INNER JOIN  profile_name     AS pnl ON (p.pid = pnl.pid AND pnl.typeid = {?})
                       INNER JOIN  profile_name     AS pnf ON (p.pid = pnf.pid AND pnf.typeid = {?})
                       INNER JOIN  profile_display  AS pd  ON (p.pid = pd.pid)
                            WHERE  hash = {?} AND hash != 'INSCRIT' AND a.state = 'pending'",
                          $nameTypes['name_ini'], $nameTypes['firstname_ini'], $hash);
        if (!$hash || $res->numRows() == 0) {
            $page->kill("<p>Cette adresse n'existe pas, ou plus, sur le serveur.</p>
                         <p>Causes probables&nbsp;:</p>
                         <ol>
                           <li>Vérifie que tu visites l'adresse du dernier
                               email reçu s'il y en a eu plusieurs.</li>
                           <li>Tu as peut-être mal copié l'adresse reçue par
                               email, vérifie-la à la main.</li>
                           <li>Tu as peut-être attendu trop longtemps pour
                               confirmer. Les pré-inscriptions sont annulées
                               tous les 30 jours.</li>
                           <li>Tu es en fait déjà inscrit.</li>
                        </ol>");
        }

        list($uid, $pid, $forlife, $bestalias, $emailXorg2, $password, $email, $services,
             $birthdate, $lastname, $firstname, $promo, $sex, $birthdate_ref, $eduType) = $res->fetchOneRow();
        $isX = ($eduType == 'x');
        $yearpromo = substr($promo, 1, 4);
        $mail_domain = User::$sub_mail_domains[$eduType] . $globals->mail->domain;

        // Prepare the template for display.
        $page->changeTpl('register/end.tpl');
        $page->assign('forlife', $forlife);
        $page->assign('firstname', $firstname);

        // Check if the user did enter a valid password; if not (or if none is found),
        // get her an information page.
        if (Post::has('response')) {
            $expected_response = sha1("$forlife:$password:" . S::v('challenge'));
            if (Post::v('response') != $expected_response) {
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
        XDB::startTransaction();
        XDB::execute("UPDATE  accounts
                         SET  password = {?}, state = 'active',
                              registration_date = NOW(), email = NULL
                       WHERE  uid = {?}", $password, $uid);
        XDB::execute("UPDATE  profiles
                         SET  birthdate = {?}, last_change = NOW()
                       WHERE  pid = {?}", $birthdate, $pid);
        XDB::execute('INSERT INTO  email_source_account (email, uid, type, flags, domain)
                           SELECT  {?}, {?}, \'forlife\', \'\', id
                             FROM  email_virtual_domains
                            WHERE  name = {?}',
                     $forlife, $uid, $mail_domain);
        XDB::execute('INSERT INTO  email_source_account (email, uid, type, flags, domain)
                           SELECT  {?}, {?}, \'alias\', \'bestalias\', id
                             FROM  email_virtual_domains
                            WHERE  name = {?}',
                     $bestalias, $uid, $mail_domain);
        if ($emailXorg2) {
            XDB::execute('INSERT INTO  email_source_account (email, uid, type, flags, domain)
                               SELECT  {?}, {?}, \'alias\', \'\', id
                                 FROM  email_virtual_domains
                                WHERE  name = {?}',
                         $emailXorg2, $uid, $mail_domain);
        }
        XDB::commit();

        // Add the registration email address as first and only redirection.
        require_once 'emails.inc.php';
        $user = User::getSilentWithUID($uid);
        $redirect = new Redirect($user);
        $redirect->add_email($email);

        // Try to start a session (so the user don't have to log in); we will use
        // the password available in Post:: to authenticate the user.
        Platal::session()->start(AUTH_MDP);

        // Subscribe the user to the services she did request at registration time.
        foreach (explode(',', $services) as $service) {
            require_once 'newsletter.inc.php';
            switch ($service) {
                case 'ax_letter':
                    NewsLetter::forGroup(NewsLetter::GROUP_AX)->subscribe($user);
                    break;
                case 'nl':
                    NewsLetter::forGroup(NewsLetter::GROUP_XORG)->subscribe($user);
                    break;
                case 'imap':
                    Email::activate_storage($user, 'imap');
                    break;
                case 'ml_promo':
                    $r = XDB::query('SELECT id FROM groups WHERE diminutif = {?}', $yearpromo);
                    if ($r->numRows()) {
                        $asso_id = $r->fetchOneCell();
                        XDB::execute('INSERT IGNORE INTO  group_members (uid, asso_id)
                                                  VALUES  ({?}, {?})',
                                     $uid, $asso_id);
                        try {
                            $mmlist = new MMList($user);
                            $mmlist->subscribe("promo" . $yearpromo);
                        } catch (Exception $e) {
                            PlErrorReport::report($e);
                            $page->trigError("L'inscription à la liste promo" . $yearpromo . " a échouée.");
                        }
                    }
                    break;
            }
        }

        // Log the registration in the user session.
        S::logger($uid)->log('inscription', $email);
        XDB::execute("UPDATE  register_pending
                         SET  hash = 'INSCRIT'
                       WHERE  uid = {?}", $uid);

        // Congratulate our newly registered user by email.
        $mymail = new PlMailer('register/success.mail.tpl');
        $mymail->addTo("\"{$user->fullName()}\" <{$user->forlifeEmail()}>");
        if ($isX) {
            $mymail->setSubject('Bienvenue parmi les X sur le web !');
        } else {
            $mymail->setSubject('Bienvenue sur Polytechnique.org !');
        }
        $mymail->assign('forlife', $forlife);
        $mymail->assign('firstname', $firstname);
        $mymail->send();

        // Index the user, to allow her to appear in searches.
        Profile::rebuildSearchTokens($pid);

        // Notify other users which were watching for her arrival.
        XDB::execute('INSERT INTO  contacts (uid, contact)
                           SELECT  uid, ni_id
                             FROM  watch_nonins
                            WHERE  ni_id = {?}', $uid);
        XDB::execute('DELETE FROM  watch_nonins
                            WHERE  ni_id = {?}', $uid);
        Platal::session()->updateNbNotifs();

        // Forcibly register the new user on default forums.
        $promoForum = 'xorg.promo.' . strtolower($promo);
        $registeredForums = array('xorg.general', 'xorg.pa.divers', 'xorg.pa.logements', $promoForum);
        foreach ($registeredForums as $forum) {
            XDB::execute("INSERT INTO  forum_subs (fid, uid)
                               SELECT  fid, {?}
                                 FROM  forums
                                WHERE  name = {?}",
                         $uid, $val);

            // Notify the newsgroup admin of the promotion forum needs be created.
            if (XDB::affectedRows() == 0 && $forum == $promoForum) {
                $promoFull = new UserFilter(new UFC_Promo('=', UserFilter::DISPLAY, $promo));
                $promoRegistered = new UserFilter(new PFC_And(
                        new UFC_Promo('=', UserFilter::DISPLAY, $promo),
                        new UFC_Registered(true),
                        new PFC_Not(new UFC_Dead())
                ));
                if ($promoRegistered->getTotalCount() > 0.2 * $promoFull->getTotalCount()) {
                    $mymail = new PlMailer('admin/forums-promo.mail.tpl');
                    $mymail->assign('promo', $promo);
                    $mymail->send();
                }
            }
        }

        // Update the global registration count stats.
        $globals->updateNbIns();

        //
        // Update collateral data sources, and inform watchers by email.
        //

        // Email the referrer(s) of this new user.
        $res = XDB::iterRow("SELECT  sender, GROUP_CONCAT(email SEPARATOR ', ') AS mails, MAX(last) AS lastDate
                               FROM  register_marketing
                              WHERE  uid = {?}
                           GROUP BY  sender
                           ORDER BY  lastDate DESC", $uid);
        XDB::execute("UPDATE  register_mstats
                         SET  success = NOW()
                       WHERE  uid = {?}", $uid);

        $market = array();
        while (list($senderid, $maketingEmails, $lastDate) = $res->next()) {
            $sender = User::getWithUID($senderid);
            $market[] = " - par {$sender->fullName()} sur $maketingEmails (le plus récemment le $lastDate)";
            $mymail = new PlMailer('register/marketer.mail.tpl');
            $mymail->setSubject("$firstname $lastname s'est inscrit à Polytechnique.org !");
            $mymail->addTo($sender);
            $mymail->assign('sender', $sender);
            $mymail->assign('firstname', $firstname);
            $mymail->assign('lastname', $lastname);
            $mymail->assign('promo', $promo);
            $mymail->assign('sex', $sex);
            $mymail->setTxtBody(wordwrap($msg, 72));
            $mymail->send();
        }

        // Email the plat/al administrators about the registration.
        if ($globals->register->notif) {
            $mymail = new PlMailer('register/registration.mail.tpl');
            $mymail->setSubject("Inscription de $firstname $lastname ($promo)");
            $mymail->assign('firstname', $firstname);
            $mymail->assign('lastname', $lastname);
            $mymail->assign('promo', $promo);
            $mymail->assign('sex', $sex);
            $mymail->assign('birthdate', $birthdate);
            $mymail->assign('birthdate_ref', $birthdate_ref);
            $mymail->assign('forlife', $forlife);
            $mymail->assign('email', $email);
            $mymail->assign('logger', S::logger());
            if (count($market) > 0) {
                $mymail->assign('market', implode("\n", $market));
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
