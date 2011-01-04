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

class XorgSession extends PlSession
{
    const INVALID_USER = -2;
    const NO_COOKIE = -1;
    const COOKIE_SUCCESS = 0;
    const INVALID_COOKIE = 1;

    public function __construct()
    {
        parent::__construct();
    }

    public function startAvailableAuth()
    {
        if (!S::logged()) {
            switch ($this->tryCookie()) {
              case self::COOKIE_SUCCESS:
                if (!$this->start(AUTH_COOKIE)) {
                    return false;
                }
                break;

              case self::INVALID_USER:
              case self::INVALID_COOKIE:
                return false;
            }
        }
        if ((check_ip('dangerous') && S::has('uid')) || check_account()) {
            S::logger()->log("view_page", $_SERVER['REQUEST_URI']);
        }
        return true;
    }

    /** Check the cookie and set the associated uid in the auth_by_cookie session variable.
     */
    private function tryCookie()
    {
        S::kill('auth_by_cookie');
        if (Cookie::v('access') == '' || !Cookie::has('uid')) {
            return self::NO_COOKIE;
        }

        $res = XDB::query('SELECT  uid, password
                             FROM  accounts
                            WHERE  uid = {?} AND state = \'active\'',
                         Cookie::i('uid'));
        if ($res->numRows() != 0) {
            list($uid, $password) = $res->fetchOneRow();
            if (sha1($password) == Cookie::v('access')) {
                S::set('auth_by_cookie', $uid);
                return self::COOKIE_SUCCESS;
            } else {
                return self::INVALID_COOKIE;
            }
        }
        return self::INVALID_USER;
    }

    private function checkPassword($uname, $login, $response, $login_type)
    {
        if ($login_type == 'alias') {
            $res = XDB::query('SELECT  a.uid, a.password
                                 FROM  accounts AS a
                           INNER JOIN  aliases  AS l ON (l.uid = a.uid AND l.type != \'homonyme\')
                                WHERE  l.alias = {?} AND a.state = \'active\'',
                              $login);
        } else {
            $res = XDB::query('SELECT  uid, password
                                 FROM  accounts
                                WHERE  ' . $login_type . ' = {?}',
                              $login);
        }
        if (list($uid, $password) = $res->fetchOneRow()) {
            $expected_response = sha1("$uname:$password:" . S::v('challenge'));
            /* Deprecates len(password) > 10 conversion. */
            if ($response != $expected_response) {
                if (!S::logged()) {
                    Platal::page()->trigError('Mot de passe ou nom d\'utilisateur invalide');
                } else {
                    Platal::page()->trigError('Mot de passe invalide');
                }
                S::logger($uid)->log('auth_fail', 'bad password');
                return null;
            }
            return $uid;
        }
        Platal::page()->trigError('Mot de passe ou nom d\'utilisateur invalide');
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
            return User::getSilentWithUID(S::i('auth_by_cookie'));
        }


        /* We want to do auth... we must have infos from a form.
         */
        if (!Post::has('username') || !Post::has('response') || !S::has('challenge')) {
            return null;
        }

        /** We come from an authentication form.
         */
        if (S::suid()) {
            $login = $uname = S::suid('uid');
            $loginType = 'uid';
            $redirect = false;
        } else {
            $uname = Post::v('username');
            if (Post::s('domain') == "alias") {
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
                $loginType = 'alias';
            } else if (Post::s('domain') == "ax") {
                $login = $uname;
                $redirect = false;
                $loginType = 'hruid';
            } else {
                $login = $uname;
                $redirect = false;
                $loginType = is_numeric($uname) ? 'uid' : 'alias';
            }
        }

        $uid = $this->checkPassword($uname, $login, Post::v('response'), $loginType);
        if (!is_null($uid) && S::suid()) {
            if (S::suid('uid') == $uid) {
                $uid = S::i('uid');
            } else {
                $uid = null;
            }
        }
        if (!is_null($uid)) {
            S::set('auth', AUTH_MDP);
            if (!S::suid()) {
                if (Post::has('domain')) {
                    $domain = Post::v('domain', 'login');
                    if ($domain == 'alias') {
                        Cookie::set('domain', 'alias', 300);
                    } else if ($domain == 'ax') {
                        Cookie::set('domain', 'ax', 300);
                    } else {
                        Cookie::kill('domain');
                    }
                }
            }
            S::kill('challenge');
            S::logger($uid)->log('auth_ok');
        }
        return User::getSilentWithUID($uid);
    }

    protected function startSessionAs($user, $level)
    {
        if ((!is_null(S::user()) && S::user()->id() != $user->id())
            || (S::has('uid') && S::i('uid') != $user->id())) {
            return false;
        } else if (S::has('uid')) {
            return true;
        }
        if ($level == AUTH_SUID) {
            S::set('auth', AUTH_MDP);
        }

        // Loads uid and hruid into the session for developement conveniance.
        $_SESSION = array_merge($_SESSION, array('uid' => $user->id(), 'hruid' => $user->hruid, 'token' => $user->token, 'user' => $user));

        // Starts the session's logger, and sets up the permanent cookie.
        if (S::suid()) {
            S::logger()->log("suid_start", S::v('hruid') . ' by ' . S::suid('hruid'));
        } else {
            S::logger()->saveLastSession();
            Cookie::set('uid', $user->id(), 300);

            if (S::i('auth_by_cookie') == $user->id() || Post::v('remember', 'false') == 'true') {
                $this->setAccessCookie(false, S::i('auth_by_cookie') != $user->id());
            } else {
                $this->killAccessCookie();
            }
        }

        // Finalizes the session setup.
        $this->makePerms($user->perms, $user->is_admin);
        $this->securityChecks();
        $this->setSkin();
        $this->updateNbNotifs();
        check_redirect();

        // We should not have to use this private data anymore
        S::kill('auth_by_cookie');
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
        $res = XDB::query('SELECT  a.uid, a.hruid
                             FROM  accounts AS a
                            WHERE  a.token = {?} AND a.hruid = {?} AND a.state = \'active\'',
                          $token, $login);
        if ($res->numRows() == 1) {
            return new User(null, $res->fetchOneAssoc());
        }
        return null;
    }

    protected function makePerms($perm, $is_admin)
    {
        S::set('perms', User::makePerms($perm, $is_admin));
    }

    public function setSkin()
    {
        if (S::logged() && (!S::has('skin') || S::suid())) {
            $res = XDB::query('SELECT  skin_tpl
                                 FROM  accounts AS a
                           INNER JOIN  skins    AS s on (a.skin = s.id)
                                WHERE  a.uid = {?} AND skin_tpl != \'\'', S::i('uid'));
            S::set('skin', $res->fetchOneCell());
        }
    }

    public function loggedLevel()
    {
        return AUTH_COOKIE;
    }

    public function sureLevel()
    {
        return AUTH_MDP;
    }


    public function updateNbNotifs()
    {
        require_once 'notifs.inc.php';
        $user = S::user();
        $n = Watch::getCount($user);
        S::set('notifs', $n);
    }

    public function setAccessCookie($replace = false, $log = true) {
        if (S::suid() || ($replace && !Cookie::blank('access'))) {
            return;
        }
        Cookie::set('access', sha1(S::user()->password()), 300, true);
        if ($log) {
            S::logger()->log('cookie_on');
        }
    }

    public function killAccessCookie($log = true) {
        Cookie::kill('access');
        if ($log) {
            S::logger()->log('cookie_off');
        }
    }

    public function killLoginFormCookies() {
        Cookie::kill('uid');
        Cookie::kill('domain');
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
