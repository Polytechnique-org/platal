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


/* Definitions for the OpenId Specification
 * http://openid.net/specs/openid-authentication-2_0.html
 *
 * OP Endpoint URL:             https://www.polytechnique.org/openid
 * OP Identifier:               https://www.polytechnique.org/openid
 * User Identifier:             https://www.polytechnique.org/openid/{hruid}
 * OP-Local Identifier:         {hruid}
 */

/* This implementation supports two modes:
 *     - entering the OP Identifier, which can simply be 'polytechnique.org'
 *     - entering the User Identifier, or some URL that resolves there
 * In both cases, Yadis discovery is made possible through the X-XRDS-Location
 * header.
 *
 * In the former case, Yadis discovery is performed on /, or where it redirects;
 * see platal.php. It resolves to the XRDS for this OP, and User Identifier
 * selection is performed after forcing the user to log in. This only works for
 * version 2.0 of the OpenId protocol.
 *
 * In the latter cas, Yadis discovery is performed on /openid/{hruid}. It
 * resolves ta a user-specific XRDS. This page also features HTML-based
 * discovery. This works with any version of the protocol.
 */

/* Testing suite is here:
 * http://openidenabled.com/resources/openid-test/
 * It only supports User Indentifiers.
 *
 * To test OP Identifiers, download the JanRain PHP library and use the
 * consumer provided as an example (although it appears that a failure is
 * mistakenly reported: 'Server denied check_authentication').
 * Reading the source of the server can also help understanding the code below.
 */

/* **checkid_immediate is not supported (yet)**, which means that we will
 * always ask for confirmation before redirecting to a third-party.
 * A sensible way to implement it would be to add a "Always trust this site"
 * checkbox to the form, and to store trusted websites per user. This still
 * raises the question of removing websites from that list.
 * Another possibility is to maintain a global whitelist.
 */

class OpenidModule extends PLModule
{
    function handlers()
    {
        return array(
            'openid'            => $this->make_hook('openid', AUTH_PUBLIC),
            'openid/trust'      => $this->make_hook('trust', AUTH_COOKIE),
            'openid/idp_xrds'   => $this->make_hook('idp_xrds', AUTH_PUBLIC),
            'openid/user_xrds'  => $this->make_hook('user_xrds', AUTH_PUBLIC),
            'openid/melix'      => $this->make_hook('melix', AUTH_PUBLIC),
        );
    }

    function handler_openid(&$page, $x = null)
    {
        $this->load('openid.inc.php');

        // Spec §4.1.2: if "openid.mode" is absent, whe SHOULD assume that
        // the request is not an OpenId message
        // Thus, we try to render the discovery page
        if (!array_key_exists('openid_mode', $_REQUEST)) {
            return $this->render_discovery_page($page, get_user($x));
        }

        // Now, deal with the OpenId message
        // Create a server and decode the request
        $server = init_openid_server();
        $request = $server->decodeRequest();

        // In modes 'checkid_immediate' and 'checkid_setup', the request
        // needs some logic and can not be automatically answered by the server

        // Immediate mode
        if ($request->mode == 'checkid_immediate') {

            // We deny immediate requests, unless:
            //   - the user identifier is known by the RP
            //   - the user is logged in
            //   - the user identifier matches the user logged in
            //   - the user and has whitelisted the site
            $answer = !$request->idSelect()
                      && S::logged()
                      && $request->identity == S::user()->login()
                      && is_trusted_site(S::user(), $request->trust_root);
            $response =& $request->answer($answer);

        // Setup mode
        } else if ($request->mode == 'checkid_setup') {

            // We redirect to a page where the user will authenticate
            // and confirm the use of his/her OpenId
            // Save request in session before jumping to confirmation page
            S::set('openid_request', serialize($request));
            pl_redirect('openid/trust');
            return;

        // Other requests can be automatically handled by the server
        } else {
            $response =& $server->handleRequest($request);
        }

        // Render response
        $webresponse =& $server->encodeResponse($response);
        $this->render_openid_response($webresponse);
    }

