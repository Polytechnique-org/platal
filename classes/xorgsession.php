<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

class XorgSession extends PlSession
{
    public function __construct()
    {
        parent::__construct();
        S::bootstrap('perms_backup', new PlFlagSet());
    }

    public function startAvailableAuth()
    {
        if (!(S::v('perms') instanceof PlFlagSet)) {
            S::set('perms', S::v('perms_backup'));
        }
        if (!S::logged()) {
            $cookie = $this->tryCookie();
            if ($cookie == 0) {
                return $this->start(AUTH_COOKIE);
            } else if ($cookie == 1 || $cookie == -2) {
                return false;
            }
        }
        if ((check_ip('dangerous') && S::has('uid')) || check_account()) {
            $_SESSION['log']->log("view_page", $_SERVER['REQUEST_URI']);
        }
        return true;
    }

    /** Check the cookie and set the associated user_id in the auth_by_cookie session variable.
     */
    private function tryCookie()
    {
        S::kill('auth_by_cookie');
        if (Cookie::v('ORGaccess') == '' || !Cookie::has('ORGuid')) {
            return -1;
        }

        $res = XDB::query('SELECT  user_id, password
                             FROM  auth_user_md5
                            WHERE  user_id = {?} AND perms IN(\'admin\', \'user\')',
                         Cookie::i('ORGuid'));
        if ($res->numRows() != 0) {
            list($uid, $password) = $res->fetchOneRow();
            require_once 'secure_hash.inc.php';
            $expected_value = hash_encrypt($password);
            if ($expected_value == Cookie::v('ORGaccess')) {
                S::set('auth_by_cookie', $uid);
                return 0;
            } else {
                return 1;
            }
        }
        return -2;
    }

    private function checkPassword($uname, $login, $response, $login_type)
    {
        $res = XDB::query('SELECT  u.user_id, u.password
                             FROM  auth_user_md5 AS u
                       INNER JOIN  aliases       AS a ON (a.id = u.user_id AND type != \'homonyme\')
                             WHERE  a.' . $login_type . ' = {?} AND u.perms IN(\'admin\', \'user\')',
                          $login);
        if (list($uid, $password) = $res->fetchOneRow()) {
            require_once 'secure_hash.inc.php';
            $expected_response = hash_encrypt("$uname:$password:" . S::v('challenge'));
            if ($response != $expected_response) {
                $new_password = hash_xor(Env::v('xorpass'), $password);
                $expected_response = hash_encrypt("$uname:$new_password:" . S::v('challenge'));
                if ($response == $expected_response) {
                      XDB::execute('UPDATE  auth_user_md5
                                       SET  password = {?}
                                     WHERE  user_id = {?}',
                                   $new_password, $uid);
                }
            }
            if ($response != $expected_response) {
                S::logger($uid)->log('auth_fail', 'bad password');
                return null;
            }
            return $uid;
        }
        return null;
    }


    /** Check auth.
     */
    protected function doAuth($level)
    {
        global $globals;

        /* Cookie authentication
         */
        if ($level == AUTH_COOKIE && !S::has('auth_by_cookie')) {
            $this->tryCookie();
        }
        if ($level == AUTH_COOKIE && S::has('auth_by_cookie')) {
            if (!S::logged()) {
                S::set('auth', AUTH_COOKIE);
            }
            return S::i('auth_by_cookie');
        }


        /* We want to do auth... we must have infos from a form.
         */
        if (!Post::has('username') || !Post::has('response') || !S::has('challenge')) {
            return null;
        }

        /** We come from an authentication form.
         */
        if (S::has('suid')) {
            $suid  = S::v('suid');
            $login = $uname = $suid['forlife'];
            $redirect = false;
        } else {
            $uname = Env::v('username');

            if (Env::v('domain') == "alias") {
                $res = XDB::query('SELECT  redirect
                                     FROM  virtual
                               INNER JOIN  virtual_redirect USING(vid)
                                    WHERE  alias LIKE {?}',
                                   $uname . '@' . $globals->mail->alias_dom);
                $redirect = $res->fetchOneCell();
                if ($redirect) {
                    $login = substr($redirect, 0, strpos($redirect, '@'));
                } else {
                    $login = '';
                }
            } else {
                $login = $uname;
                $redirect = false;
            }
        }

        $uid = $this->checkPassword($uname, $login, Post::v('response'), (!$redirect && preg_match('/^\d*$/', $uname)) ? 'id' : 'alias');
        if (!is_null($uid)) {
            S::set('auth', AUTH_MDP);
            if (Post::has('domain')) {
                if (($domain = Post::v('domain', 'login')) == 'alias') {
                    setcookie('ORGdomain', "alias", (time() + 25920000), '/', '', 0);
                } else {
                    setcookie('ORGdomain', '', (time() - 3600), '/', '', 0);
                }
                // pour que la modification soit effective dans le reste de la page
                $_COOKIE['ORGdomain'] = $domain;
            }
            S::kill('challenge');
            S::logger($uid)->log('auth_ok');
        }
        return $uid;
    }

    protected function startSessionAs($uid, $level)
    {
        if ((!is_null(S::v('user')) && S::i('user') != $uid) || (S::has('uid') && S::i('uid') != $uid)) {
            return false;
        } else if (S::has('uid')) {
            return true;
        }
        if ($level == -1) {
            S::set('auth', AUTH_COOKIE);
        }
        unset($_SESSION['log']);

        // Retrieves main user properties.
        global $globals;
        $res  = XDB::query("SELECT  u.user_id AS uid, u.hruid, prenom, prenom_ini, nom, nom_ini, nom_usage, perms, promo, promo_sortie,
                                    matricule, password, FIND_IN_SET('femme', u.flags) AS femme,
                                    CONCAT(a.alias, '@{$globals->mail->domain}') AS forlife,
                                    CONCAT(a2.alias, '@{$globals->mail->domain}') AS bestalias,
                                    q.core_mail_fmt AS mail_fmt, UNIX_TIMESTAMP(q.banana_last) AS banana_last, q.watch_last, q.core_rss_hash,
                                    FIND_IN_SET('watch', u.flags) AS watch_account, q.last_version, g.g_account_name IS NOT NULL AS googleapps
                              FROM  auth_user_md5   AS u
                        INNER JOIN  auth_user_quick AS q  USING(user_id)
                        INNER JOIN  aliases         AS a  ON (u.user_id = a.id AND a.type = 'a_vie')
                        INNER JOIN  aliases         AS a2 ON (u.user_id = a2.id AND FIND_IN_SET('bestalias', a2.flags))
                         LEFT JOIN  gapps_accounts  AS g  ON (u.user_id = g.l_userid AND g.g_status = 'active')
                             WHERE  u.user_id = {?} AND u.perms IN('admin', 'user')", $uid);
        $sess = $res->fetchOneAssoc();
        $perms = $sess['perms'];
        unset($sess['perms']);

        // Retrieves account usage information (last login, last host).
        $res = XDB::query('SELECT  UNIX_TIMESTAMP(s.start) AS lastlogin, s.host
                             FROM  logger.sessions AS s
                            WHERE  s.uid = {?} AND s.suid = 0
                         ORDER BY  s.start DESC
                            LIMIT  1', $uid);
        if ($res->numRows()) {
            $sess = array_merge($sess, $res->fetchOneAssoc());
        }

        // Loads the data into the real session.
        $_SESSION = array_merge($_SESSION, $sess);

        // Starts the session's logger, and sets up the permanent cookie.
        if (S::has('suid')) {
            $suid = S::v('suid');
            $logger = S::logger($uid);
            $logger->log("suid_start", S::v('forlife') . " by " . $suid['uid']);
        } else {
            $logger = S::logger($uid);
            setcookie('ORGuid', $uid, (time() + 25920000), '/', '', 0);
            if (Post::v('remember', 'false') == 'true') {
                $cookie = hash_encrypt($sess['password']);
                setcookie('ORGaccess', $cookie, (time() + 25920000), '/', '', 0);
                if ($logger) {
                    $logger->log("cookie_on");
                }
            } else {
                setcookie('ORGaccess', '', time() - 3600, '/', '', 0);
                if ($logger) {
                    $logger->log("cookie_off");
                }
            }
        }

        // Finalizes the session setup.
        S::set('perms', User::makePerms($perms));
        $this->securityChecks();
        $this->setSkin();
        $this->updateNbNotifs();
        check_redirect();
        return true;
    }

    private function securityChecks()
    {
        $mail_subject = array();
        if (check_account()) {
            $mail_subject[] = 'Connexion d\'un utilisateur surveillé';
        }
        if (check_ip('unsafe')) {
            $mail_subject[] = 'Une IP surveillee a tente de se connecter';
            if (check_ip('ban')) {
                send_warning_mail(implode(' - ', $mail_subject));
                $this->destroy();
                Platal::page()->kill('Une erreur est survenue lors de la procédure d\'authentification. '
                                    . 'Merci de contacter au plus vite '
                                    . '<a href="mailto:support@polytechnique.org">support@polytechnique.org</a>');
                return false;
            }
        }
        if (count($mail_subject)) {
            send_warning_mail(implode(' - ', $mail_subject));
        }
    }

    public function tokenAuth($login, $token)
    {
        $res = XDB::query('SELECT  u.hruid
                             FROM  aliases         AS a
                       INNER JOIN  auth_user_md5   AS u ON (a.id = u.user_id AND u.perms IN ("admin", "user"))
                       INNER JOIN  auth_user_quick AS q ON (a.id = q.user_id AND q.core_rss_hash = {?})
                            WHERE  a.alias = {?} AND a.type != "homonyme"', $token, $login);
        if ($res->numRows() == 1) {
            $data = $res->fetchOneAssoc();
            return new User($data['hruid'], $data);
        }
        return null;
    }

    public function setSkin()
    {
        global $globals;
        if (S::logged() && (!S::has('skin') || S::has('suid'))) {
            $uid = S::v('uid');
            $res = XDB::query("SELECT  skin_tpl
                                 FROM  auth_user_quick AS a
                           INNER JOIN  skins           AS s ON a.skin = s.id
                                WHERE  user_id = {?} AND skin_tpl != ''", $uid);
            S::set('skin', $res->fetchOneCell());
        }
    }

    public function sureLevel()
    {
        return AUTH_MDP;
    }


    public function updateNbNotifs()
    {
        require_once 'notifs.inc.php';
        $n = select_notifs(false, S::i('uid'), S::v('watch_last'), false);
        S::set('notifs', $n->numRows());
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
