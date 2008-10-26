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
 * User-Supplied Identifier:    https://www.polytechnique.org/openid/{$hruid}
 *    Identity selection is not supported by this implementation
 * OP-Local Identifier:         {$hruid}
 */

/* Testing suite is here:
 * http://openidenabled.com/resources/openid-test/
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
        );
    }

    function handler_openid(&$page, $x = null)
    {
        $this->load('openid.inc.php');
        $user = get_user($x);

        // Spec §4.1.2: if "openid.mode" is absent, whe SHOULD assume that
        // the request is not an OpenId message
        // Thus, we try to render the discovery page
        if (!array_key_exists('openid_mode', $_REQUEST)) {
            return $this->render_discovery_page($page, $user);
        }

        // Create a server and decode the request
        $server = init_openid_server();
        $request = $server->decodeRequest();

        // This request requires user interaction
        if (in_array($request->mode,
                     array('checkid_immediate', 'checkid_setup'))) {

            // Each user has only one identity to choose from
            // So we can make automatically the identity selection
            if ($request->idSelect()) {
                $request->identity = get_user_openid_url($user);
            }

            // If we still don't have an identifier (used or desired), give up
            if (!$request->identity) {
                $this->render_no_identifier_page($page, $request);
                return;
            }

            // We always require confirmation before sending information
            // to third-party websites
            if ($request->immediate) {
                $response =& $request->answer(false);
            } else {
                // Save request in session and jump to confirmation page
                S::set('request', serialize($request));
                pl_redirect('openid/trust');
                return;
            }

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
        $request = S::v('request');
        if (is_null($request)) {
            // There is no authentication information, something went wrong
            pl_redirect('/');
            return;
        } else {
            // Unserialize the request
            require_once 'Auth/OpenID/Server.php';
            $request = unserialize($request);
        }

        $server = init_openid_server();
        $user = S::user();

        // Check that the identity matches the user currently logged in
        if ($request->identity != get_user_openid_url($user)) {
            $response =& $request->answer(false);
            $webresponse =& $server->encodeResponse($response);
            $this->render_openid_response($webresponse);
            return;
        }

        // Ask the user for confirmation
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $page->changeTpl('openid/trust.tpl');
            $page->assign('relying_party', $request->trust_root);
            return;
        }

        // At this point $_SERVER['REQUEST_METHOD'] == 'POST'
        // Answer to the Relying Party based on the user's choice
        if (isset($_POST['trust'])) {
            unset($_SESSION['request']);
            $response =& $request->answer(true, null, $request->identity);

            // Answer with some sample Simple Registration data.
            // TODO USE REAL USER DATA FROM $user
            $sreg_data = array(
                               'fullname' => 'Example User',
                               'nickname' => 'example',
                               'dob' => '1970-01-01',
                               'email' => 'invalid@example.com',
                               'gender' => 'F',
                               'postcode' => '12345',
                               'country' => 'ES',
                               'language' => 'eu',
                               'timezone' => 'America/New_York');

            // Add the simple registration response values to the OpenID
            // response message.
            require_once 'Auth/OpenID/SReg.php';
            $sreg_request = Auth_OpenID_SRegRequest::fromOpenIDRequest($request);
            $sreg_response = Auth_OpenID_SRegResponse::extractResponse($sreg_request, $sreg_data);
            $sreg_response->toMessage($response->fields);

        } else { // !isset($_POST['trust'])
            unset($_SESSION['request']);
            $response =& $request->answer(false);
        }

        // Generate a response to send to the user agent.
        $webresponse =& $server->encodeResponse($response);
        $this->render_openid_response($webresponse);
    }

    function handler_idp_xrds(&$page)
    {
        // Load constants
        $this->load('openid.inc.php');

        // Set XRDS content-type and template
        header('Content-type: application/xrds+xml');
        $page->changeTpl('openid/idp_xrds.tpl', NO_SKIN);

        // Set variables
        $page->changeTpl('openid/idp_xrds.tpl', NO_SKIN);
        $page->assign('type', Auth_OpenID_TYPE_2_0_IDP);
        $page->assign('uri', get_openid_url());
    }

    function handler_user_xrds(&$page, $x = null)
    {
        // Load constants
        $this->load('openid.inc.php');

        // Set XRDS content-type and template
        header('Content-type: application/xrds+xml');
        $page->changeTpl('openid/user_xrds.tpl', NO_SKIN);

        // Set variables
        $page->assign('type1', Auth_OpenID_TYPE_2_0);
        $page->assign('type2', Auth_OpenID_TYPE_1_1);
        $page->assign('uri', get_openid_url());
    }

    //--------------------------------------------------------------------//

    function render_discovery_page(&$page, $user)
    {

        // Show the documentation if this is not the OpenId page of an user
        if (is_null($user)) {
            pl_redirect('Xorg/OpenId');
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