    function handler_trust(&$page, $x = null)
    {
        $this->load('openid.inc.php');

        // Recover request in session
        $request = S::v('openid_request');
        if (is_null($request)) {
            // There is no authentication information, something went wrong
            pl_redirect('/');
            return;
        }

        // Unserialize the request
        require_once 'Auth/OpenID/Server.php';
        $request = unserialize($request);

        $server = init_openid_server();
        $user = S::user();
        $identity = null;
        $claimed_id = null;

        // Set the identity to the user currently logged in
        // if an OP Identifier was initially used
        if ($request->identity == Auth_OpenID_IDENTIFIER_SELECT) {
            $identity = $user->hruid;
            $claimed_id = get_user_openid_url($user);
        // Check that the identity matches the user currently logged in
        // if an User Identifier was initially used
        } else if ($request->identity != $user->hruid) {
            $response =& $request->answer(false);
            $webresponse =& $server->encodeResponse($response);
            $this->render_openid_response($webresponse);
            return;
        }

        // Prepare Simple Registration response fields
        require_once 'Auth/OpenID/SReg.php';
        $sreg_request = Auth_OpenID_SRegRequest::fromOpenIDRequest($request);
        $sreg_response = Auth_OpenID_SRegResponse::extractResponse($sreg_request, get_sreg_data($user));

        // Check the whitelist
        $whitelisted = is_trusted_site($user, $request->trust_root);

        // Ask the user for confirmation
        if (!$whitelisted && $_SERVER['REQUEST_METHOD'] != 'POST') {
            $page->changeTpl('openid/trust.tpl');
            $page->assign('relying_party', $request->trust_root);
            $page->assign_by_ref('sreg_data', $sreg_response->data);
            return;
        }

        // At this point $_SERVER['REQUEST_METHOD'] == 'POST'
        // Answer to the Relying Party
        if ($whitelisted || isset($_POST['trust'])) {
            S::kill('openid_request');
            $response =& $request->answer(true, null, $identity, $claimed_id);

            // Add the simple registration response values to the OpenID
            // response message.
            $sreg_response->toMessage($response->fields);

        } else { // !$whitelisted && !isset($_POST['trust'])
            S::kill('openid_request');
            $response =& $request->answer(false);
        }

        // Generate a response to send to the user agent.
        $webresponse =& $server->encodeResponse($response);
        $this->render_openid_response($webresponse);
    }

    function handler_idp_xrds(&$page)
    {
        $this->load('openid.inc.php');

        // Set XRDS content-type and template
        header('Content-type: application/xrds+xml');
        $page->changeTpl('openid/idp_xrds.tpl', NO_SKIN);

        // Set variables
        $page->assign('type2', Auth_OpenID_TYPE_2_0_IDP);
        $page->assign('sreg', Auth_OpenID_SREG_URI);
        $page->assign('provider', get_openid_url());
    }

    function handler_user_xrds(&$page, $x = null)
    {
        $this->load('openid.inc.php');

        // Make sure the user exists
        $user = get_user($x);
        if (is_null($user)) {
            return PL_NOT_FOUND;
        }

        // Set XRDS content-type and template
        header('Content-type: application/xrds+xml');
        $page->changeTpl('openid/user_xrds.tpl', NO_SKIN);

        // Set variables
        $page->assign('type2', Auth_OpenID_TYPE_2_0);
        $page->assign('type1', Auth_OpenID_TYPE_1_1);
        $page->assign('sreg', Auth_OpenID_SREG_URI);
        $page->assign('provider', get_openid_url());
        $page->assign('local_id', $user->hruid);
    }

    function handler_melix(&$page, $x = null)
    {
        $this->load('openid.inc.php');
        $user = get_user_by_alias($x);

        // This will redirect to the canonic URL, which was not used
        // if this hook was triggered
        return $this->render_discovery_page(&$page, $user);
    }

    //--------------------------------------------------------------------//

    function render_discovery_page(&$page, $user)
    {

        // Show the documentation if this is not the OpenId page of an user
        if (is_null($user)) {
            pl_redirect('Xorg/OpenId');
        }

        // Redirect to the canonic URL if we are using an alias
        // There might be a risk of redirection loop here
        // if $_SERVER was not exactly what we expect
        $current_url = 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://'
                       . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $canonic_url = get_user_openid_url($user);
        if ($current_url != $canonic_url) {
            http_redirect($canonic_url);
        }

        // Include X-XRDS-Location response-header for Yadis discovery
        header('X-XRDS-Location: ' . get_user_xrds_url($user));

        // Select template
        $page->changeTpl('openid/openid.tpl');

        // Sets the title of the html page.
        $page->setTitle($user->fullName());

        // Sets the <link> tags for HTML-Based Discovery
        $page->addLink('openid.server openid2.provider', get_openid_url());
        $page->addLink('openid.delegate openid2.local_id', $user->hruid);

        // Adds the global user property array to the display.
        $page->assign_by_ref('user', $user);

        return;
    }

    function render_no_identifier_page($page, $request)
    {
        // Select template
        $page->changeTpl('openid/no_identifier.tpl');
    }

    function render_openid_response($webresponse)
    {
        // Send HTTP response code
        if ($webresponse->code != AUTH_OPENID_HTTP_OK) {
            header(sprintf("HTTP/1.1 %d ", $webresponse->code),
                   true, $webresponse->code);
        }

        // Send headers
        foreach ($webresponse->headers as $k => $v) {
            header("$k: $v");
        }
        header('Connection: close');

        // Send body
        print $webresponse->body;
        exit;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>