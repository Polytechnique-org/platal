<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

class XorgGroupeXSession extends XorgSession
{
    public function __construct()
    {
        parent::__construct();
    }

    public function startAvailableAuth()
    {
        if (!S::logged() && Get::has('auth')) {
            if (!$this->start(AUTH_PASSWD)) {
                return false;
            }
        }

        global $globals;
        if (!S::logged() && $globals->xorgauth->auth_baseurl) {
            // prevent connection to be linked to disconnection
            if (($i = strpos($_SERVER['REQUEST_URI'], 'exit')) !== false)
                $returl = "https://{$_SERVER['SERVER_NAME']}".substr($_SERVER['REQUEST_URI'], 0, $i);
            else
                $returl = "https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
            $url  = $globals->xorgauth->auth_baseurl;
            $url .= "?session=" . session_id();
            $url .= "&challenge=" . S::v('challenge');
            $url .= "&pass=" . md5(S::v('challenge') . $globals->xorgauth->secret);
            $url .= "&url=".urlencode($returl);
            S::set('loginX', $url);
        }

        return true;
    }

    protected function doAuth($level)
    {
        if (S::identified()) { // Nothing to do there
            return User::getSilentWithValues(null, array('uid' => S::i('uid')));
        }
        if (!Get::has('auth')) {
            return null;
        }
        global $globals;
        if (md5('1' . S::v('challenge') . $globals->xorgauth->secret . Get::i('uid') . '1' ) != Get::v('auth')) {
            return null;
        }
        Get::kill('auth');
        S::set('auth', AUTH_PASSWD);
        return User::getSilentWithValues(null, array('uid' => Get::i('uid')));
    }

    protected function startSessionAs($user, $level) {
        S::kill('loginX');
        S::kill('challenge');
        return parent::startSessionAs($user, $level);
    }

}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
