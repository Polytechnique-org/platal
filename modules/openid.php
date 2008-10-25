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
        );
    }

    function handler_openid(&$page, $x = null)
    {
        // Determines the user whose openid we are going to display
        if (is_null($x)) {
            return PL_NOT_FOUND;
        }

        $login = S::logged() ? User::get($x) : User::getSilent($x);
        if (!$login) {
            return PL_NOT_FOUND;
        }

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

}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
