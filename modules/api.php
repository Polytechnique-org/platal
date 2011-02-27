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

class ApiModule extends PlModule
{
    function handlers()
    {
        return array(
            // TODO(vzanotti): Extend the plat/al engine to support placeholders
            // in handler urls, for instance "api/1/user/%forlife/isRegistered".
            'api/1/user'   => $this->make_api_hook('user', AUTH_COOKIE, 'api_user_readonly'),
            'api/1/search' => $this->make_api_hook('search', AUTH_COOKIE),
        );
    }

    // This handler supports the following URL patterns:
    //   /api/1/user/{forlife}/isRegistered
    function handler_user(PlPage $page, PlUser $authUser, $payload, $user = null, $selector = null)
    {
        // Retrieve the PlUser referenced in the request. Note that this is the
        // target user, not the authenticated user.
        $user = PlUser::getSilent($user);
        if (empty($user)) {
            return PL_NOT_FOUND;
        }

        if ($selector == 'isRegistered') {
            $page->jsonAssign('isRegistered', $user->isActive());
            return PL_JSON;
        } else {
            return PL_NOT_FOUND;
        }
    }

    function handler_search(PlPage $page, PlUser $authUser, $payload, $mode = 'quick')
    {
        if (!isset($payload['quick'])) {
            $page->trigError('Malformed search query');
            return PL_BAD_REQUEST;
        }
        if (strlen(trim($payload['quick'])) < 3) {
            $page->jsonAssign('profile_count', -1);
            $page->jsonAssign('profiles', array());
            return PL_JSON;
        }
        Env::set('quick', $payload['quick']);
        foreach (array('with_soundex', 'exact') as $key) {
            if (isset($payload[$key])) {
                Env::set($key, $payload[$key]);
            }
        }

        require_once 'userset.inc.php';
        $view = new QuickSearchSet();
        $view->addMod('json', 'JSon', true, $payload);
        $view->apply('api/1/search', $page, 'json');
        return PL_JSON;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
