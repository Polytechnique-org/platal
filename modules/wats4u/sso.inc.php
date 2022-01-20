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


/** Helpers for Wats4U' SSO.
 *
 * Entry points:
 * - wats4u_sso_check: checks that the current request is valid
 * - wats4u_sso_build_return_url: build the full return URL for the given User
 *
 * Both functions will dispatch calls to the variant handling the requested protocol version.
 */

require_once 'security.inc.php';
require_once 'urls.inc.php';

define('WATS4U_MINIMUM_CHALLENGE_LENGTH', 16);


/** Check the validity of the current Wats4U SSO request.
 *
 * Dispatches the request to the appropriate protocol checker.
 *
 * Uses Get::
 *
 * @returns: boolean, whether the current HTTP request is a valid Wats4U request.
 */
function wats4u_sso_check()
{
    global $globals;

    $version = Get::s('version');
    switch ($version) {

    case "1.0":
        if ( !Get::has('url')
            || !Get::has('challenge')
            || !Get::has('pass')
            || !Get::has('session')
        ) {
            return false;
        }
        $return_url = Get::s('url');
        $challenge = Get::s('challenge');
        $pass = Get::s('pass');
        $shared_key = $globals->wats4u->shared_key;
        $valid_return_url_regex = $globals->wats4u->return_url_regex;

        return wats4u_sso_v1_check($return_url, $challenge, $pass,
            $shared_key, $valid_return_url_regex);

    default:
        return false;
    }
}


/** Build a Wats4U return URL based on the current SSO request.
 *
 * Dispatches the request to the appropriate protocol builder.
 *
 * Uses Get::
 *
 * @returns: string, the new return url.
 */
function wats4u_sso_build_return_url($user)
{
    switch (Get::s('version')){
    case "1.0":
        return wats4u_sso_v1_build_return_url($user);

    default:
        return "";
    }
}


/** Protocol v1.0
 *  =============
 */

/** Check v1 SSO requests.
 * "pure" function.
 */
function wats4u_sso_v1_check($return_url, $challenge, $pass,
    $shared_key, $valid_return_url_regex)
{
    if (strlen($challenge) < WATS4U_MINIMUM_CHALLENGE_LENGTH) {
        return false;
    }

    $expected = md5($challenge . $shared_key);
    if (! secure_string_compare($expected, $pass)) {
        return false;
    }

    if (!preg_match($valid_return_url_regex, $return_url)) {
        return false;
    }

    return true;
}

/** Encrypts some data according to Wats4U SSO specs.
 *
 * We use Blowfish/ECB, and encode the output as base64
 * PHP uses a '\0' char padding (a.k.a PKCS#5).
 *
 * @returns: The encrypted/encoded string, or false is something failed.
 */
function wats4u_sso_v1_encrypt_data($data, $key)
{
    $enc = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $data, MCRYPT_MODE_ECB);
    if ($enc === false) {
        return $enc;
    }

    // Encode as base64/URL
    return strtr(base64_encode($enc), '+/=', '-_,');
}


/** Specific return URL builder for protocol v1.0.
 * Reads Get::
 */
function wats4u_sso_v1_build_return_url($user)
{
    global $globals;
    $challenge = Get::s('challenge');
    $session = Get::s('session');
    $return_url = Get::s('url');

    $profile = $user->profile();
    $ax_id = $profile->ax_id;
    $email = $user->forlifeEmail();
    $contributing = 1;  // TODO: Fetch actual data
    $last_name = $profile->lastName();
    $first_name = $profile->firstName();

    $signature = md5("1" . $challenge . $globals->wats4u->shared_key . $ax_id . "1");

    $cleartext_data = "$challenge\n$ax_id\n$email\n$contributing\n$last_name\n$first_name";
    $encrypted_data = wats4u_sso_v1_encrypt_data($cleartext_data, $globals->wats4u->shared_key);
    if ($encrypted_data === false) {
        return "";
    }

    $params = array(
        'session' => $session,
        'matricule' => $ax_id,
        'auth' => $signature,
        'data' => $encrypted_data,
    );

    return rebuild_url($return_url, http_build_query($params));
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
