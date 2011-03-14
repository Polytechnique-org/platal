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

class XnetSession extends XorgSession
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

        if (!S::logged() && Post::has('auth_type') && Post::v('auth_type') == 'xnet') {
            $type = XDB::fetchOneCell('SELECT  type
                                         FROM  accounts
                                        WHERE  hruid = {?}',
                                      Post::v('username'));
            if (!is_null($type) && $type != 'xnet') {
                Platal::page()->trigErrorRedirect('Ce formulaire d\'authentification est réservé aux extérieurs à la communauté polytechnicienne.', '');
            }

            $user = parent::doAuth(AUTH_MDP);
            if (is_null($user)) {
                return false;
            }
            if (!parent::checkAuth(AUTH_MDP) || !parent::startSessionAs($user, AUTH_MDP)) {
                $this->destroy();
                return false;
            }
        }

        global $globals;
        if (!S::logged() && $globals->xnet->auth_baseurl) {
            // prevent connection to be linked to disconnection
            if (($i = strpos($_SERVER['REQUEST_URI'], 'exit')) !== false)
                $returl = "http://{$_SERVER['SERVER_NAME']}".substr($_SERVER['REQUEST_URI'], 0, $i);
            else
                $returl = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $url  = $globals->xnet->auth_baseurl;
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
            } else if ($globals->asso('pub') == 'public') {
                $perms->addFlag('groupannu');
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
            return User::getSilentWithValues(null, array('uid' => S::i('uid')));
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
        return User::getSilentWithValues(null, array('uid' => Get::i('uid')));
    }

    protected function startSessionAs($user, $level)
    {
        // The user must have 'groups' permission to access X.net
        if (!$user->checkPerms('groups')) {
            return false;
        }
        if ($level == AUTH_SUID) {
            S::set('auth', AUTH_MDP);
        }

        S::set('uid', $user->uid);
        S::set('hruid', $user->hruid);

        // XXX: Transition code, should not be in session anymore
        S::set('display_name', $user->display_name);
        S::set('full_name', $user->full_name);
        S::set('femme', $user->isFemale());
        S::set('email_format', $user->email_format);
        S::set('token', $user->token);
        S::set('perms', $user->perms);
        S::set('is_admin', $user->is_admin);


        $this->makePerms($user->perms, $user->is_admin);
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

    public function doSelfSuid()
    {
        $user =& S::user();
        if (!$this->startSUID($user)) {
            return false;
        }
        S::set('perms', User::makePerms(PERMS_USER));
        return true;
    }

    public function stopSUID()
    {
        $perms = S::suid('perms');
        if (!parent::stopSUID()) {
            return false;
        }
        S::kill('may_update');
        S::kill('is_member');
        S::set('perms', $perms);
        return true;
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
    } elseif (S::admin() || (S::suid() && $force)) {
        $may_update[$asso_id] = true;
    } elseif (!isset($may_update[$asso_id]) || $force) {
        $res = XDB::query("SELECT  perms
                             FROM  group_members
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
    } elseif (S::suid() && $force) {
        $is_member[$asso_id] = true;
    } elseif (!isset($is_member[$asso_id]) || $force) {
        $res = XDB::query("SELECT  COUNT(*)
                             FROM  group_members
                            WHERE  uid={?} AND asso_id={?}",
                S::v('uid'), $asso_id);
        $is_member[$asso_id] = ($res->fetchOneCell() == 1);
    }
    return $is_member[$asso_id];
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
