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
            'prefs'     => $this->make_hook('prefs',     AUTH_COOKIE),
            'prefs/rss' => $this->make_hook('prefs_rss', AUTH_COOKIE),
            'prefs/webredirect'
                        => $this->make_hook('webredir',  AUTH_MDP),
            'skin'      => $this->make_hook('skin',      AUTH_COOKIE),

            // password related thingies
            'password'      => $this->make_hook('password',  AUTH_MDP),
            'tmpPWD'        => $this->make_hook('tmpPWD',    AUTH_PUBLIC),
            'password/smtp' => $this->make_hook('smtppass',  AUTH_MDP),
            'recovery'      => $this->make_hook('recovery',  AUTH_PUBLIC),
            'exit'          => $this->make_hook('exit', AUTH_PUBLIC),

            // happenings related thingies
            'rss'         => $this->make_hook('rss',       AUTH_PUBLIC),
        );
    }

    function handler_index(&$page)
    {
        if (logged()) {
            redirect("events");
        }
    }

    function handler_cacert(&$page)
    {
        $data = file_get_contents('/etc/ssl/xorgCA/cacert.pem');
        header('Content-Type: application/x-x509-ca-cert');
        header('Content-Length: '.strlen($data));
        echo $data;
        exit;
    }

    function handler_changelog(&$page)
    {
        $page->changeTpl('changeLog.tpl');

        $clog = htmlentities(file_get_contents(dirname(__FILE__).'/../ChangeLog'));
        $clog = preg_replace('!(#[0-9]+(,[0-9]+)*)!e', 'bugize("\1")', $clog);
        $page->assign('ChangeLog', $clog);
    }

    function __set_rss_state($state)
    {
        global $globals;

        if ($state) {
            $_SESSION['core_rss_hash'] = rand_url_id(16);
            $globals->xdb->execute('UPDATE  auth_user_quick
                                   SET  core_rss_hash={?} WHERE user_id={?}',
                                   Session::get('core_rss_hash'),
                                   Session::getInt('uid'));
        } else {
            $globals->xdb->execute('UPDATE  auth_user_quick
                                   SET  core_rss_hash="" WHERE user_id={?}',
                                   Session::getInt('uid'));
            Session::kill('core_rss_hash');
        }
    }

    function handler_prefs(&$page)
    {
        global $globals;

        $page->changeTpl('preferences.tpl');
        $page->assign('xorg_title','Polytechnique.org - Mes préférences');

        if (Env::has('mail_fmt')) {
            $fmt = Env::get('mail_fmt');
            if ($fmt != 'texte') $fmt = 'html';
            $globals->xdb->execute("UPDATE auth_user_quick
                                       SET core_mail_fmt = '$fmt'
                                     WHERE user_id = {?}",
                                     Session::getInt('uid'));
            $_SESSION['mail_fmt'] = $fmt;
            redirect($globals->baseurl.'/preferences');
        }

        if (Env::has('rss')) {
            $this->__set_rss_state(Env::getBool('rss'));
        }

        $page->assign('prefs', $globals->hook->prefs());
    }

    function handler_webredir(&$page)
    {
        global $globals;

        $page->changeTpl('webredirect.tpl');

        $page->assign('xorg_title','Polytechnique.org - Redirection de page WEB');

        $log =& Session::getMixed('log');
        $url = Env::get('url');

        if (Env::get('submit') == 'Valider' and Env::has('url')) {
            $globals->xdb->execute('UPDATE auth_user_quick
                                       SET redirecturl = {?} WHERE user_id = {?}',
                                   $url, Session::getInt('uid'));
            $log->log('carva_add', 'http://'.Env::get('url'));
            $page->trig("Redirection activée vers <a href='http://$url'>$url</a>");
        } elseif (Env::get('submit') == "Supprimer") {
            $globals->xdb->execute("UPDATE auth_user_quick
                                       SET redirecturl = ''
                                     WHERE user_id = {?}",
                                   Session::getInt('uid'));
            $log->log("carva_del", $url);
            Post::kill('url');
            $page->trig('Redirection supprimée');
        }

        $res = $globals->xdb->query('SELECT redirecturl
                                       FROM auth_user_quick
                                      WHERE user_id = {?}',
                                    Session::getInt('uid'));
        $page->assign('carva', $res->fetchOneCell());
    }

    function handler_prefs_rss(&$page)
    {
        global $globals;

        $page->changeTpl('filrss.tpl');

        $page->assign('goback', Env::get('referer', 'login'));

        if (Env::get('act_rss') == 'Activer') {
            $this->__set_rss_state(true);
            $page->trig("Ton Fil RSS est activé.");
        }
    }

    function handler_password(&$page)
    {
        global $globals;

        if (Post::has('response2'))  {
            require_once 'secure_hash.inc.php';

            $_SESSION['password'] = $password = Post::get('response2');

            $globals->xdb->execute('UPDATE  auth_user_md5 
                                       SET  password={?}
                                     WHERE  user_id={?}', $password,
                                     Session::getInt('uid'));

            $log =& Session::getMixed('log');
            $log->log('passwd', '');

            if (Cookie::get('ORGaccess')) {
                setcookie('ORGaccess', hash_encrypt($password), (time()+25920000), '/', '' ,0);
            }

            $page->changeTpl('motdepasse.success.tpl');
            $page->run();
        }

        $page->changeTpl('motdepasse.tpl');
        $page->addJsLink('javascript/motdepasse.js');
        $page->assign('xorg_title','Polytechnique.org - Mon mot de passe');
    }

    function handler_smtppass(&$page)
    {
        global $globals;

        $page->changeTpl('acces_smtp.tpl');
        $page->assign('xorg_title','Polytechnique.org - Acces SMTP/NNTP');

        $uid  = Session::getInt('uid');
        $pass = Env::get('smtppass1');
        $log  = Session::getMixed('log');

        if (Env::get('op') == "Valider" && strlen($pass) >= 6 
        &&  Env::get('smtppass1') == Env::get('smtppass2')) 
        {
            $globals->xdb->execute('UPDATE auth_user_md5 SET smtppass = {?}
                                     WHERE user_id = {?}', $pass, $uid);
            $page->trig('Mot de passe enregistré');
            $log->log("passwd_ssl");
        } elseif (Env::get('op') == "Supprimer") {
            $globals->xdb->execute('UPDATE auth_user_md5 SET smtppass = ""
                                     WHERE user_id = {?}', $uid);
            $page->trig('Compte SMTP et NNTP supprimé');
            $log->log("passwd_del");
        }

        $res = $globals->xdb->query("SELECT IF(smtppass != '', 'actif', '') 
                                       FROM auth_user_md5
                                      WHERE user_id = {?}", $uid);
        $page->assign('actif', $res->fetchOneCell());
    }

    function handler_recovery(&$page)
    {
        global $globals;

        $page->changeTpl('recovery.tpl');

        if (!Env::has('login') || !Env::has('birth')) {
            return;
        }

        if (!ereg('[0-3][0-9][0-1][0-9][1][9]([0-9]{2})', Env::get('birth'))) {
            $page->trig_run('Date de naissance incorrecte ou incohérente');
        }
        $birth   = sprintf('%s-%s-%s', substr(Env::get('birth'),4,4), substr(Env::get('birth'),2,2), substr(Env::get('birth'),0,2));

        $mailorg = strtok(Env::get('login'), '@');

        // paragraphe rajouté : si la date de naissance dans la base n'existe pas, on l'update
        // avec celle fournie ici en espérant que c'est la bonne

        $res = $globals->xdb->query(
                "SELECT  user_id, naissance
                   FROM  auth_user_md5 AS u
             INNER JOIN  aliases       AS a ON (u.user_id=a.id AND type!='homonyme')
                  WHERE  a.alias={?} AND u.perms IN ('admin','user') AND u.deces=0", $mailorg);
        list($uid, $naissance) = $res->fetchOneRow();

        if ($naissance == $birth) {
            $page->assign('ok', true);

            $url   = rand_url_id(); 
            $globals->xdb->execute('INSERT INTO perte_pass (certificat,uid,created) VALUES ({?},{?},NOW())', $url, $uid);
            $res   = $globals->xdb->query('SELECT email FROM emails WHERE uid = {?} AND NOT FIND_IN_SET("filter", flags)', $uid);
            $mails = implode(', ', $res->fetchColumn());

            require_once "diogenes/diogenes.hermes.inc.php";
            $mymail = new HermesMailer();
            $mymail->setFrom('"Gestion des mots de passe" <support+password@polytechnique.org>');
            $mymail->addTo($mails);
            $mymail->setSubject('Ton certificat d\'authentification');
            $mymail->setTxtBody("Visite la page suivante qui expire dans six heures :
{$globals->baseurl}/tmpPWD/$url

Si en cliquant dessus tu n'y arrives pas, copie intégralement l'adresse dans la barre de ton navigateur.

-- 
Polytechnique.org
\"Le portail des élèves & anciens élèves de l'Ecole polytechnique\"".(Post::get('email') ? "

Adresse de secours :
    ".Post::get('email') : "")."

Mail envoyé à ".Env::get('login'));
            $mymail->send();

            // on cree un objet logger et on log l'evenement
            $logger = $_SESSION['log'] = new DiogenesCoreLogger($uid);
            $logger->log('recovery', $emails);
        } else {
            $page->trig('Pas de résultat correspondant aux champs entrés dans notre base de données.');
        }
    }

    function handler_tmpPWD(&$page, $certif = null)
    {
        global $globals;

        $globals->xdb->execute('DELETE FROM perte_pass
                                      WHERE DATE_SUB(NOW(), INTERVAL 380 MINUTE) > created');

        $res   = $globals->xdb->query('SELECT uid FROM perte_pass WHERE certificat={?}', $certif);
        $ligne = $res->fetchOneAssoc();
        if (!$ligne) {
            $page->changeTpl('index.tpl');
            $page->kill("Cette adresse n'existe pas ou n'existe plus sur le serveur.");
        }

        $uid = $ligne["uid"];
        if (Post::has('response2')) {
            $password = Post::get('response2');
            $logger   = new DiogenesCoreLogger($uid);
            $globals->xdb->query('UPDATE  auth_user_md5 SET password={?}
                                   WHERE  user_id={?} AND perms IN("admin","user")',
                                 $password, $uid);
            $globals->xdb->query('DELETE FROM perte_pass WHERE certificat={?}', $certif);
            $logger->log("passwd","");
            $page->changeTpl('tmpPWD.success.tpl');
        } else {
            $page->changeTpl('motdepasse.tpl');
            $page->addJsLink('javascript/motdepasse.js');
        }
    }

    function handler_skin(&$page)
    {
        global $globals;

        if (!$globals->skin->enable) {
            redirect('./');
        }

        $page->changeTpl('skins.tpl');
        $page->assign('xorg_title','Polytechnique.org - Skins');

        if (Env::has('newskin'))  {  // formulaire soumis, traitons les données envoyées
            $globals->xdb->execute('UPDATE auth_user_quick
                                       SET skin={?} WHERE user_id={?}',
                                    Env::getInt('newskin'),
                                    Session::getInt('uid'));
            set_skin();
        }

        $sql = "SELECT s.*,auteur,count(*) AS nb
                  FROM skins AS s
             LEFT JOIN auth_user_quick AS a ON s.id=a.skin
                 WHERE skin_tpl != '' AND ext != ''
              GROUP BY id ORDER BY s.date DESC";
        $page->assign_by_ref('skins', $globals->xdb->iterator($sql));
    }

    function handler_exit(&$page, $level = null)
    {
        if (Session::has('suid')) {
            if (Session::has('suid')) {
                $a4l  = Session::get('forlife');
                $suid = Session::getMixed('suid');
                $log  = Session::getMixed('log');
                $log->log("suid_stop", Session::get('forlife') . " by " . $suid['forlife']);
                $_SESSION = $suid;
                Session::kill('suid');
                redirect($globals->baseurl.'/admin/utilisateurs.php?login='.$a4l);
            } else {
                redirect("events");
            }
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
            redirect(rawurldecode(Get::get('redirect')));
        } else {
            $page->changeTpl('exit.tpl');
        }
    }

    function handler_rss(&$page, $user = null, $hash = null)
    {
        global $globals;

        require_once 'rss.inc.php';

        $uid = init_rss('rss.tpl', $user, $hash);

        $rss = $globals->xdb->iterator(
                'SELECT  e.id, e.titre, e.texte, e.creation_date
                   FROM  auth_user_md5   AS u
             INNER JOIN  evenements      AS e ON ( (e.promo_min = 0 || e.promo_min <= u.promo)
                                                   AND (e.promo_max = 0 || e.promo_max >= u.promo) )
                  WHERE  u.user_id = {?} AND FIND_IN_SET(e.flags, "valide")
                                         AND peremption >= NOW()', $uid);
        $page->assign('rss', $rss);
    }
}

?>
