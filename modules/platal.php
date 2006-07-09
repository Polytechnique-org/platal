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

class PlatalModule extends PLModule
{
    function handlers()
    {
        return array(
            // Preferences thingies
            'prefs'       => $this->make_hook('prefs',     AUTH_COOKIE),
            'prefs/rss'   => $this->make_hook('prefs_rss', AUTH_COOKIE),
            'skin'        => $this->make_hook('skin',      AUTH_COOKIE),

            // password related thingies
            'password'    => $this->make_hook('password',  AUTH_MDP),
            'tmpPWD'      => $this->make_hook('tmpPWD',    AUTH_PUBLIC),

            // happenings related thingies
            'rss'         => $this->make_hook('rss',       AUTH_PUBLIC),
        );
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
            redirect('preferences');
        }

        if (Env::has('rss')) {
            $this->__set_rss_state(Env::getBool('rss'));
        }

        $page->assign('prefs', $globals->hook->prefs());

        return PL_OK;
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

        return PL_OK;
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

        return PL_OK;
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

        return PL_OK;
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
        return PL_OK;
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

        return PL_OK;
    }
}

?>
