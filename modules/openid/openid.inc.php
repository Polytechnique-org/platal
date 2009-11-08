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

require_once 'Auth/OpenID/Discover.php';

// An helper class for using plat/al as an OpenId Identity Provider.
class OpenId
{
    private $base_url;        // Base url for all OpenId operations.
    private $spool_store;     // Location of the spool storage for OpenID.

    private $server = null;   // Auth::OpenId::Server object.
    private $request = null;  // Request extracted by the Server object.

    public function __construct()
    {
        global $globals;

        $this->base_url = $globals->baseurl . '/openid';
        $this->spool_store = $globals->spoolroot . '/spool/openid/store';
    }

    // Initializes an OpenId Server object; it will use a defined spool-based
    // directory to store OpenID secrets. Returns true on success.
    public function Initialize()
    {
        require_once 'Auth/OpenID/FileStore.php';
        require_once 'Auth/OpenID/Server.php';

        $store = new Auth_OpenID_FileStore($this->spool_store);
        $this->server = new Auth_OpenID_Server($store, $this->base_url);
        $this->request = $this->server->decodeRequest();

        return !is_a($this->request, 'Auth_OpenID_ServerError');
    }

    // Authorization logic helpers ---------------------------------------------

    // Returns true iff the current request is a valid openid request.
    public function IsOpenIdRequest()
    {
        return Env::has('openid_mode');
    }

    // Returns true iff the request needs to be handled directly by the calling
    // code (ie. the current user needs to be authorized).
    public function IsAuthorizationRequest()
    {
        return $this->request->mode == 'checkid_immediate' ||
               $this->request->mode == 'checkid_setup';
    }

    // Returns true iff the request requires an immediate answer (no user
    // interaction is allowed).
    public function IsImmediateRequest()
    {
        return $this->request->mode == 'checkid_immediate';
    }

    // Returns true iff the logged-in user is authorized for the current request.
    // It checks that the user is logged in, and has the authorization to use
    // that identity.
    public function IsUserAuthorized(User $user)
    {
        return $user && ($user->login() == $this->request->identity ||
            $this->request->idSelect());
    }

    // SimpleRegistration helpers ----------------------------------------------

    // Determines which SREG data are requested by the endpoint, and returns them.
    public function GetSRegDataForRequest(User &$user)
    {
        require_once 'Auth/OpenID/SReg.php';

        // Other common SReg fields we could fill are:
        //   dob, country, language, timezone.
        $sreg_request = Auth_OpenID_SRegRequest::fromOpenIDRequest($this->request);
        return Auth_OpenID_SRegResponse::extractResponse($sreg_request, array(
            'fullname' => $user->fullName(),
            'nickname' => $user->displayName(),
            'email'    => $user->bestEmail(),
            'gender'   => $user->isFemale() ? 'F' : 'M',
        ));
    }

    // Handling and answering helpers ------------------------------------------

    // Answers the current request, and renders the response. Appends the |sreg|
    // data when not null.
    public function AnswerRequest($is_authorized, $sreg_data = null)
    {
        // Creates the response.
        if ($is_authorized && $this->request->idSelect()) {
            $user = S::user();
            $response = $this->request->answer(
                $is_authorized, null, $user->login(), $this->GetUserUrl($user));
        } else {
            $response = $this->request->answer($is_authorized);
        }

        // Clobbers response, and get it back to the Relaying Party.
        if ($sreg_data) {
            $sreg_data->toMessage($response->fields);
        }
        $this->RenderResponse($response);
    }

    // Automatically handles the request without any user interaction.
    public function HandleRequest()
    {
        $response = $this->server->handleRequest($this->request);
        $this->RenderResponse($response);
    }

    // Trust management helpers ------------------------------------------------

