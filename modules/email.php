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

class EmailModule extends PLModule
{
    function handlers()
    {
        return array(
            'emails'                  => $this->make_hook('emails',      AUTH_COOKIE, 'mail'),
            'emails/alias'            => $this->make_hook('alias',       AUTH_MDP,    'mail'),
            'emails/antispam'         => $this->make_hook('antispam',    AUTH_MDP,    'mail'),
            'emails/broken'           => $this->make_hook('broken',      AUTH_COOKIE, 'user'),
            'emails/redirect'         => $this->make_hook('redirect',    AUTH_MDP,    'mail'),
            'emails/send'             => $this->make_hook('send',        AUTH_MDP,    'mail'),
            'emails/antispam/submit'  => $this->make_hook('submit',      AUTH_COOKIE, 'user'),
            'emails/test'             => $this->make_hook('test',        AUTH_COOKIE, 'mail', NO_AUTH),

            'emails/rewrite/in'       => $this->make_hook('rewrite_in',  AUTH_PUBLIC),
            'emails/rewrite/out'      => $this->make_hook('rewrite_out', AUTH_PUBLIC),

            'emails/imap/in'          => $this->make_hook('imap_in',     AUTH_PUBLIC),

            'admin/emails/duplicated' => $this->make_hook('duplicated',  AUTH_MDP,    'admin'),
            'admin/emails/watch'      => $this->make_hook('duplicated',  AUTH_MDP,    'admin'),
            'admin/emails/lost'       => $this->make_hook('lost',        AUTH_MDP,    'admin'),
            'admin/emails/broken'     => $this->make_hook('broken_addr', AUTH_MDP,    'admin'),
        );
    }

