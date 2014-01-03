<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

function bugize($list)
{
    $list = preg_split('/,/', $list, -1, PREG_SPLIT_NO_EMPTY);
    $ans  = array();

    foreach ($list as $bug) {
        $clean = str_replace('#', '', $bug);
        $ans[] = "<a href='http://trackers.polytechnique.org/task/$clean'>$bug</a>";
    }

    return join(',', $ans);
}


class PlatalModule extends PLModule
{
    function handlers()
    {
        return array(
            'index'             => $this->make_hook('index',        AUTH_PUBLIC),
            'cacert.pem'        => $this->make_hook('cacert',       AUTH_PUBLIC),
            'changelog'         => $this->make_hook('changelog',    AUTH_PUBLIC),

            // Preferences thingies
            'prefs'             => $this->make_hook('prefs',        AUTH_COOKIE, 'user,groups'),
            'prefs/rss'         => $this->make_hook('prefs_rss',    AUTH_COOKIE, 'user'),
            'prefs/webredirect' => $this->make_hook('webredir',     AUTH_PASSWD, 'mail'),
            'prefs/skin'        => $this->make_hook('skin',         AUTH_COOKIE, 'user'),
            'prefs/email'       => $this->make_hook('prefs_email',  AUTH_COOKIE, 'mail'),

            // password related thingies
            'password'          => $this->make_hook('password',     AUTH_PASSWD, 'user,groups'),
            'password/smtp'     => $this->make_hook('smtppass',     AUTH_PASSWD, 'mail'),
            'tmpPWD'            => $this->make_hook('tmpPWD',       AUTH_PUBLIC),
            'recovery'          => $this->make_hook('recovery',     AUTH_PUBLIC),
            'recovery/ext'      => $this->make_hook('recovery_ext', AUTH_PUBLIC),
            'register/ext'      => $this->make_hook('register_ext', AUTH_PUBLIC),
            'exit'              => $this->make_hook('exit',         AUTH_PUBLIC),
            'review'            => $this->make_hook('review',       AUTH_PUBLIC),
            'deconnexion.php'   => $this->make_hook('exit',         AUTH_PUBLIC),

            'error'             => $this->make_hook('test_error',   AUTH_COOKIE),
        );
    }

    function handler_index($page)
    {
        // Include X-XRDS-Location response-header for Yadis discovery
        global $globals;
        header('X-XRDS-Location: ' . $globals->baseurl . '/openid/xrds');

        // Redirect to the suitable page
        if (S::logged()) {
            pl_redirect('events');
        } else if (!@$GLOBALS['IS_XNET_SITE']) {
            $this->handler_review($page);
        }
    }

    function handler_cacert($page)
    {
        pl_cached_content_headers("application/x-x509-ca-cert");
        readfile("/etc/ssl/xorgCA/cacert.pem");
        exit;
    }

    function handler_changelog($page, $core = null)
    {
        $page->changeTpl('platal/changeLog.tpl');

        function formatChangeLog($file) {
            $clog = pl_entities(file_get_contents($file));
            $clog = preg_replace('/===+\s*/', '</pre><hr /><pre>', $clog);
            // url catch only (not all wiki syntax)
            $clog = preg_replace(array(
                '/((?:https?|ftp):\/\/(?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/ui',
                '/(\s|^)www\.((?:\.*,*[\w@~%$£µ&i#\-+=_\/\?;])*)/iu',
                '/(?:mailto:)?([a-z0-9.\-+_]+@([\-.+_]?[a-z0-9])+)/i'),
              array(
                '<a href="\\0">\\0</a>',
                '\\1<a href="http://www.\\2">www.\\2</a>',
                '<a href="mailto:\\0">\\0</a>'),
              $clog);
            $clog = preg_replace('!(#[0-9]+(,[0-9]+)*)!e', 'bugize("\1")', $clog);
            $clog = preg_replace('!vim:.*$!', '', $clog);
            return preg_replace("!(<hr />(\\s|\n)*)?<pre>(\s|\n)*</pre>((\\s|\n)*<hr />)?!m", "", "<pre>$clog</pre>");
        }
        if ($core != 'core') {
            $page->assign('core', false);
            $page->assign('ChangeLog', formatChangeLog(dirname(__FILE__).'/../ChangeLog'));
        } else {
            $page->assign('core', true);
            $page->assign('ChangeLog', formatChangeLog(dirname(__FILE__).'/../core/ChangeLog'));
        }
    }

