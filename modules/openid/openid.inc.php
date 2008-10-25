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

require_once "Auth/OpenID/Discover.php";

function init_openid_server()
{
    // Initialize a filesystem-based store
    $store_location = dirname(__FILE__) . '/../../spool/openid/store';
    require_once "Auth/OpenID/FileStore.php";
    $store = new Auth_OpenID_FileStore($store_location);

    // Create an OpenId server
    require_once 'Auth/OpenID/Server.php';
    return new Auth_OpenID_Server($store, get_openid_url());
}

function get_openid_url()
{
    global $globals;
    return $globals->baseurl . '/openid';
}

function get_user($x) {
    if (is_null($x)) {
        return null;
    }
    $user = User::getSilent($x);
    return $user ? $user : null;

}

function get_user_openid_url($user)
{
    if (is_null($user)) {
        return null;
    }
    global $globals;
    return $globals->baseurl . '/openid/' . $user->hruid;
}

function get_user_xrds_url($user)
{
    if (is_null($user)) {
        return null;
    }
    global $globals;
    return $globals->baseurl . '/openid/user_xrds/' . $user->hruid;
}




// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>