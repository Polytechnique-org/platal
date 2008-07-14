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

class S
{
    /** Set a constructor because this is called prior to S::s(), so we can
     * define S::s() for other usages.
     */
    private function __construct()
    {
        assert(false);
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
        return (string)S::v($key, $default);
    }

    public static function i($key, $default = 0)
    {
        $i = S::v($key, $default);
        return is_numeric($i) ? intval($i) : $default;
    }

    public static function l(array $keys)
    {
        return array_map(array('S', 'v'), $keys);
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] =& $value;
    }

    public static function bootstrap($key, $value)
    {
        if (!S::has($key)) {
            S::set($key, $value);
        }
    }

    public static function logger($uid = null)
    {
        if (!S::has('log')) {
            if (S::has('suid')) {
                $suid = S::v('suid');
                S::set('log', new PlLogger(S::v('uid'), $suid['uid']));
            } else if (S::has('uid') || $uid) {
                S::set('log', new PlLogger(S::v('uid', $uid)));
            }
        }
        return S::v('log');
    }

    /** User object storage and accessor. The user object (an instance of the
     * local subclass of PlUser) is currently stored as a S class variable, and
     * not as a session variable, so as to avoid bloating the global on-disk
     * session.
     * TODO: When all the codebase will use S::user() as the only source for
     * user ids, fullname/displayname, and forlife/bestalias, S::$user should
     * move into the php session (and data it helds should be removed from
     * the php session). */
    private static $user = null;
    public static function &user()
    {
        if (self::$user == null) {
            self::$user = User::getSilentWithValues(S::i('uid'), $_SESSION);
        }
        return self::$user;
    }

    public static function has_perms()
    {
        return Platal::session()->checkPerms(PERMS_ADMIN);
    }

    public static function logged()
    {
        return S::v('auth', AUTH_PUBLIC) > AUTH_PUBLIC;
    }

    public static function identified()
    {
        return S::v('auth', AUTH_PUBLIC) >= Platal::session()->sureLevel();
    }

    // Anti-XSRF protections.
    public static function has_xsrf_token()
    {
        return S::has('xsrf_token') && S::v('xsrf_token') == Env::v('token');
    }

    public static function assert_xsrf_token()
    {
        if (!S::has_xsrf_token()) {
            Platal::page()->kill('L\'opération n\'a pas pu aboutir, merci de réessayer.');
        }
    }

    public static function rssActivated()
    {
        return S::has('core_rss_hash') && S::v('core_rss_hash');
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