    function __set_rss_state($state)
    {
        if ($state) {
            if (!S::user()->token) {
                S::user()->token = rand_url_id(16);
                S::set('token', S::user()->token);
                XDB::execute('UPDATE  accounts
                                 SET  token = {?}
                               WHERE  uid = {?}', S::user()->token, S::i('uid'));
            }
        } else {
            S::kill('token');
            S::user()->token = null;
            XDB::execute('UPDATE  accounts
                             SET  token = NULL
                           WHERE  uid = {?}', S::i('uid'));
        }
    }

    function handler_prefs($page)
    {
        $page->changeTpl('platal/preferences.tpl');
        $page->setTitle('Mes préférences');

        if (Post::has('email_format')) {
            S::assert_xsrf_token();
            $fmt = Post::s('email_format');
            S::user()->setEmailFormat($fmt);
        }

        if (Post::has('rss')) {
            S::assert_xsrf_token();
            $this->__set_rss_state(Post::s('rss') == 'on');
        }
    }

    function handler_webredir($page)
    {
        $page->changeTpl('platal/webredirect.tpl');
        $page->setTitle('Redirection de page WEB');

        if (Env::v('submit') == 'Valider' && !Env::blank('url')) {
            if (Env::blank('url')) {
                $page->trigError('URL invalide');
            } else {
                $url = Env::t('url');
                XDB::execute('INSERT INTO  carvas (uid, url)
                                   VALUES  ({?}, {?})
                  ON DUPLICATE KEY UPDATE  url = VALUES(url)',
                             S::i('uid'), $url);
                S::logger()->log('carva_add', 'http://' . $url);
                $page->trigSuccess("Redirection activée vers <a href='http://$url'>$url</a>");
            }
        } elseif (Env::v('submit') == 'Supprimer') {
            XDB::execute('DELETE FROM carvas
                                WHERE uid = {?}', S::i('uid'));
            Post::kill('url');
            S::logger()->log('carva_del');
            $page->trigSuccess('Redirection supprimée');
        }

        $url = XDB::fetchOneCell('SELECT  url
                                    FROM  carvas
                                   WHERE  uid = {?}', S::i('uid'));
        $page->assign('carva', $url);

        # FIXME: this code is not multi-domain compatible. We should decide how
        # carva will extend to users not in the main domain.
        $best = XDB::fetchOneCell('SELECT  email
                                     FROM  email_source_account
                                    WHERE  uid = {?} AND FIND_IN_SET(\'bestalias\', flags)',
                                  S::user()->id());
        $page->assign('bestalias', $best);
    }

    function handler_prefs_rss($page)
    {
        $page->changeTpl('platal/filrss.tpl');

        $page->assign('goback', Env::v('referer', 'login'));

        if (Env::v('act_rss') == 'Activer') {
            $this->__set_rss_state(true);
            $page->trigSuccess("Ton Fil RSS est activé.");
        }
    }

    function handler_prefs_email($page)
    {
        $page->changeTpl('platal/email_preferences.tpl');

        if (Post::has('submit')) {
            S::assert_xsrf_token();

            $from_email = Post::t('from_email');
            $from_format = Post::v('from_format');

            // Checks email.
            $email_regex = '/^[a-z0-9.\-+_\$]+@([\-.+_]?[a-z0-9])+$/i';
            if (!preg_match($email_regex, $from_email)) {
                $full_regex = '/^[^<]*<[a-z0-9.\-+_\$]+@([\-.+_]?[a-z0-9])+>$/i';
                if (!preg_match($full_regex, $from_email)) {
                    $page->trigError("L'adresse email est erronée.");
                    $error = true;
                    $page->assign('from_email', $from_email);
                    $page->assign('from_format', $from_format);
                    $page->assign('error', true);
                    return;
                }
            }

            // Saves data.
            XDB::execute('UPDATE  accounts
                             SET  from_email = {?}, from_format = {?}
                           WHERE  uid = {?}',
                         $from_email, ($from_format == 'html' ? 'html' : 'text'), S::user()->id());
            $page->trigSuccess('Données enregistrées.');
        }

        $data = XDB::fetchOneAssoc('SELECT  from_email, from_format
                                      FROM  accounts
                                     WHERE  uid = {?}',
                                   S::user()->id());
        $page->assign('from_email', $data['from_email']);
        $page->assign('from_format', $data['from_format']);
        $page->assign('error', false);
    }

    function handler_password($page)
    {
        global $globals;

        if (Post::has('pwhash') && Post::t('pwhash'))  {
            S::assert_xsrf_token();

            S::set('password', $password = Post::t('pwhash'));
            XDB::execute('UPDATE  accounts
                             SET  password = {?}
                           WHERE  uid={?}', $password,
                         S::i('uid'));

            // If GoogleApps is enabled, and the user did choose to use synchronized passwords,
            // updates the Google Apps password as well.
            if ($globals->mailstorage->googleapps_domain) {
                require_once 'googleapps.inc.php';
                $account = new GoogleAppsAccount(S::user());
                if ($account->active() && $account->sync_password) {
                    $account->set_password($password);
                }
            }

            S::logger()->log('passwd');
            Platal::session()->setAccessCookie(true);

            $page->changeTpl('platal/password.success.tpl');
            $page->run();
        }

        $page->changeTpl('platal/password.tpl');
        $page->setTitle('Mon mot de passe');
        $page->assign('do_auth', 0);
    }

    function handler_smtppass($page)
    {
        $page->changeTpl('platal/acces_smtp.tpl');
        $page->setTitle('Acces SMTP/NNTP');

        $wp = new PlWikiPage('Xorg.SMTPSécurisé');
        $wp->buildCache();
        $wp = new PlWikiPage('Xorg.NNTPSécurisé');
        $wp->buildCache();

        $uid  = S::i('uid');
        $pass = Env::v('smtppass1');

        if (Env::v('op') == "Valider" && strlen($pass) >= 6
            &&  Env::v('smtppass1') == Env::v('smtppass2')) {
            XDB::execute('UPDATE  accounts
                             SET  weak_password = {?}
                           WHERE  uid = {?}', $pass, $uid);
            $page->trigSuccess('Mot de passe enregistré');
            S::logger()->log("passwd_ssl");
        } elseif (Env::v('op') == "Supprimer") {
            XDB::execute('UPDATE  accounts
                             SET  weak_password = NULL
                           WHERE  uid = {?}', $uid);
            $page->trigSuccess('Compte SMTP et NNTP supprimé');
            S::logger()->log("passwd_del");
        }

        $res = XDB::query("SELECT  weak_password IS NOT NULL
                             FROM  accounts
                            WHERE  uid = {?}", $uid);
        $page->assign('actif', $res->fetchOneCell());
    }

    function handler_recovery($page)
    {
        global $globals;

        $page->changeTpl('platal/recovery.tpl');

        if (!Env::has('login') || !Env::has('birth')) {
            return;
        }

        if (!preg_match('/^[0-3][0-9][0-1][0-9][1][9]([0-9]{2})$/', Env::v('birth'))) {
            $page->trigError('Date de naissance incorrecte ou incohérente');
            return;
        }

        $birth   = sprintf('%s-%s-%s',
                           substr(Env::v('birth'), 4, 4),
                           substr(Env::v('birth'), 2, 2),
                           substr(Env::v('birth'), 0, 2));

        $mailorg = strtok(Env::v('login'), '@');

        $profile = Profile::get(Env::t('login'));
        if (is_null($profile) || $profile->birthdate != $birth) {
            $page->trigError('Les informations que tu as rentrées ne permettent pas de récupérer ton mot de passe.<br />'.
                        'Si tu as un homonyme, utilise prenom.nom.promo comme login');
            return;
        }

        $user = $profile->owner();
        if ($user->state != 'active') {
            $page->trigError('Ton compte n\'est pas activé.');
            return;
        }

        if ($user->lost) {
            $page->assign('no_addr', true);
            return;
        }

        $page->assign('ok', true);

        $url = rand_url_id();
        XDB::execute('INSERT INTO  account_lost_passwords (certificat,uid,created)
                           VALUES  ({?},{?},NOW())', $url, $user->id());
        $to = XDB::fetchOneCell('SELECT  redirect
                                   FROM  email_redirect_account
                                  WHERE  uid = {?} AND redirect = {?}',
                                $user->id(), Post::t('email'));
        if (is_null($to)) {
            $emails = XDB::fetchColumn('SELECT  redirect
                                          FROM  email_redirect_account
                                         WHERE  uid = {?} AND flags = \'inactive\' AND type = \'smtp\'',
                                       $user->id());
            $inactives_to = implode(', ', $emails);
        }
        $mymail = new PlMailer();
        $mymail->setFrom('"Gestion des mots de passe" <support+password@' . $globals->mail->domain . '>');
        if (is_null($to)) {
            $mymail->addTo($user);
            $log_to = $user->bestEmail();
            if (!is_null($inactives_to)) {
                $log_to = $inactives_to . ', ' . $log_to;
                $mymail->addTo($inactives_to);
            }
        } else {
            $mymail->addTo($to);
            $log_to = $to;
        }
        $mymail->setSubject("Ton certificat d'authentification");
        $mymail->setTxtBody("Visite la page suivante qui expire dans six heures :
{$globals->baseurl}/tmpPWD/$url

Si en cliquant dessus tu n'y arrives pas, copie intégralement l'adresse dans la barre de ton navigateur. Si tu n'as pas utilisé ce lien dans six heures, tu peux tout simplement recommencer cette procédure.

--
Polytechnique.org
\"Le portail des élèves & anciens élèves de l'École polytechnique\"

Email envoyé à ".Env::v('login') . (is_null($to) ? '' : '
Adresse de secours : ' . $to));
        $mymail->send();

        S::logger($user->id())->log('recovery', $log_to);
    }

    function handler_recovery_ext($page)
    {
        $page->changeTpl('xnet/recovery.tpl');

        if (!Post::has('login')) {
            return;
        }

        $user = User::getSilent(Post::t('login'));
        if (is_null($user)) {
            $page->trigError('Le compte n\'existe pas.');
            return;
        }
        if ($user->state != 'active') {
            $page->trigError('Ton compte n\'est pas activé.');
            return;
        }

        $page->assign('ok', true);

        $hash = rand_url_id();
        XDB::execute('INSERT INTO  account_lost_passwords (uid, created, certificat)
                           VALUES  ({?}, NOW(), {?})',
                     $user->id(), $hash);

        $mymail = new PlMailer('platal/password_recovery_xnet.mail.tpl');
        $mymail->setTo($user);
        $mymail->assign('hash', $hash);
        $mymail->assign('email', Post::t('login'));
        $mymail->send();

        S::logger($user->id())->log('recovery', $user->bestEmail());
    }

    function handler_tmpPWD($page, $certif = null)
    {
        global $globals;
        XDB::execute('DELETE FROM  account_lost_passwords
                            WHERE  DATE_SUB(NOW(), INTERVAL 380 MINUTE) > created');

        if (Post::has('pwhash') && Post::t('pwhash')) {
            $uid = XDB::fetchOneCell('SELECT  uid
                                        FROM  accounts
                                       WHERE  hruid = {?}',
                                     Post::t('username'));
            $password = Post::t('pwhash');
            XDB::query('UPDATE  accounts
                           SET  password = {?}
                         WHERE  uid = {?} AND state = \'active\'',
                       $password, $uid);
            XDB::query('DELETE FROM  account_lost_passwords
                              WHERE  certificat = {?}', $certif);

            // If GoogleApps is enabled, and the user did choose to use synchronized passwords,
            // updates the Google Apps password as well.
            if ($globals->mailstorage->googleapps_domain) {
                require_once 'googleapps.inc.php';
                $account = new GoogleAppsAccount(User::getSilent($uid));
                if ($account->active() && $account->sync_password) {
                    $account->set_password($password);
                }
            }

            S::logger($uid)->log("passwd", "");

            // Try to start a session (so the user don't have to log in); we will use
            // the password available in Post:: to authenticate the user.
            Platal::session()->start(AUTH_PASSWD);

            $page->changeTpl('platal/tmpPWD.success.tpl');
        } else {
            $res = XDB::query('SELECT  uid
                                 FROM  account_lost_passwords
                                WHERE  certificat = {?}', $certif);
            $ligne = $res->fetchOneAssoc();
            if (!$ligne) {
                $page->changeTpl('platal/index.tpl');
                $page->kill("Cette adresse n'existe pas ou n'existe plus sur le serveur.");
            }

            $hruid = XDB::fetchOneCell('SELECT  hruid
                                          FROM  accounts
                                         WHERE  uid = {?}',
                                       $ligne['uid']);
            $page->changeTpl('platal/password.tpl');
            $page->assign('hruid', $hruid);
            $page->assign('do_auth', 1);
        }
    }

    function handler_register_ext($page, $hash = null)
    {
        XDB::execute('DELETE FROM  register_pending_xnet
                            WHERE  DATE_SUB(NOW(), INTERVAL 1 MONTH) > date');
        $res = XDB::fetchOneAssoc('SELECT  uid, hruid, email
                                     FROM  register_pending_xnet
                                    WHERE  hash = {?}',
                                  $hash);

        if (is_null($hash) || is_null($res)) {
            $page->trigErrorRedirect('Cette adresse n\'existe pas ou n\'existe plus sur le serveur.', '');
        }

        if (Post::has('pwhash') && Post::t('pwhash')) {
            XDB::startTransaction();
            XDB::query('UPDATE  accounts
                           SET  password = {?}, state = \'active\', registration_date = NOW()
                         WHERE  uid = {?} AND state = \'pending\' AND type = \'xnet\'',
                       Post::t('pwhash'), $res['uid']);
            XDB::query('DELETE FROM  register_pending_xnet
                              WHERE  uid = {?}',
                              $res['uid']);
            XDB::commit();

            S::logger($res['uid'])->log('passwd', '');

            // Try to start a session (so the user don't have to log in); we will use
            // the password available in Post:: to authenticate the user.
            Post::kill('wait');
            Platal::session()->startAvailableAuth();

            $page->changeTpl('xnet/register.success.tpl');
            $page->assign('email', $res['email']);
        } else {
            $page->changeTpl('platal/password.tpl');
            $page->assign('xnet', true);
            $page->assign('hruid', $res['hruid']);
            $page->assign('do_auth', 1);
        }
    }

    function handler_skin($page)
    {
        global $globals;

        $page->changeTpl('platal/skins.tpl');
        $page->setTitle('Skins');

        if (Env::has('newskin'))  {  // formulaire soumis, traitons les données envoyées
            XDB::execute('UPDATE  accounts
                             SET  skin = {?}
                           WHERE  uid = {?}',
                         Env::i('newskin'), S::i('uid'));
            S::kill('skin');
            Platal::session()->setSkin();
        }

        $res = XDB::query('SELECT  id
                             FROM  skins
                            WHERE  skin_tpl = {?}', S::v('skin'));
        $page->assign('skin_id', $res->fetchOneCell());

        $sql = 'SELECT  s.*, auteur, COUNT(*) AS nb
                  FROM  skins AS s
             LEFT JOIN  accounts AS a ON (a.skin = s.id)
                 WHERE  skin_tpl != \'\' AND ext != \'\'
              GROUP BY  id ORDER BY s.date DESC';
        $page->assign('skins', XDB::iterator($sql));
    }

    function handler_exit($page, $level = null)
    {
        if (S::suid()) {
            $old = S::user()->login();
            S::logger()->log('suid_stop', $old . " by " . S::suid('hruid'));
            Platal::session()->stopSUID();
            $target = S::s('suid_startpage');
            S::kill('suid_startpage');
            if (!empty($target)) {
                http_redirect($target);
            }
            pl_redirect('admin/user/' . $old);
        }

        if ($level == 'forget' || $level == 'forgetall') {
            Platal::session()->killAccessCookie();
        }

        if ($level == 'forgetuid' || $level == 'forgetall') {
            Platal::session()->killLoginFormCookies();
        }

        if (S::logged()) {
            S::logger()->log('deconnexion', @$_SERVER['HTTP_REFERER']);
            Platal::session()->destroy();
        }

        if (Get::has('redirect')) {
            http_redirect(rawurldecode(Get::v('redirect')));
        } else {
            $page->changeTpl('platal/exit.tpl');
        }
    }

    function handler_review($page, $action = null, $mode = null)
    {
        // Include X-XRDS-Location response-header for Yadis discovery
        global $globals;
        header('X-XRDS-Location: ' . $globals->baseurl . '/openid/xrds');

        $this->load('review.inc.php');
        $dom = 'Review';
        if (@$GLOBALS['IS_XNET_SITE']) {
            $dom .= 'Xnet';
        }
        $wp = new PlWikiPage($dom . '.Admin');
        $conf = explode('%0a', $wp->getField('text'));
        $wiz = new PlWizard('Tour d\'horizon', PlPage::getCoreTpl('plwizard.tpl'), true);
        foreach ($conf as $line) {
            $list = preg_split('/\s*[*|]\s*/', $line, -1, PREG_SPLIT_NO_EMPTY);
            $wiz->addPage('ReviewPage', $list[0], $list[1]);
        }
        $wiz->apply($page, 'review', $action, $mode);
    }

    function handler_test_error($page, $mode = null)
    {
        if ($mode == 'js') {
            $page->changeTpl('platal/error.tpl');
        } else {
            throw new Exception("Blih");
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
