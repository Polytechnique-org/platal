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

class Session
{
    public static function init()
    {
        @session_start();
        if (empty($_SESSION['challenge'])) {
            $_SESSION['challenge'] = sha1(uniqid(rand(), true));
        }
        if (!isset($_SESSION['perms']) || !($_SESSION['perms'] instanceof FlagSet)) {
            $_SESSION['perms'] = new FlagSet();
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
