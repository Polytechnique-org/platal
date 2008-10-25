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

class OpenidModule extends PLModule
{
    function handlers()
    {
        return array(
            'openid'    => $this->make_hook('openid', AUTH_PUBLIC),
            'openid/idp_xrds'   => $this->make_hook('idp_xrds', AUTH_PUBLIC),
            'openid/user_xrds'  => $this->make_hook('user_xrds', AUTH_PUBLIC),
            'openid/trust'  => $this->make_hook('trust', AUTH_PUBLIC),
        );
    }

    function init_openid()
    {
        require_once 'Auth/OpenID.php';
        $this->load('openid.inc.php');
    }

    function handler_openid(&$page, $x = null)
    {
        global $globals;

        // Determines the user whose openid we are going to display
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        $login = S::logged() ? User::get($x) : User::getSilent($x);
        if (!$login) {
            return PL_NOT_FOUND;
        }

        // Include X-XRDS-Location response-header for Yadis discovery
        $user_xrds = $globals->baseurl . 'openid/user_xrds/' . $login->hruid;
        header('X-XRDS-Location: ' . $user_xrds);

        // Select template
        $page->changeTpl('openid/openid.tpl');

        // Sets the title of the html page.
        $page->setTitle($login->fullName());

        // Sets the <link> tags for HTML-Based Discovery
        $page->addLink('openid.server openid2.provider',
                       $globals->baseurl . '/openid');
        $page->addLink('openid.delegate openid2.local_id',
                       $login->hruid);

        // Adds the global user property array to the display.
        $page->assign_by_ref('user', $login);
    }

    function handler_idp_xrds(&$page)
    {
        global $globals;

        // Load constants
        require_once "Auth/OpenID/Discover.php";

        // Set XRDS content-type and template
        header('Content-type: application/xrds+xml');
        $page->changeTpl('openid/idp_xrds.tpl', NO_SKIN);

        // Set variables
        $page->changeTpl('openid/idp_xrds.tpl', NO_SKIN);
        $page->assign('type', Auth_OpenID_TYPE_2_0_IDP);
        $page->assign('uri', $globals->baseurl . '/openid');
    }

    function handler_user_xrds(&$page, $x = null)
    {
        global $globals;

        // Load constants
        require_once "Auth/OpenID/Discover.php";

        // Set XRDS content-type and template
        header('Content-type: application/xrds+xml');
        $page->changeTpl('openid/user_xrds.tpl', NO_SKIN);

        // Set variables
        $page->assign('type1', Auth_OpenID_TYPE_2_0);
        $page->assign('type2', Auth_OpenID_TYPE_1_1);
        $page->assign('uri', $globals->baseurl . '/openid');
    }

}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
