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

class XnetSession extends PlSession
{
    public function __construct()
    {
        parent::__construct();
    }

    public function startAvailableAuth()
    {
        if (!S::logged() && Get::has('auth')) {
            if (!$this->start(AUTH_MDP)) {
                return false;
            }
        }

        global $globals;
        if (!S::logged()) {
            // prevent connection to be linked to disconnection
            if (($i = strpos($_SERVER['REQUEST_URI'], 'exit')) !== false)
                $returl = "http://{$_SERVER['SERVER_NAME']}".substr($_SERVER['REQUEST_URI'], 0, $i);
            else
                $returl = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $url  = "https://www.polytechnique.org/auth-groupex";
            $url .= "?session=" . session_id();
            $url .= "&challenge=" . S::v('challenge');
            $url .= "&pass=" . md5(S::v('challenge') . $globals->xnet->secret);
            $url .= "&url=".urlencode($returl);
            S::set('loginX', $url);
        }

        if (S::logged() && $globals->asso()) {
            $perms = S::v('perms');
            $perms->rmFlag('groupadmin');
            $perms->rmFlag('groupmember');
            $perms->rmFlag('groupannu');
            if (may_update()) {
                $perms->addFlag('groupadmin');
                $perms->addFlag('groupmember');
                $perms->addFlag('groupannu');
            }
            if (is_member()) {
                $perms->addFlag('groupmember');
                if ($globals->asso('pub') != 'private') {
                    $perms->addFlag('groupannu');
                }
            }
            if ($globals->asso('cat') == 'Promotions') {
                $perms->addFlag('groupannu');
            }
            S::set('perms', $perms);
        }
        return true;
    }

    protected function doAuth($level)
    {
        if (S::identified()) { // ok, c'est bon, on n'a rien à faire
            return S::i('uid');
        }
        if (!Get::has('auth')) {
            return null;
        }
        global $globals;
        if (md5('1' . S::v('challenge') . $globals->xnet->secret . Get::i('uid') . '1') != Get::v('auth')) {
            return null;
        }
        Get::kill('auth');
        S::set('auth', AUTH_MDP);
        return Get::i('uid');
    }

    protected function startSessionAs($user, $level)
    {
        global $globals;

        if ($level == -1) {
            S::set('auth', AUTH_MDP);
        }
        $res  = XDB::query("SELECT  u.user_id AS uid, u.hruid, prenom, nom, perms, promo, password, FIND_IN_SET('femme', u.flags) AS femme,
                                    CONCAT(a.alias, '@{$globals->mail->domain}') AS forlife,
                                    CONCAT(a2.alias, '@{$globals->mail->domain}') AS bestalias,
                                    q.core_mail_fmt AS mail_fmt, q.core_rss_hash
                              FROM  auth_user_md5   AS u
                        INNER JOIN  auth_user_quick AS q  USING(user_id)
                        INNER JOIN  aliases         AS a  ON (u.user_id = a.id AND a.type = 'a_vie')
                        INNER JOIN  aliases         AS a2 ON (u.user_id = a2.id AND FIND_IN_SET('bestalias', a2.flags))
                             WHERE  u.user_id = {?} AND u.perms IN('admin', 'user')
                             LIMIT  1", $user);
        $sess = $res->fetchOneAssoc();
        $perms = $sess['perms'];
        unset($sess['perms']);
        $_SESSION = array_merge($_SESSION, $sess);
        S::set('perms', User::makePerms($perms));
        S::kill('challenge');
        S::kill('loginX');
        S::kill('may_update');
        S::kill('is_member');
        Get::kill('uid');
        Get::kill('PHPSESSID');

        $args = array();
        foreach($_GET as $key => $val) {
            $args[] = urlencode($key). '=' .urlencode($val);
        }
        return true;
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
            return new User($res->fetchOneCell());
        }
        return null;
    }

    public function doSelfSuid()
    {
        if (!$this->startSUID(S::i('uid'))) {
            return false;
        }
        S::set('perms', User::makePerms('user'));
        return true;
    }

    public function stopSUID()
    {
        $suid = S::v('suid');
        if (!parent::stopSUID()) {
            return false;
        }
        S::kill('suid');
        S::kill('may_update');
        S::kill('is_member');
        S::set('perms', $suid['perms']);
        return true;
    }

    public function makePerms($perm)
    {
        $flags = new PlFlagSet();
        if ($perm == 'disabled' || $perm == 'ext') {
            S::set('perms', $flags);
            return;
        }
        $flags->addFlag(PERMS_USER);
        if ($perm == 'admin') {
            $flags->addFlag(PERMS_ADMIN);
        }
        S::set('perms', $flags);
    }

    public function loggedLevel()
    {
        return AUTH_COOKIE;
    }

    public function sureLevel()
    {
        return AUTH_MDP;
    }
}

// {{{ function may_update

/** Return administration rights for the current asso
 * @param force Force administration rights to be read from database
 * @param lose  Force administration rights to be false
 */
function may_update($force = false, $lose = false)
{
    if (!isset($_SESSION['may_update'])) {
        $_SESSION['may_update'] = array();
    }
    $may_update =& $_SESSION['may_update'];

    global $globals;
    $asso_id = $globals->asso('id');
    if (!$asso_id) {
        return false;
    } elseif ($lose) {
        $may_update[$asso_id] = false;
    } elseif (S::has_perms() || (S::has('suid') && $force)) {
        $may_update[$asso_id] = true;
    } elseif (!isset($may_update[$asso_id]) || $force) {
        $res = XDB::query("SELECT  perms
                             FROM  groupex.membres
                            WHERE  uid={?} AND asso_id={?}",
                          S::v('uid'), $asso_id);
        $may_update[$asso_id] = ($res->fetchOneCell() == 'admin');
    }
    return $may_update[$asso_id];
}

// }}}
// {{{ function is_member

/** Get membership informations for the current asso
 * @param force Force membership to be read from database
 * @param lose  Force membership to be false
 */
function is_member($force = false, $lose = false)
{
    if (!isset($_SESSION['is_member'])) {
        $_SESSION['is_member'] = array();
    }
    $is_member =& $_SESSION['is_member'];

    global $globals;
    $asso_id = $globals->asso('id');
    if (!$asso_id) {
        return false;
    } elseif ($lose) {
        $is_member[$asso_id] = false;
    } elseif (S::has('suid') && $force) {
        $is_member[$asso_id] = true;
    } elseif (!isset($is_member[$asso_id]) || $force) {
        $res = XDB::query("SELECT  COUNT(*)
                             FROM  groupex.membres
                            WHERE  uid={?} AND asso_id={?}",
                S::v('uid'), $asso_id);
        $is_member[$asso_id] = ($res->fetchOneCell() == 1);
    }
    return $is_member[$asso_id];
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
