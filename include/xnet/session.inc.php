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

require_once dirname(__FILE__).'/../../classes/Session.php';

class XnetSession
{
    // {{{ function init

    function init() {
        global $globals;

        S::init();

        if (!S::logged()) {
            // prevent connexion to be linked to deconnexion
            if (($i = strpos($_SERVER['REQUEST_URI'], 'exit')) !== false)
                $returl = "http://{$_SERVER['SERVER_NAME']}".substr($_SERVER['REQUEST_URI'], 0, $i);
            else
                $returl = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $url  = "https://www.polytechnique.org/auth-groupex.php";
            $url .= "?session=" . session_id();
            $url .= "&challenge=" . S::v('challenge');
            $url .= "&pass=" . md5(S::v('challenge') . $globals->xnet->secret);
            $url .= "&url=".urlencode($returl);
            $_SESSION['loginX'] = $url;
        }
    }

    // }}}
    // {{{ function destroy()

    function destroy() {
        S::destroy();
        XnetSession::init();
    }

    // }}}
    // {{{ function doAuth()

    /** Try to do an authentication.
     *
     * @param page the calling page (by reference)
     */
    function doAuth()
    {
	if (S::identified()) { // ok, c'est bon, on n'a rien à faire
	    return true;
	}

        if (Get::has('auth')) {
            return XnetSession::doAuthX();
        }

        return false;
    }

    // }}}
    // {{{ doAuthCookie

    function doAuthCookie() {
        return XnetSession::doAuth();
    }

    // }}}
    // {{{ doAuthX

    function doAuthX() {
        global $globals, $page;

        if (md5('1'.S::v('challenge').$globals->xnet->secret.Get::i('uid').'1') != Get::v('auth')) {
            $page->kill("Erreur d'authentification avec polytechnique.org !");
        }

        $res  = XDB::query("
            SELECT  u.user_id AS uid, prenom, nom, perms, promo, password, FIND_IN_SET('femme', u.flags) AS femme,
                    a.alias AS forlife, a2.alias AS bestalias, q.core_mail_fmt AS mail_fmt, q.core_rss_hash
              FROM  auth_user_md5   AS u
        INNER JOIN  auth_user_quick AS q  USING(user_id)
        INNER JOIN  aliases         AS a  ON (u.user_id = a.id AND a.type='a_vie')
        INNER JOIN  aliases         AS a2 ON (u.user_id = a2.id AND FIND_IN_SET('bestalias',a2.flags))
             WHERE  u.user_id = {?} AND u.perms IN('admin','user')
             LIMIT  1", Get::i('uid'));
        $_SESSION = array_merge($_SESSION, $res->fetchOneAssoc());
        $_SESSION['auth'] = AUTH_MDP;
        S::kill('challenge');
        S::kill('loginX');
        Get::kill('auth');
        Get::kill('uid');
        $path = Get::v('n');
        Get::kill('n');
        Get::kill('PHPSESSID');

        $args = array();
        foreach($_GET as $key => $val) {
            $args[] = urlencode($key).'='.urlencode($val);
        }

        http_redirect($globals->baseurl . '/' . $path, join('&', $args));
    }

    // }}}
}

// {{{ may_update

function may_update() {
    global $globals;
    if (!$globals->asso('id')) { return false; }
    if (S::has_perms()) { return true; }
    $res = XDB::query(
            "SELECT  perms
               FROM  groupex.membres
              WHERE  uid={?} AND asso_id={?}", S::v('uid'), $globals->asso('id'));
    return $res->fetchOneCell() == 'admin';
}

// }}}
// {{{ is_member

function is_member() {
    global $globals;
    $asso_id = $globals->asso('id');
    if (!$asso_id) { return false; }
    static $is_member;
    if (!$is_member) $is_member = array();
    if (!isset($is_member[$asso_id]))
    {
        $res = XDB::query(
            "SELECT  COUNT(*)
               FROM  groupex.membres
              WHERE  uid={?} AND asso_id={?}",
                S::v('uid'), $asso_id);
        $is_member[$asso_id] = $res->fetchOneCell() == 1;
    }
    return $is_member[$asso_id];
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