    function handler_emails($page, $action = null, $email = null)
    {
        global $globals;
        require_once 'emails.inc.php';

        $page->changeTpl('emails/index.tpl');
        $page->setTitle('Mes emails');

        $user = S::user();

        // Apply the bestalias change request.
        if ($action == 'best' && $email) {
            if (!S::has_xsrf_token()) {
                return PL_FORBIDDEN;
            }

            // First delete the bestalias flag from all this user's emails.
            XDB::execute("UPDATE  email_source_account
                             SET  flags = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', flags, ','), ',bestalias,', ','))
                           WHERE  uid = {?}", $user->id());
            // Then gives the bestalias flag to the given email.
            list($email, $domain) = explode('@', $email);
            XDB::execute("UPDATE  email_source_account  AS s
                      INNER JOIN  email_virtual_domains AS m ON (m.id = s.domain)
                      INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                             SET  flags = CONCAT_WS(',', IF(flags = '', NULL, flags), 'bestalias')
                           WHERE  s.uid = {?} AND s.email = {?} AND d.name = {?}",
                         $user->id(), $email, $domain);
            XDB::execute('UPDATE  accounts              AS a
                      INNER JOIN  email_virtual_domains AS d ON (d.name = {?})
                      INNER JOIN  email_virtual_domains AS m ON (d.aliasing = m.id)
                             SET  a.best_domain = d.id
                           WHERE  a.uid = {?} AND m.name = {?}',
                         $domain, $user->id(), $user->mainEmailDomain());

            // As having a non-null bestalias value is critical in
            // plat/al's code, we do an a posteriori check on the
            // validity of the bestalias.
            fix_bestalias($user);
            // Then refetch the user to update its bestalias.
            S::set('user', User::getWithUID(S::user()->id()));
        }

        // Fetch and display aliases.
        $aliases = XDB::iterator("SELECT  CONCAT(s.email, '@', d.name) AS email, (s.type = 'forlife') AS forlife,
                                          (s.email REGEXP '\\\\.[0-9]{2}$') AS hundred_year, s.expire,
                                          (FIND_IN_SET('bestalias', s.flags) AND a.best_domain = d.id) AS bestalias,
                                          ((s.type = 'alias_aux') AND d.aliasing = d.id) AS alias
                                    FROM  email_source_account  AS s
                              INNER JOIN  accounts              AS a ON (s.uid = a.uid)
                              INNER JOIN  email_virtual_domains AS m ON (s.domain = m.id)
                              INNER JOIN  email_virtual_domains AS d ON (d.aliasing = m.id)
                                   WHERE  s.uid = {?}
                                ORDER BY  !alias, s.email",
                                 $user->id());
        $page->assign('aliases', $aliases);

        $alias = XDB::fetchOneCell('SELECT  COUNT(email)
                                      FROM  email_source_account
                                     WHERE  uid = {?} AND type = \'alias_aux\'',
                                   $user->id());
        $page->assign('alias', $alias);


        // Check for homonyms.
        $page->assign('homonyme', $user->homonyme);

        // Display active redirections.
        $redirect = new Redirect($user);
        $page->assign('mails', $redirect->active_emails());

        // User's mail domains.
        $mail_domains = array($user->alternateEmailDomain());
        $mail_domains[] = User::$sub_mail_domains['all'] . $globals->mail->domain;
        $mail_domains[] = User::$sub_mail_domains['all'] . $globals->mail->domain2;
        $page->assign('main_email_domain', $user->mainEmailDomain());
        $page->assign('mail_domains', $mail_domains);
    }

    function handler_alias($page, $action = null, $value = null)
    {
        global $globals;

        $page->changeTpl('emails/alias.tpl');
        $page->setTitle('Alias melix.net');

        $user = S::user();
        $page->assign('request', AliasReq::get_request($user->id()));

        // Remove the email alias.
        if ($action == 'delete') {
            S::assert_xsrf_token();

            XDB::execute('DELETE FROM  email_source_account
                                WHERE  uid = {?} AND type = \'alias_aux\'',
                         $user->id());

            require_once 'emails.inc.php';
            fix_bestalias($user);
        }

        // Fetch existing auxiliary aliases.
        list($alias, $old_alias) = XDB::fetchOneRow('SELECT  CONCAT(s.email, \'@\', d.name), s.email
                                                       FROM  email_source_account  AS s
                                                 INNER JOIN  email_virtual_domains AS d ON (s.domain = d.id)
                                                      WHERE  s.uid = {?} AND s.type = \'alias_aux\'',
                                                    $user->id());
        $visibility = $user->hasProfile() && ($user->profile(true)->alias_pub == 'public');
        $page->assign('current', $alias);
        $page->assign('user', $user);
        $page->assign('mail_public', $visibility);

        if ($action == 'ask' && Env::has('alias') && Env::has('reason')) {
            S::assert_xsrf_token();

            // Retrieves user request.
            $new_alias  = Env::v('alias');
            $reason = Env::v('reason');
            $public = (Env::v('public', 'off') == 'on') ? 'public' : 'private';

            $page->assign('r_alias', $new_alias);
            $page->assign('r_reason', $reason);
            if ($public == 'public') {
                $page->assign('r_public', true);
            }

            // Checks special charaters in alias.
            if (!preg_match("/^[a-zA-Z0-9\-.]{3,20}$/", $new_alias)) {
                $page->trigError("L'adresse demandée n'est pas valide."
                            . " Vérifie qu'elle comporte entre 3 et 20 caractères"
                            . " et qu'elle ne contient que des lettres non accentuées,"
                            . " des chiffres ou les caractères - et .");
                return;
            } else {
                // Checks if the alias has already been given.
                $res = XDB::query('SELECT  COUNT(email)
                                     FROM  email_source_account
                                    WHERE  email = {?} AND type = \'alias_aux\'',
                                  $new_alias);
                if ($res->fetchOneCell() > 0) {
                    $page->trigError("L'alias $new_alias a déja été attribué. Tu ne peux donc pas l'obtenir.");
                    return;
                }

                // Checks if the alias has already been asked for.
                $it = Validate::iterate('alias');
                while($req = $it->next()) {
                    if ($req->alias == $new_alias) {
                        $page->trigError("L'alias $new_alias a déja été demandé. Tu ne peux donc pas l'obtenir pour l'instant.");
                        return;
                    }
                }

                // Sends requests. This will erase any previous alias pending request.
                $myalias = new AliasReq($user, $new_alias, $reason, $public, $old_alias);
                $myalias->submit();
                $page->assign('success', $new_alias);
                return;
            }
        } elseif ($action == 'set' && ($value == 'public' || $value == 'private')) {
            if (!S::has_xsrf_token()) {
                return PL_FORBIDDEN;
            }

            if ($user->hasProfile()) {
                XDB::execute('UPDATE  profiles
                                 SET  alias_pub = {?}
                               WHERE  pid = {?}',
                            $value, $user->profile()->id());
            }
            exit;
        }
    }

    function handler_redirect($page, $action = null, $email = null, $rewrite = null)
    {
        global $globals;
        require_once 'emails.inc.php';

        $page->changeTpl('emails/redirect.tpl');

        $user = S::user();
        $page->assign_by_ref('user', $user);
        $page->assign('eleve', $user->promo() >= date("Y") - 5);

        $redirect = new Redirect($user);

        // FS#703 : $_GET is urldecoded twice, hence
        // + (the data) => %2B (in the url) => + (first decoding) => ' ' (second decoding)
        // Since there can be no spaces in emails, we can fix this with :
        $email = str_replace(' ', '+', $email);

        // Apply email redirection change requests.
        $result = SUCCESS;
        if ($action == 'remove' && $email) {
            $result = $redirect->delete_email($email);
        }

        if ($action == 'active' && $email) {
            $redirect->modify_one_email($email, true);
        }

        if ($action == 'inactive' && $email) {
            $redirect->modify_one_email($email, false);
        }

        if ($action == 'rewrite' && $email) {
            $redirect->modify_one_email_redirect($email, $rewrite);
        }

        if (Env::has('emailop')) {
            S::assert_xsrf_token();

            $actifs = Env::v('emails_actifs', array());
            if (Env::v('emailop') == "ajouter" && Env::has('email')) {
                $error_email = false;
                $new_email = Env::v('email');
                if ($new_email == "new@example.org") {
                    $new_email = Env::v('email_new');
                }
                $result = $redirect->add_email($new_email);
                if ($result == ERROR_INVALID_EMAIL) {
                    $error_email = true;
                    $page->assign('email', $new_email);
                }
                $page->assign('retour', $result);
                $page->assign('error_email', $error_email);
            } elseif (empty($actifs)) {
                $result = ERROR_INACTIVE_REDIRECTION;
            } elseif (is_array($actifs)) {
                $result = $redirect->modify_email($actifs, Env::v('emails_rewrite', array()));
            }
        }

        switch ($result) {
          case ERROR_INACTIVE_REDIRECTION:
            $page->trigError('Tu ne peux pas avoir aucune adresse de redirection active, sinon ton adresse '
                             . $user->forlifeEmail() . ' ne fonctionnerait plus.');
            break;
          case ERROR_INVALID_EMAIL:
            $page->trigError('Erreur : l\'email n\'est pas valide.');
            break;
          case ERROR_LOOP_EMAIL:
            $page->trigError('Erreur : ' . $user->forlifeEmail()
                             . ' ne doit pas être renvoyé vers lui-même, ni vers son équivalent en '
                             . $globals->mail->domain2 . ' ni vers polytechnique.edu.');
            break;
        }
        // Fetch existing email aliases.
        $alias = XDB::query('SELECT  CONCAT(s.email, \'@\', d.name) AS email, s.expire
                               FROM  email_source_account  AS s
                         INNER JOIN  email_virtual_domains AS m ON (s.domain = m.id)
                         INNER JOIN  email_virtual_domains AS d ON (m.id = d.aliasing)
                              WHERE  s.uid = {?}
                           ORDER BY  NOT(s.type = \'alias_aux\'), s.email, d.name',
                            $user->id());
        $page->assign('alias', $alias->fetchAllAssoc());
        $page->assign('best_email', $user->bestEmail());

        $page->assign('emails', $redirect->emails);

        // Display GoogleApps acount information.
        require_once 'googleapps.inc.php';
        $page->assign('googleapps', GoogleAppsAccount::account_status($user->id()));

        require_once 'emails.combobox.inc.php';
        fill_email_combobox($page, array('job', 'stripped_directory'));
    }

    function handler_antispam($page, $filter_status = null, $redirection = null)
    {
        require_once 'emails.inc.php';
        $wp = new PlWikiPage('Xorg.Antispam');
        $wp->buildCache();

        $page->changeTpl('emails/antispam.tpl');

        $user = S::user();
        $bogo = new Bogo($user);
        if (!is_null($filter_status)) {
            if (is_null($redirection)) {
                $bogo->changeAll($filter_status);
            } else {
                $bogo->change($redirection, $filter_status);
            }
        }
        $page->assign('filter', $bogo->state);
        $page->assign('single_state', $bogo->single_state);
        $page->assign('single_redirection', $bogo->single_redirection);
        $page->assign('redirections', $bogo->redirections);
    }

    function handler_submit($page)
    {
        $wp = new PlWikiPage('Xorg.Mails');
        $wp->buildCache();
        $page->changeTpl('emails/submit_spam.tpl');

        if (Post::has('send_email')) {
            S::assert_xsrf_token();

            $upload = PlUpload::get($_FILES['mail'], S::user()->login(), 'spam.submit', true);
            if (!$upload) {
                $page->trigError('Une erreur a été rencontrée lors du transfert du fichier');
                return;
            }
            $mime = $upload->contentType();
            if ($mime != 'text/x-mail' && $mime != 'message/rfc822') {
                $upload->clear();
                $page->trigError('Le fichier ne contient pas un email complet');
                return;
            }
            $type = (Post::v('type') == 'spam' ? 'spam' : 'nonspam');

            global $globals;
            $box    = $type . '@' . $globals->mail->domain;
            $mailer = new PlMailer();
            $mailer->addTo($box);
            $mailer->setFrom('"' . S::user()->fullName() . '" <web@' . $globals->mail->domain . '>');
            $mailer->setTxtBody($type . ' soumis par ' . S::user()->login() . ' via le web');
            $mailer->addUploadAttachment($upload, $type . '.mail');
            $mailer->send();
            $page->trigSuccess('Le message a été transmis à ' . $box);
            $upload->clear();
        }
    }

    function handler_send($page)
    {
        $page->changeTpl('emails/send.tpl');

        $page->setTitle('Envoyer un email');

        // action si on recoit un formulaire
        if (Post::has('save')) {
            if (!S::has_xsrf_token()) {
                return PL_FORBIDDEN;
            }

            unset($_POST['save']);
            if (trim(preg_replace('/-- .*/', '', Post::v('contenu'))) != "") {
                Post::set('to_contacts', explode(';', Post::s('to_contacts')));
                Post::set('cc_contacts', explode(';', Post::s('cc_contacts')));
                $data = serialize($_POST);
                XDB::execute('INSERT INTO  email_send_save (uid, data)
                                   VALUES  ({?}, {?})
                  ON DUPLICATE KEY UPDATE  data = VALUES(data)',
                             S::user()->id('uid'), $data);
            }
            exit;
        } else if (Env::v('submit') == 'Envoyer') {
            S::assert_xsrf_token();

            function getEmails($aliases)
            {
                if (!is_array($aliases)) {
                    return null;
                }
                $uf = new UserFilter(new UFC_Hrpid($aliases));
                $users = $uf->iterUsers();
                $ret = array();
                while ($user = $users->next()) {
                    $ret[] = $user->forlife;
                }
                return join(', ', $ret);
            }

            $error = false;
            foreach ($_FILES as &$file) {
                if ($file['name'] && !PlUpload::get($file, S::user()->login(), 'emails.send', false)) {
                    $page->trigError(PlUpload::$lastError);
                    $error = true;
                    break;
                }
            }

            if (!$error) {
                XDB::execute("DELETE FROM  email_send_save
                                    WHERE  uid = {?}",
                             S::user()->id());

                $to2  = getEmails(Env::v('to_contacts'));
                $cc2  = getEmails(Env::v('cc_contacts'));
                $txt  = str_replace('^M', '', Env::v('contenu'));
                $to   = str_replace(';', ',', Env::t('to'));
                $subj = Env::t('sujet');
                $from = Env::t('from');
                $cc   = str_replace(';', ',', Env::t('cc'));
                $bcc  = str_replace(';', ',', Env::t('bcc'));

                $email_regex = '/^[a-z0-9.\-+_\$]+@([\-.+_]?[a-z0-9])+$/i';
                foreach (explode(',', $to . ',' . $cc . ',' . $bcc) as $email) {
                    $email = trim($email);
                    if ($email != '' && !preg_match($email_regex, $email)) {
                        $page->trigError("L'adresse email " . $email  . ' est erronée.');
                        $error = true;
                    }
                }
                if (empty($to) && empty($cc) && empty($to2) && empty($bcc) && empty($cc2)) {
                    $page->trigError("Indique au moins un destinataire.");
                    $error = true;
                }

                if ($error) {
                    $page->assign('uploaded_f', PlUpload::listFilenames(S::user()->login(), 'emails.send'));
                } else {
                    $mymail = new PlMailer();
                    $mymail->setFrom($from);
                    $mymail->setSubject($subj);
                    if (!empty($to))  { $mymail->addTo($to); }
                    if (!empty($cc))  { $mymail->addCc($cc); }
                    if (!empty($bcc)) { $mymail->addBcc($bcc); }
                    if (!empty($to2)) { $mymail->addTo($to2); }
                    if (!empty($cc2)) { $mymail->addCc($cc2); }
                    $files =& PlUpload::listFiles(S::user()->login(), 'emails.send');
                    foreach ($files as $name=>&$upload) {
                        $mymail->addUploadAttachment($upload, $name);
                    }
                    if (Env::v('nowiki')) {
                        $mymail->setTxtBody(wordwrap($txt, 78, "\n"));
                    } else {
                        $mymail->setWikiBody($txt);
                    }
                    if ($mymail->send()) {
                        $page->trigSuccess("Ton email a bien été envoyé.");
                        $_REQUEST = array('bcc' => S::user()->bestEmail());
                        PlUpload::clear(S::user()->login(), 'emails.send');
                    } else {
                        $page->trigError("Erreur lors de l'envoi du courriel, réessaye.");
                        $page->assign('uploaded_f', PlUpload::listFilenames(S::user()->login(), 'emails.send'));
                    }
                }
            }
        } else {
            $res = XDB::query("SELECT  data
                                 FROM  email_send_save
                                WHERE  uid = {?}", S::i('uid'));
            if ($res->numRows() == 0) {
                PlUpload::clear(S::user()->login(), 'emails.send');
                $_REQUEST['bcc'] = S::user()->bestEmail();
            } else {
                $data = unserialize($res->fetchOneCell());
                $_REQUEST = array_merge($_REQUEST, $data);
            }
        }

        $uf = new UserFilter(new PFC_And(new UFC_Contact(S::user()),
                                         new UFC_Registered()),
                             UserFilter::sortByName());
        $contacts = $uf->getProfiles();
        $page->assign('contacts', $contacts);
        $page->assign('maxsize', ini_get('upload_max_filesize') . 'o');
        $page->assign('user', S::user());
    }

    function handler_test($page, $hruid = null)
    {
        require_once 'emails.inc.php';

        if (!S::has_xsrf_token()) {
            return PL_FORBIDDEN;
        }

        // Retrieves the User object for the test email recipient.
        if (S::admin() && $hruid) {
            $user = User::getSilent($hruid);
        } else {
            $user = S::user();
        }
        if (!$user) {
            return PL_NOT_FOUND;
        }

        // Sends the test email.
        $redirect = new Redirect($user);

        $mailer = new PlMailer('emails/test.mail.tpl');
        $mailer->assign('email', $user->bestEmail());
        $mailer->assign('redirects', $redirect->active_emails());
        $mailer->assign('display_name', $user->displayName());
        $mailer->assign('sexe', $user->isFemale());
        $mailer->send($user->isEmailFormatHtml());
        exit;
    }

    function handler_rewrite_in($page, $mail, $hash)
    {
        $page->changeTpl('emails/rewrite.tpl');
        $page->assign('option', 'in');
        if (empty($mail) || empty($hash)) {
            return PL_NOT_FOUND;
        }
        $pos = strrpos($mail, '_');
        if ($pos === false) {
            return PL_NOT_FOUND;
        }
        $mail{$pos} = '@';
        $res = XDB::query('SELECT  COUNT(*)
                             FROM  email_redirect_account
                            WHERE  redirect = {?} AND hash = {?} AND type = \'smtp\'',
                          $mail, $hash);
        $count = intval($res->fetchOneCell());
        if ($count > 0) {
            XDB::query('UPDATE  email_redirect_account
                           SET  allow_rewrite = true, hash = NULL
                         WHERE  redirect = {?} AND hash = {?} AND type = \'smtp\'',
                       $mail, $hash);
            $page->trigSuccess("Réécriture activée pour l'adresse " . $mail);
            return;
        }
        return PL_NOT_FOUND;
    }

    function handler_rewrite_out($page, $mail, $hash)
    {
        $page->changeTpl('emails/rewrite.tpl');
        $page->assign('option', 'out');
        if (empty($mail) || empty($hash)) {
            return PL_NOT_FOUND;
        }
        $pos = strrpos($mail, '_');
        if ($pos === false) {
            return PL_NOT_FOUND;
        }
        $mail{$pos} = '@';
        $res = XDB::query('SELECT  COUNT(*)
                             FROM  email_redirect_account
                            WHERE  redirect = {?} AND hash = {?} AND type = \'smtp\'',
                          $mail, $hash);
        $count = intval($res->fetchOneCell());
        if ($count > 0) {
            global $globals;
            $res = XDB::query('SELECT  e.redirect, e.rewrite, a.hruid
                                 FROM  email_redirect_account AS e
                           INNER JOIN  accounts               AS a ON (e.uid = a.uid)
                                WHERE  e.redirect = {?} AND e.hash = {?}',
                              $mail, $hash);
            XDB::query('UPDATE  email_redirect_account
                           SET  allow_rewrite = false, hash = NULL
                         WHERE  redirect = {?} AND hash = {?}',
                       $mail, $hash);
            list($mail, $rewrite, $hruid) = $res->fetchOneRow();
            $mail = new PlMailer();
            $mail->setFrom("webmaster@" . $globals->mail->domain);
            $mail->addTo("support@" .  $globals->mail->domain);
            $mail->setSubject("Tentative de détournement de correspondance via le rewrite");
            $mail->setTxtBody("$hruid a tenté un rewrite de $mail vers $rewrite. Cette demande a été rejetée via le web");
            $mail->send();
            $page->trigWarning("Un mail d'alerte a été envoyé à l'équipe de " . $globals->core->sitename);
            return;
        }
        return PL_NOT_FOUND;
    }

    function handler_imap_in($page, $hash = null, $login = null)
    {
        $page->changeTpl('emails/imap_register.tpl');
        $user = null;
        if (!empty($hash) || !empty($login)) {
            $user = User::getSilent($login);
            if ($user) {
                $req = XDB::query('SELECT  1
                                     FROM  newsletter_ins
                                    WHERE  uid = {?} AND hash = {?}',
                                  $user->id(), $hash);
                if ($req->numRows() == 0) {
                    $user = null;
                }
            }
        }

        require_once 'emails.inc.php';
        $page->assign('ok', false);
        if (S::logged() && (is_null($user) || $user->id() == S::i('uid'))) {
            Email::activate_storage(S::user(), 'imap', Bogo::IMAP_DEFAULT);
            $page->assign('ok', true);
            $page->assign('yourself', S::user()->displayName());
            $page->assign('sexe', S::user()->isFemale());
        } else if (!S::logged() && $user) {
            Email::activate_storage($user, 'imap', Bogo::IMAP_DEFAULT);
            $page->assign('ok', true);
            $page->assign('yourself', $user->displayName());
            $page->assign('sexe', $user->isFemale());
        }
    }

    function handler_broken($page, $warn = null, $email = null)
    {
        require_once 'emails.inc.php';
        $wp = new PlWikiPage('Xorg.PatteCassée');
        $wp->buildCache();

        global $globals;

        $page->changeTpl('emails/broken.tpl');

        if ($warn == 'warn' && $email) {
            S::assert_xsrf_token();

            // Usual verifications.
            $email = valide_email($email);
            $uid = XDB::fetchOneCell('SELECT  uid
                                        FROM  email_redirect_account
                                       WHERE  redirect = {?}', $email);

            if ($uid) {
                $dest = User::getWithUID($uid);

                $mail = new PlMailer('emails/broken-web.mail.tpl');
                $mail->assign('email', $email);
                $mail->assign('request', S::user());
                $mail->sendTo($dest);
                $page->trigSuccess('Email envoyé&nbsp;!');
            }
        } elseif (Post::has('email')) {
            S::assert_xsrf_token();

            $email = Post::t('email');

            if (!User::isForeignEmailAddress($email)) {
                $page->assign('neuneu', true);
            } else {
                $user = mark_broken_email($email);
                $page->assign('user', $user);
                $page->assign('email', $email);
            }
        }
    }

    function handler_duplicated($page, $action = 'list', $email = null)
    {
        $page->changeTpl('emails/duplicated.tpl');

        $states = array('pending'   => 'En attente...',
                        'safe'      => 'Pas d\'inquiétude',
                        'unsafe'    => 'Recherches en cours',
                        'dangerous' => 'Usurpations par cette adresse');
        $page->assign('states', $states);

        if (Post::has('action')) {
            S::assert_xsrf_token();
        }
        switch (Post::v('action')) {
          case 'create':
            if (trim(Post::v('emailN')) != '') {
                Xdb::execute('INSERT IGNORE INTO email_watch (email, state, detection, last, uid, description)
                                          VALUES ({?}, {?}, CURDATE(), NOW(), {?}, {?})',
                             trim(Post::v('emailN')), Post::v('stateN'), S::i('uid'), Post::v('descriptionN'));
            };
            break;

          case 'edit':
            Xdb::execute('UPDATE email_watch
                             SET state = {?}, last = NOW(), uid = {?}, description = {?}
                           WHERE email = {?}', Post::v('stateN'), S::i('uid'), Post::v('descriptionN'), Post::v('emailN'));
            break;

          default:
            if ($action == 'delete' && !is_null($email)) {
                Xdb::execute('DELETE FROM email_watch WHERE email = {?}', $email);
            }
        }
        if ($action != 'create' && $action != 'edit') {
            $action = 'list';
        }
        $page->assign('action', $action);

        if ($action == 'list') {
            $it = XDB::iterRow('SELECT  w.email, w.detection, w.state, s.email AS forlife
                                  FROM  email_watch            AS w
                            INNER JOIN  email_redirect_account AS r ON (w.email = r.redirect)
                            INNER JOIN  email_source_account   AS s ON (s.uid = r.uid AND s.type = \'forlife\')
                              ORDER BY  w.state, w.email, s.email');

            $table = array();
            $props = array();
            while (list($email, $date, $state, $forlife) = $it->next()) {
                if (count($props) == 0 || $props['mail'] != $email) {
                    if (count($props) > 0) {
                        $table[] = $props;
                    }
                    $props = array('mail' => $email,
                                   'detection' => $date,
                                   'state' => $state,
                                   'users' => array($forlife));
                } else {
                    $props['users'][] = $forlife;
                }
            }
            if (count($props) > 0) {
                $table[] = $props;
            }
            $page->assign('table', $table);
        } elseif ($action == 'edit') {
            $it = XDB::iterRow('SELECT  w.detection, w.state, w.last, w.description,
                                        a.hruid AS edit, s.email AS forlife
                                  FROM  email_watch            AS w
                            INNER JOIN  email_redirect_account AS r ON (w.email = r.redirect)
                            INNER JOIN  email_source_account   AS s ON (s.uid = r.uid AND s.type = \'forlife\')
                             LEFT JOIN  accounts               AS a ON (w.uid = a.uid)
                                 WHERE  w.email = {?}
                              ORDER BY  s.email',
                               $email);

            $props = array();
            while (list($detection, $state, $last, $description, $edit, $forlife) = $it->next()) {
                if (count($props) == 0) {
                    $props = array('mail'        => $email,
                                   'detection'   => $detection,
                                   'state'       => $state,
                                   'last'        => $last,
                                   'description' => $description,
                                   'edit'        => $edit,
                                   'users'       => array($forlife));
                } else {
                    $props['users'][] = $forlife;
                }
            }
            $page->assign('doublon', $props);
        }
    }

    function handler_lost($page, $action = 'list', $email = null)
    {
        $page->changeTpl('emails/lost.tpl');

        $page->assign('lost_emails',
                      XDB::iterator('SELECT  a.uid, a.hruid, pd.promo
                                       FROM  accounts               AS a
                                 INNER JOIN  account_types          AS at ON (a.type = at.type)
                                  LEFT JOIN  email_redirect_account AS er ON (er.uid = a.uid AND er.flags = \'active\' AND er.broken_level < 3
                                                                              AND er.type != \'imap\' AND er.type != \'homonym\')
                                  LEFT JOIN  account_profiles       AS ap ON (ap.uid = a.uid AND FIND_IN_SET(\'owner\', ap.perms))
                                  LEFT JOIN  profile_display        AS pd ON (ap.pid = pd.pid)
                                      WHERE  a.state = \'active\' AND er.redirect IS NULL AND FIND_IN_SET(\'mail\', at.perms)
                                   GROUP BY  a.uid
                                   ORDER BY  pd.promo, a.hruid'));
    }

    function handler_broken_addr($page)
    {
        require_once 'emails.inc.php';
        $page->changeTpl('emails/broken_addr.tpl');

        if (Env::has('sort_broken')) {
            S::assert_xsrf_token();

            $list = trim(Env::v('list'));
            if ($list == '') {
                $page->trigError('La liste est vide.');
            } else {
                $valid_emails = array();
                $invalid_emails = array();
                $broken_list = explode("\n", $list);
                sort($broken_list);
                foreach ($broken_list as $orig_email) {
                    $orig_email = trim($orig_email);
                    if ($orig_email != '') {
                        $email = valide_email($orig_email);
                        if (empty($email) || $email == '@') {
                            $invalid_emails[] = trim($orig_email) . ': invalid email';
                        } elseif (!in_array($email, $valid_emails)) {
                            $nb = XDB::fetchOneCell('SELECT  COUNT(*)
                                                       FROM  email_redirect_account
                                                      WHERE  redirect = {?}', $email);
                            if ($nb > 0) {
                                $valid_emails[] = $email;
                            } else {
                                $invalid_emails[] = $orig_email . ': no such redirection';
                            }
                        }
                    }
                }

                $page->assign('valid_emails', $valid_emails);
                $page->assign('invalid_emails', $invalid_emails);
            }
        }

        if (Env::has('process_broken')) {
            S::assert_xsrf_token();

            $list = trim(Env::v('list'));
            if ($list == '') {
                $page->trigError('La liste est vide.');
            } else {
                require_once 'notifs.inc.php';

                $broken_user_list = array();
                $broken_user_email_count = array();
                $broken_list = explode("\n", $list);
                sort($broken_list);

                foreach ($broken_list as $email) {
                    if ($user = mark_broken_email($email, true)) {
                        if ($user['nb_mails'] > 0 && $user['notify']) {
                            $mail = new PlMailer('emails/broken.mail.tpl');
                            $dest = User::getSilentWithUID($user['uid']);
                            $mail->setTo($dest);
                            $mail->assign('user', $user);
                            $mail->assign('email', $email);
                            $mail->send();
                        } else {
                            $profile = Profile::get($user['alias']);
                            WatchProfileUpdate::register($profile, 'broken');
                        }

                        if (!isset($broken_user_list[$user['uid']])) {
                            $broken_user_list[$user['uid']] = array($email);
                        } else {
                            $broken_user_list[$user['uid']][] = $email;
                        }
                        $broken_user_email_count[$user['uid']] = $user['nb_mails'];
                    }
                }

                XDB::execute('UPDATE  email_redirect_account
                                 SET  broken_level = broken_level - 1
                               WHERE  flags = \'active\' AND broken_level > 1
                                      AND DATE_ADD(last, INTERVAL 1 MONTH) < CURDATE()');
                XDB::execute('UPDATE  email_redirect_account
                                 SET  broken_level = 0
                               WHERE  flags = \'active\' AND broken_level = 1
                                      AND DATE_ADD(last, INTERVAL 1 YEAR) < CURDATE()');

                // Output the list of users with recently broken addresses,
                // along with the count of valid redirections.
                pl_cached_content_headers('text/x-csv', 1);

                $csv = fopen('php://output', 'w');
                fputcsv($csv, array('nom', 'promo', 'bounces', 'nbmails', 'url', 'corps', 'job', 'networking'), ';');
                $corpsList = DirEnum::getOptions(DirEnum::CURRENTCORPS);
                foreach ($broken_user_list as $uid => $mails) {
                    $profile = Profile::get($uid);
                    $corps = $profile->getCorps();
                    $current_corps = ($corps && $corps->current) ? $corpsList[$corps->current] : '';
                    $jobs = $profile->getJobs();
                    $companies = array();
                    foreach ($jobs as $job) {
                        $companies[] = $job->company->name;
                    }
                    $networkings = $profile->getNetworking(Profile::NETWORKING_ALL);
                    $networking_list = array();
                    foreach ($networkings as $networking) {
                        $networking_list[] = $networking['address'];
                    }
                    fputcsv($csv, array($profile->fullName(), $profile->promo(),
                                        join(',', $mails), $broken_user_email_count[$uid],
                                        'https://www.polytechnique.org/marketing/broken/' . $profile->hrid(),
                                        $current_corps, implode(',', $companies), implode(',', $networking_list)), ';');
                }
                fclose($csv);
                exit;
            }
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
