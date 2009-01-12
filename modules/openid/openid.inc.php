<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

function get_user_by_alias($x) {
    if (is_null($x)) {
        return null;
    }
    // TODO such a function should probably be provided in the User class
    // or at least not here
    $res = XDB::query('SELECT  u.user_id
                         FROM  auth_user_md5   AS u
                   INNER JOIN  auth_user_quick AS q USING(user_id)
                   INNER JOIN  aliases         AS a ON (a.id = u.user_id AND type != \'homonyme\')
                        WHERE  u.perms IN(\'admin\', \'user\')
                          AND  q.emails_alias_pub = \'public\'
                          AND  a.alias = {?}',
                               $x);
    if (list($uid) = $res->fetchOneRow()) {
        $user = User::getSilent($uid);
    }
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

function get_idp_xrds_url()
{
    global $globals;
    return $globals->baseurl . '/openid/idp_xrds';
}

function get_user_xrds_url($user)
{
    if (is_null($user)) {
        return null;
    }
    global $globals;
    return $globals->baseurl . '/openid/user_xrds/' . $user->hruid;
}

function get_sreg_data($user)
{
    if (is_null($user)) {
        return null;
    }
    return array('fullname' => $user->fullName(),
                 'nickname' => $user->displayName(),
                 'dob' => null,
                 'email' => $user->bestEmail(),
                 'gender' => $user->isFemale() ? 'F' : 'M',
                 'postcode' => null,
                 'country' => null,
                 'language' => null,
                 'timezone' => null);
}

function is_trusted_site($user, $url)
{
    $res = XDB::query('SELECT  COUNT(*)
                         FROM  openid_trusted
                        WHERE  (user_id = {?} OR user_id IS NULL)
                          AND  url = {?}',
                               $user->id(), $url);
    return $res->fetchOneCell() > 0;
}

function add_trusted_site($user, $url)
{
    XDB::execute("INSERT IGNORE INTO openid_trusted
                      SET user_id={?}, url={?}",
                  $user->id(), $url);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>