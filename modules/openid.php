<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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


class OpenidModule extends PLModule
{
    function handlers()
    {
        return array(
            'openid'               => $this->make_hook('openid',        AUTH_PUBLIC),
            'openid/melix'         => $this->make_hook('melix',         AUTH_PUBLIC),
            'openid/xrds'          => $this->make_hook('xrds',          AUTH_PUBLIC),
            'openid/trust'         => $this->make_hook('trust',         AUTH_PASSWD, 'user'),
            'openid/trusted'       => $this->make_hook('trusted',       AUTH_PASSWD, 'user'),
            'admin/openid/trusted' => $this->make_hook('admin_trusted', AUTH_PASSWD, 'admin'),
        );
    }

    function handler_openid($page, $login = null)
    {
        $this->load('openid.inc.php');
        $requested_user = User::getSilent($login);
        $server = new OpenId();

        // Spec §4.1.2: if "openid.mode" is absent, we SHOULD assume that
        // the request is not an OpenId message.
        if (!$server->IsOpenIdRequest()) {
            if ($requested_user) {
                $server->RenderDiscoveryPage($page, $requested_user);
                return;
            } else {
                pl_redirect('Xorg/OpenId');
            }
            exit;
        }

        // Initializes the OpenId environment from the request.
        $server->Initialize();

        // In modes 'checkid_immediate' and 'checkid_setup', we need to check
        // by ourselves that we want to allow the user to be authenticated.
        // Otherwise it can simply be forwarded to the Server object.
        if ($server->IsAuthorizationRequest()) {
            $authorized = S::logged() &&
                $server->IsUserAuthorized(S::user()) &&
                $server->IsEndpointTrusted(S::user());

            if ($authorized) {
                // TODO(vzanotti): SReg requests are currently not honored if
                // the website is already trusted. We may want to redirect SReg
                // requests to /openid/trust, to allow the user to choose.
                $server->AnswerRequest(true);
            } else if ($server->IsImmediateRequest()) {
                $server->AnswerRequest(false);
            } else {
                // The user is currently not authorized to get her authorization
                // request approved. Two possibilities:
                //  * the endpoint is not yet trusted => redirect to openid/trust
                //  * the user is not logged in => log in the user.
                //
                // The second case requires a special handling when the request
                // was POSTed, as our current log in mechanism does not preserve
                // POST arguments.
                $openid_args = $server->GetQueryStringForRequest();
                if (S::logged()) {
                    pl_redirect('openid/trust', $openid_args);
                } else if (Post::has('openid_mode')) {
                    pl_redirect('openid', $openid_args);
                } else {
                    return PL_DO_AUTH;
                }
            }
        } else {
            $server->HandleRequest();
        }

        // All requests should have been answered at this point. The best here
        // is to get the user back to a safe page.
        pl_redirect('');
    }

    function handler_melix($page, $login = null)
    {
        $this->load('openid.inc.php');

        global $globals;
        $melix = ($login ? $login . '@' . $globals->mail->alias_dom : null);

        if ($melix && ($requested_user = User::getSilent($melix))) {
            $server = new OpenId();
            $server->RenderDiscoveryPage($page, $requested_user);
        } else {
            pl_redirect('Xorg/OpenId');
        }
    }

    function handler_xrds($page, $login = null)
    {
        $this->load('openid.inc.php');
        $requested_user = User::getSilent($login);
        $server = new OpenId();

        if (!$login) {
            $server->RenderMainXrdsPage($page);
        } else if ($requested_user) {
            $server->RenderUserXrdsPage($page, $requested_user);
        } else {
            return PL_NOT_FOUND;
        }
    }

    function handler_trust($page)
    {
        $this->load('openid.inc.php');
        $server = new OpenId();
        $user = S::user();

        // Initializes the OpenId environment from the request.
        if (!$server->Initialize() || !$server->IsAuthorizationRequest()) {
            $page->kill("Ta requête OpenID a échoué, merci de réessayer.");
        }

        // Prepares the SREG data, if any is required.
        $sreg_response = $server->GetSRegDataForRequest($user);

        // Asks the user about her trust level of the current request, if not
        // done yet.
        if (!Post::has('trust_accept') && !Post::has('trust_cancel')) {
            $page->changeTpl('openid/trust.tpl');
            $page->assign('openid_query', $server->GetQueryStringForRequest());
            $page->assign('relying_party', $server->GetEndpoint());
            $page->assign('sreg_data', $sreg_response->contents());

            return;
        }

        // Interprets the form results, and updates the user whitelist.
        S::assert_xsrf_token();
        $trusted = $server->UpdateEndpointTrust(
            $user,
            Post::b('trust_accept') && !Post::b('trust_cancel'),
            Post::b('trust_always'));

        // Finally answers the request.
        if ($server->IsUserAuthorized($user) && $trusted) {
            $server->AnswerRequest(true, Post::b('trust_sreg') ? $sreg_response : null);
        } else {
            $server->AnswerRequest(false);
        }
    }

    function handler_trusted($page, $action = 'list', $id = null)
    {
        $page->setTitle('Sites tiers de confiance');
        $page->assign('title', 'Mes sites tiers de confiance pour OpenId');
        $table_editor = new PLTableEditor('openid/trusted', 'account_auth_openid', 'id');
        $table_editor->set_where_clause(XDB::format('uid = {?}',  S::user()->id()));
        $table_editor->vars['uid']['display_list'] = false;
        $table_editor->vars['uid']['display_item'] = false;
        $table_editor->describe('url', 'site tiers', true);
        $page->assign('deleteonly', true);
        $table_editor->apply($page, $action, $id);
    }

    function handler_admin_trusted($page, $action = 'list', $id = null)
    {
        $page->setTitle('Sites tiers de confiance');
        $page->assign('title', 'Sites tiers de confiance globaux pour OpenId');
        $table_editor = new PLTableEditor('admin/openid/trusted', 'account_auth_openid', 'id');
        $table_editor->set_where_clause('uid IS NULL');
        $table_editor->vars['uid']['display_list'] = false;
        $table_editor->vars['uid']['display_item'] = false;
        $table_editor->describe('url', 'site tiers', true);
        $page->assign('readonly', true);
        $table_editor->apply($page, $action, $id);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
