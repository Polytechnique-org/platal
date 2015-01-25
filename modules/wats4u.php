<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

class Wats4uModule extends PLModule
{

    function handlers()
    {
        return array(
            'wats4u/sso'         => $this->make_hook('sso',      AUTH_PUBLIC, ''),
        );
    }

    function handler_sso($page)
    {
        $this->load('sso.inc.php');
        // First, perform security checks.
        if (!wats4u_sso_check()) {
            return PL_BAD_REQUEST;
        }

        global $globals;

        if (!S::logged()) {
            // Request auth.

            $page->assign('external_auth', true);
            $page->assign('ext_url', $globals->wats4u->public_url);
            $page->setTitle('Authentification');
            $page->setDefaultSkin('group_login');
            $page->assign('group', null);

            return PL_DO_AUTH;
        }

        if (!S::user()->checkPerms(PERMS_USER)) {
            // External (X.net) account
            return PL_FORBIDDEN;
        }

        // Update the last login information (unless the user is in SUID).
        $uid = S::i('uid');
        if (!S::suid()) {
            global $platal;
            S::logger($uid)->log('connexion_wats4u', $platal->path.' '.urldecode($_GET['url']));
        }

        // If we logged in specifically for this 'external_auth' request
        // and didn't want to "keep access to services", we kill the session
        // just before returning.
        // See classes/xorgsession.php:startSessionAs
        if (S::b('external_auth_exit')) {
            S::logger()->log('deconnexion', @$_SERVER['HTTP_REFERER']);
            Platal::session()->killAccessCookie();
            Platal::session()->destroy();
        }

        // Compute return URL
        $full_return = wats4u_sso_build_return_url(S::user());
        if ($full_return === "") {
            // Something went wrong
            $page->kill("Erreur dans le traitement de la requête Wats4U.");
        }

        http_redirect($full_return);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