    // Returns true iff the current endpoint is currently trusted by |user|.
    public function IsEndpointTrusted(User $user)
    {
        $res = XDB::query(
            "SELECT  COUNT(*)
               FROM  openid_trusted
              WHERE  (user_id = {?} OR user_id IS NULL) AND url = {?}",
            $user->id(), $this->request->trust_root);
        return ($res->fetchOneCell() > 0);
    }

    // Updates the trust level for the given endpoint, based on the value pf
    // |trusted| and |permanent_trust| (the latter is ignored when the former
    // value is false). Returns true iff the current endpoint is trusted.
    public function UpdateEndpointTrust(User &$user, $trusted, $permanent_trust) {
        $initial_trust = $this->IsEndpointTrusted($user);
        if (!$initial_trust && $trusted && $permanent_trust) {
            XDB::execute(
                "INSERT IGNORE INTO  openid_trusted
                                SET  user_id = {?}, url = {?}",
                $user->id(), $this->request->trust_root);
        }

        return ($initial_trust || $trusted);
    }

    // Page renderers ----------------------------------------------------------

    // Renders the OpenId discovery page for |user|.
    public function RenderDiscoveryPage(&$page, User &$user)
    {
        $page->changeTpl('openid/openid.tpl');
        $page->setTitle($user->fullName());
        $page->addLink('openid.server openid2.provider', $this->base_url);
        $page->addLink('openid.delegate openid2.local_id', $user->login());
        $page->assign_by_ref('user', $user);

        // Include the X-XRDS-Location header for Yadis discovery.
        header('X-XRDS-Location: ' . $this->GetUserXrdsUrl($user));
    }

    // Renders the main XRDS page.
    public function RenderMainXrdsPage(&$page)
    {
        pl_content_headers("application/xrds+xml");
        $page->changeTpl('openid/idp_xrds.tpl', NO_SKIN);
        $page->assign('type2', Auth_OpenID_TYPE_2_0_IDP);
        $page->assign('sreg', Auth_OpenID_SREG_URI);
        $page->assign('provider', $this->base_url);
    }

    // Renders the XRDS page of |user|.
    public function RenderUserXrdsPage(&$page, User &$user)
    {
        pl_content_headers("application/xrds+xml");
        $page->changeTpl('openid/user_xrds.tpl', NO_SKIN);
        $page->assign('type2', Auth_OpenID_TYPE_2_0);
        $page->assign('type1', Auth_OpenID_TYPE_1_1);
        $page->assign('sreg', Auth_OpenID_SREG_URI);
        $page->assign('provider', $this->base_url);
        $page->assign('local_id', $user->login());
    }

    // Renders the OpenId response for the HTTP client.
    public function RenderResponse($response)
    {
        if ($response) {
            $web_response = $this->server->encodeResponse($response);
            header(sprintf('%s %d', $_SERVER['SERVER_PROTOCOL'], $web_response->code),
                   true, $web_response->code);

            if (is_a($response, 'Auth_OpenID_ServerError')) {
                print "Erreur lors de l'authentification OpenId: " . $response->toString();
            } else {
                foreach ($web_response->headers as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }

                header('Connection: close');
                print $web_response->body;
            }
        }
        exit;
    }

    // URL providers -----------------------------------------------------------

    // Returns the OpenId identity URL of the requested user.
    private function GetUserUrl(User &$user)
    {
        return $this->base_url . '/' . $user->login();
    }

    // Returns the private XRDS page of a user.
    private function GetUserXrdsUrl(User &$user)
    {
        return $this->base_url . '/xrds/' . $user->login();
    }

    // Returns the endpoint in the current request.
    public function GetEndpoint()
    {
        return $this->request->trust_root;
    }

    // Extracts the OpenId arguments available in the current request, and
    // builds a query string with them.
    public function GetQueryStringForRequest()
    {
        foreach (Auth_OpenID::getQuery() as $key => $value) {
            if (strpos($key, 'openid.') === 0) {
                $args[$key] = $value;
            }
        }

        return http_build_query($args);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
