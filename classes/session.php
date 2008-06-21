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

class Session
{
    public static function init()
    {
        @session_start();
        if (empty($_SESSION['challenge'])) {
            $_SESSION['challenge'] = sha1(uniqid(rand(), true));
        }
        if (empty($_SESSION['xsrf_token'])) {
            require_once 'xorg.misc.inc.php';
            $_SESSION['xsrf_token'] = rand_url_id();
        }
        if (!isset($_SESSION['perms']) || !($_SESSION['perms'] instanceof PlFlagSet)) {
            $_SESSION['perms'] = new PlFlagSet();
        }
    }

    public static function destroy()
    {
        @session_destroy();
        unset($_SESSION);
    }

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function kill($key)
    {
        unset($_SESSION[$key]);
    }

    public static function v($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function s($key, $default = '')
    {
        return (string)Session::v($key, $default);
    }

    public static function i($key, $default = 0)
    {
        $i = Session::v($key, $default);
        return is_numeric($i) ? intval($i) : $default;
    }

    public static function l(array $keys)
    {
        return array_map(array('Session', 'v'), $keys);
    }

    public static function has_perms()
    {
        return Session::logged() && Session::v('perms')->hasFlag(PERMS_ADMIN);
    }

    public static function logged()
    {
        return Session::v('auth', AUTH_PUBLIC) >= AUTH_COOKIE;
    }

    public static function identified()
    {
        return Session::v('auth', AUTH_PUBLIC) >= AUTH_MDP;
    }

    // Anti-XSRF protections.
    public static function has_xsrf_token()
    {
        return Session::has('xsrf_token') && Session::v('xsrf_token') == Env::v('token');
    }

    public static function assert_xsrf_token()
    {
        if (!Session::has_xsrf_token()) {
            global $page;
            if ($page instanceof PlPage) {
                $page->kill("L'opération n'a pas pu aboutir, merci de réessayer.");
            }
        }
    }

    public static function rssActivated()
    {
        return Session::has('core_rss_hash') && Session::v('core_rss_hash');
    }
}

// {{{ function check_perms()

/** verifie si un utilisateur a les droits pour voir une page
 ** si ce n'est pas le cas, on affiche une erreur
 * @return void
 */
function check_perms()
{
    global $page;
    if (!S::has_perms()) {
        if ($_SESSION['log']) {
            $_SESSION['log']->log("noperms",$_SERVER['PHP_SELF']);
        }
	$page->kill("Tu n'as pas les permissions nécessaires pour accéder à cette page.");
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
