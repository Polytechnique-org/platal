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

function bugize($list)
{
    $list = split(',', $list);
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
            'index'       => $this->make_hook('index',     AUTH_PUBLIC),
            'cacert.pem'  => $this->make_hook('cacert',    AUTH_PUBLIC),
            'changelog'   => $this->make_hook('changelog', AUTH_PUBLIC),

            // Preferences thingies
            'prefs'       => $this->make_hook('prefs',     AUTH_COOKIE),
            'prefs/rss'   => $this->make_hook('prefs_rss', AUTH_COOKIE),
            'prefs/webredirect'
                          => $this->make_hook('webredir',  AUTH_MDP),
            'prefs/skin'  => $this->make_hook('skin',      AUTH_COOKIE),

            // password related thingies
            'password'      => $this->make_hook('password',  AUTH_MDP),
            'tmpPWD'        => $this->make_hook('tmpPWD',    AUTH_PUBLIC),
            'password/smtp' => $this->make_hook('smtppass',  AUTH_MDP),
            'recovery'      => $this->make_hook('recovery',  AUTH_PUBLIC),
            'exit'          => $this->make_hook('exit', AUTH_PUBLIC),
            'review'        => $this->make_hook('review', AUTH_PUBLIC),
            'deconnexion.php' => $this->make_hook('exit', AUTH_PUBLIC),
        );
    }

    function handler_index(&$page)
    {
        if (S::logged()) {
            pl_redirect('events');
        } else if (!@$GLOBALS['IS_XNET_SITE']) {
            pl_redirect('review');
        }
    }

    function handler_cacert(&$page)
    {
        $data = file_get_contents("/etc/ssl/xorgCA/cacert.pem","r");
        header("Pragma:");
        header("Set-Cookie:");
        header("Cache-Control:");
        header("Expires:");
        header("Content-Type: application/x-x509-ca-cert");
        header("Content-Length: ".strlen($data));
        echo $data;
        exit;
    }

    function handler_changelog(&$page)
    {
        $page->changeTpl('platal/changeLog.tpl');

        $clog = pl_entities(file_get_contents(dirname(__FILE__).'/../ChangeLog'));
        $clog = preg_replace('/=+\s*/', '</pre><hr /><pre>', $clog);
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
        $page->assign('ChangeLog', $clog);
    }

    function __set_rss_state($state)
    {
        if ($state) {
            $_SESSION['core_rss_hash'] = rand_url_id(16);
            XDB::execute('UPDATE  auth_user_quick
                                   SET  core_rss_hash={?} WHERE user_id={?}',
                                   S::v('core_rss_hash'), S::v('uid'));
        } else {
            XDB::execute('UPDATE  auth_user_quick
                                   SET  core_rss_hash="" WHERE user_id={?}',
                                   S::v('uid'));
            S::kill('core_rss_hash');
        }
    }

    function handler_prefs(&$page)
    {
        $page->changeTpl('platal/preferences.tpl');
        $page->assign('xorg_title','Polytechnique.org - Mes préférences');

        if (Post::has('mail_fmt')) {
            $fmt = Post::v('mail_fmt');
            if ($fmt != 'texte') $fmt = 'html';
            XDB::execute("UPDATE auth_user_quick
                                       SET core_mail_fmt = '$fmt'
                                     WHERE user_id = {?}",
                                     S::v('uid'));
            $_SESSION['mail_fmt'] = $fmt;
        }

        if (Post::has('rss')) {
            $this->__set_rss_state(Post::b('rss'));
        }
    }

    function handler_webredir(&$page)
    {
        $page->changeTpl('platal/webredirect.tpl');

        $page->assign('xorg_title','Polytechnique.org - Redirection de page WEB');

        $log =& S::v('log');
        $url = Env::v('url');

        if (Env::v('submit') == 'Valider' and Env::has('url')) {
            XDB::execute('UPDATE auth_user_quick
                                       SET redirecturl = {?} WHERE user_id = {?}',
                                   $url, S::v('uid'));
            $log->log('carva_add', 'http://'.Env::v('url'));
            $page->trig("Redirection activée vers <a href='http://$url'>$url</a>");
        } elseif (Env::v('submit') == "Supprimer") {
            XDB::execute("UPDATE auth_user_quick
                                       SET redirecturl = ''
                                     WHERE user_id = {?}",
                                   S::v('uid'));
            $log->log("carva_del", $url);
            Post::kill('url');
            $page->trig('Redirection supprimée');
        }

        $res = XDB::query('SELECT redirecturl
                                       FROM auth_user_quick
                                      WHERE user_id = {?}',
                                    S::v('uid'));
        $page->assign('carva', $res->fetchOneCell());
    }

    function handler_prefs_rss(&$page)
    {
        $page->changeTpl('platal/filrss.tpl');

        $page->assign('goback', Env::v('referer', 'login'));

        if (Env::v('act_rss') == 'Activer') {
            $this->__set_rss_state(true);
            $page->trig("Ton Fil RSS est activé.");
        }
    }

    function handler_password(&$page)
    {
        if (Post::has('response2'))  {
            require_once 'secure_hash.inc.php';

            $_SESSION['password'] = $password = Post::v('response2');

            XDB::execute('UPDATE  auth_user_md5
                             SET  password={?}
                           WHERE  user_id={?}', $password,
                           S::v('uid'));

            $log =& S::v('log');
            $log->log('passwd', '');

            if (Cookie::v('ORGaccess')) {
                setcookie('ORGaccess', hash_encrypt($password), (time()+25920000), '/', '' ,0);
            }

            $page->changeTpl('platal/motdepasse.success.tpl');
            $page->run();
        }

        $page->changeTpl('platal/motdepasse.tpl');
        $page->addJsLink('motdepasse.js');
        $page->assign('xorg_title','Polytechnique.org - Mon mot de passe');
    }

    function handler_smtppass(&$page)
    {
        $page->changeTpl('platal/acces_smtp.tpl');
        $page->assign('xorg_title','Polytechnique.org - Acces SMTP/NNTP');

        require_once 'wiki.inc.php';
        wiki_require_page('Xorg.SMTPSécurisé');
        wiki_require_page('Xorg.NNTPSécurisé');

        $uid  = S::v('uid');
        $pass = Env::v('smtppass1');
        $log  = S::v('log');

        if (Env::v('op') == "Valider" && strlen($pass) >= 6
        &&  Env::v('smtppass1') == Env::v('smtppass2'))
        {
            XDB::execute('UPDATE auth_user_md5 SET smtppass = {?}
                                     WHERE user_id = {?}', $pass, $uid);
            $page->trig('Mot de passe enregistré');
            $log->log("passwd_ssl");
        } elseif (Env::v('op') == "Supprimer") {
            XDB::execute('UPDATE auth_user_md5 SET smtppass = ""
                                     WHERE user_id = {?}', $uid);
            $page->trig('Compte SMTP et NNTP supprimé');
            $log->log("passwd_del");
        }

        $res = XDB::query("SELECT IF(smtppass != '', 'actif', '')
                                       FROM auth_user_md5
                                      WHERE user_id = {?}", $uid);
        $page->assign('actif', $res->fetchOneCell());
    }

    function handler_recovery(&$page)
    {
        global $globals;

        $page->changeTpl('platal/recovery.tpl');

        if (!Env::has('login') || !Env::has('birth')) {
            return;
        }

        if (!ereg('[0-3][0-9][0-1][0-9][1][9]([0-9]{2})', Env::v('birth'))) {
            $page->trig('Date de naissance incorrecte ou incohérente');
            return;
        }

        $birth   = sprintf('%s-%s-%s',
                           substr(Env::v('birth'), 4, 4),
                           substr(Env::v('birth'), 2, 2),
                           substr(Env::v('birth'), 0, 2));

        $mailorg = strtok(Env::v('login'), '@');

        // paragraphe rajouté : si la date de naissance dans la base n'existe pas, on l'update
        // avec celle fournie ici en espérant que c'est la bonne

        $res = XDB::query(
                "SELECT  user_id, naissance
                   FROM  auth_user_md5 AS u
             INNER JOIN  aliases       AS a ON (u.user_id=a.id AND type != 'homonyme')
                  WHERE  a.alias={?} AND u.perms IN ('admin','user') AND u.deces=0", $mailorg);
        list($uid, $naissance) = $res->fetchOneRow();

        if ($naissance == $birth) {
            $res = XDB::query("SELECT  COUNT(*)
                                 FROM  emails
                                WHERE  uid = {?} AND flags != 'panne' AND flags != 'filter'", $uid);
            $count = intval($res->fetchOneCell());
            if ($count == 0) {
                $page->assign('no_addr', true);
                return;
            }

            $page->assign('ok', true);

            $url   = rand_url_id();
            XDB::execute('INSERT INTO  perte_pass (certificat,uid,created)
                               VALUES  ({?},{?},NOW())', $url, $uid);
            $res   = XDB::query('SELECT  email
                                   FROM  emails
                                  WHERE  uid = {?} AND email = {?}',
                                $uid, Post::v('email'));
            if ($res->numRows()) {
                $mails = $res->fetchOneCell();
            } else {
                $res   = XDB::query('SELECT  email
                                       FROM  emails
                                      WHERE  uid = {?} AND NOT FIND_IN_SET("filter", flags)', $uid);
                $mails = implode(', ', $res->fetchColumn());
            }
            $mymail = new PlMailer();
            $mymail->setFrom('"Gestion des mots de passe" <support+password@' . $globals->mail->domain . '>');
            $mymail->addTo($mails);
            $mymail->setSubject('Ton certificat d\'authentification');
            $mymail->setTxtBody("Visite la page suivante qui expire dans six heures :
{$globals->baseurl}/tmpPWD/$url

Si en cliquant dessus tu n'y arrives pas, copie intégralement l'adresse dans la barre de ton navigateur. Si tu n'as pas utilisé ce lien dans six heures, tu peux tout simplement recommencer cette procédure.

--
Polytechnique.org
\"Le portail des élèves & anciens élèves de l'Ecole polytechnique\"

Mail envoyé à ".Env::v('login') . (Post::has('email') ? "
Adresse de secours : " . Post::v('email') : ""));
            $mymail->send();

            // on cree un objet logger et on log l'evenement
            $logger = $_SESSION['log'] = new CoreLogger($uid);
            $logger->log('recovery', $mails);
        } else {
            $page->trig('Les informations que tu as rentrées ne permettent pas de récupérer ton mot de passe.<br />'.
                        'Si tu as un homonyme, utilise prenom.nom.promo comme login');
        }
    }

    function handler_tmpPWD(&$page, $certif = null)
    {
        XDB::execute('DELETE FROM perte_pass
                                      WHERE DATE_SUB(NOW(), INTERVAL 380 MINUTE) > created');

        $res   = XDB::query('SELECT uid FROM perte_pass WHERE certificat={?}', $certif);
        $ligne = $res->fetchOneAssoc();
        if (!$ligne) {
            $page->changeTpl('platal/index.tpl');
            $page->kill("Cette adresse n'existe pas ou n'existe plus sur le serveur.");
        }

        $uid = $ligne["uid"];
        if (Post::has('response2')) {
            $password = Post::v('response2');
            $logger   = new CoreLogger($uid);
            XDB::query('UPDATE  auth_user_md5 SET password={?}
                                   WHERE  user_id={?} AND perms IN("admin","user")',
                                 $password, $uid);
            XDB::query('DELETE FROM perte_pass WHERE certificat={?}', $certif);
            $logger->log("passwd","");
            $page->changeTpl('platal/tmpPWD.success.tpl');
        } else {
            $page->changeTpl('platal/motdepasse.tpl');
            $page->addJsLink('motdepasse.js');
        }
    }

    function handler_skin(&$page)
    {
        global $globals;

        $page->changeTpl('platal/skins.tpl');
        $page->assign('xorg_title','Polytechnique.org - Skins');

        if (Env::has('newskin'))  {  // formulaire soumis, traitons les données envoyées
            XDB::execute('UPDATE auth_user_quick
                             SET skin={?} WHERE user_id={?}',
                         Env::i('newskin'), S::v('uid'));
            S::kill('skin');
            set_skin();
        }

        $res = XDB::query('SELECT id FROM skins WHERE skin_tpl={?}', S::v('skin'));
        $page->assign('skin_id', $res->fetchOneCell());

        $sql = "SELECT s.*,auteur,count(*) AS nb
                  FROM skins AS s
             LEFT JOIN auth_user_quick AS a ON s.id=a.skin
                 WHERE skin_tpl != '' AND ext != ''
              GROUP BY id ORDER BY s.date DESC";
        $page->assign('skins', XDB::iterator($sql));
    }

    function handler_exit(&$page, $level = null)
    {
        if (S::has('suid')) {
            $a4l  = S::v('forlife');
            $suid = S::v('suid');
            $log  = S::v('log');
            $log->log("suid_stop", S::v('forlife') . " by " . $suid['forlife']);
            $_SESSION = $suid;
            S::kill('suid');
            pl_redirect('admin/user/' . $a4l);
        }

        if ($level == 'forget' || $level == 'forgetall') {
            setcookie('ORGaccess', '', time() - 3600, '/', '', 0);
            Cookie::kill('ORGaccess');
            if (isset($_SESSION['log']))
                $_SESSION['log']->log("cookie_off");
        }

        if ($level == 'forgetuid' || $level == 'forgetall') {
            setcookie('ORGuid', '', time() - 3600, '/', '', 0);
            Cookie::kill('ORGuid');
            setcookie('ORGdomain', '', time() - 3600, '/', '', 0);
            Cookie::kill('ORGdomain');
        }

        if (isset($_SESSION['log'])) {
            $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $_SESSION['log']->log('deconnexion',$ref);
        }

        XorgSession::destroy();

        if (Get::has('redirect')) {
            http_redirect(rawurldecode(Get::v('redirect')));
        } else {
            $page->changeTpl('platal/exit.tpl');
        }
    }

    function handler_review(&$page, $action = null, $mode = null) 
    {
        require_once 'wiki.inc.php';
        require_once dirname(__FILE__) . '/platal/review.inc.php';
        $dir = wiki_work_dir();
        $dom = 'Review';
        if (@$GLOBALS['IS_XNET_SITE']) {
            $dom .= 'Xnet';
        }
        if (!is_dir($dir)) {
            $page->kill("Impossible de trouver le wiki");
        }
        if (!file_exists($dir . '/' . $dom . '.Admin')) {
            $page->kill("Impossible de trouver la page d'administration");
        }
        $conf = preg_grep('/^text=/', explode("\n", file_get_contents($dir . '/' . $dom . '.Admin')));
        $conf = preg_split('/(text\=|\%0a)/', array_shift($conf), -1, PREG_SPLIT_NO_EMPTY);
        $wiz = new PlWizard('Tour d\'horizon', 'core/plwizard.tpl', true);
        foreach ($conf as $line) {
            $list = preg_split('/\s*[*|]\s*/', $line, -1, PREG_SPLIT_NO_EMPTY);
            $wiz->addPage('ReviewPage', $list[0], $list[1]);
        }
        $wiz->apply($page, 'review', $action, $mode);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
