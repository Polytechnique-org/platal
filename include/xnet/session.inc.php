<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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

require_once('platal/session.inc.php');

// {{{ class XorgSession

class XnetSession extends DiogenesCoreSession
{
    // {{{ function XnetSession()

    function XnetSession()
    {
	$this->DiogenesCoreSession();
    }

    // }}}
    // {{{ function init
    
    function init() {
        global $globals;

        @session_start();
        if (!Session::has('session')) {
            $_SESSION['session'] = new XnetSession;
        }
        if (!logged()) {
            // prevent connexion to be linked to deconnexion
            if (($i = strpos($_SERVER['REQUEST_URI'], 'deconnexion.php')) !== false)
                $returl = "http://{$_SERVER['SERVER_NAME']}".substr($_SERVER['REQUEST_URI'], 0, $i);
            else
                $returl = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $url  = "https://www.polytechnique.org/auth-groupex.php";
            $url .= "?session=" . session_id();
            $url .= "&challenge=" . $_SESSION['session']->challenge;
            $url .= "&pass=" . md5($_SESSION['session']->challenge . $globals->xnet->secret);
            $url .= "&url=".urlencode($returl);
            $_SESSION['session']->loginX = $url;
        }
    }
    
    // }}}
    // {{{ function destroy()
    
    function destroy() {
        @session_destroy();
        unset($_SESSION);
        XnetSession::init();
    }
    
    // }}}
    // {{{ function doAuth()

    /** Try to do an authentication.
     *
     * @param page the calling page (by reference)
     */
    function doAuth(&$page)
    {
	global $globals;
	if (identified()) { // ok, c'est bon, on n'a rien à faire
	    return true;
	}

        if (Get::has('auth')) {
            return $this->doAuthX($page);
        } elseif (Post::has('challenge') && Post::has('username') && Post::has('response')) {
            return $this->doAuthOther($page);
        } else {
            $this->doLogin($page);
        }
    }

    // }}}
    // {{{ doAuthX

    function doAuthX(&$page) {
        global $globals;

        if (md5('1'.$this->challenge.$globals->xnet->secret.Get::getInt('uid').'1') != Get::get('auth')) {
            $page->kill("Erreur d'authentification avec polytechnique.org !");
        }

        $res  = $globals->xdb->query("
            SELECT  u.user_id AS uid, prenom, nom, perms, promo, password, FIND_IN_SET('femme', u.flags) AS femme,
                    a.alias AS forlife, a2.alias AS bestalias, q.core_mail_fmt AS mail_fmt, q.core_rss_hash
              FROM  auth_user_md5   AS u
        INNER JOIN  auth_user_quick AS q  USING(user_id)
        INNER JOIN  aliases         AS a  ON (u.user_id = a.id AND a.type='a_vie')
        INNER JOIN  aliases         AS a2 ON (u.user_id = a2.id AND FIND_IN_SET('bestalias',a2.flags))
             WHERE  u.user_id = {?} AND u.perms IN('admin','user')
             LIMIT  1", Get::getInt('uid'));
        $_SESSION = array_merge($_SESSION, $res->fetchOneAssoc());
        $_SESSION['auth'] = AUTH_MDP;
        unset($this->challenge);
        unset($this->loginX);
        Get::kill('auth');
        Get::kill('uid');
        $args = array();
        foreach($_GET as $key=>$val) {
            $args[] = urlencode($key).'='.urlencode($val);
        }
        header('Location: '.$_SERVER['PHP_SELF'] . '?' . join('&', $args));
    }

    // }}}
    // {{{ doAuthOther

    function doAuthOther(&$page) {
        if (Post::has('challenge') && Post::has('username') && Post::has('response')) {
            $username = Post::get('username');
        }
        $this->doLogin($page);
    }

    // }}}
    // {{{ doLogin

    function doLogin(&$page) {
        $page->addJsLink('javascript/md5.js');
        $page->addJsLink('javascript/do_challenge_response.js');
        $page->assign("xorg_tpl", "xnet/login.tpl");
        $page->run();
    }

    // }}}
}

// }}}
// {{{ may_update

function may_update() {
    global $globals;
    if (!$globals->asso('id')) { return false; }
    if (has_perms()) { return true; }
    $res = $globals->xdb->query(
            "SELECT  perms
               FROM  groupex.membres
              WHERE  uid={?} AND asso_id={?}", Session::getInt('uid'), $globals->asso('id'));
    return $res->fetchOneCell() == 'admin';
}

// }}}
// {{{ is_member

function is_member() {
    global $globals;
    if (!$globals->asso('id')) { return false; }
    $res = $globals->xdb->query(
            "SELECT  COUNT(*)
               FROM  groupex.membres
              WHERE  uid={?} AND asso_id={?}", Session::getInt('uid'), $globals->asso('id'));
    return $res->fetchOneCell() == 1;
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
