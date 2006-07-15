<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

require_once dirname(__FILE__).'/lists.php';

class XnetListsModule extends ListsModule
{
    var $client;

    function handlers()
    {
        return array(
            'grp/lists'           => $this->make_hook('lists',     AUTH_MDP),
            'grp/lists/create'    => $this->make_hook('create',    AUTH_MDP),

            'grp/lists/members'   => $this->make_hook('members',   AUTH_COOKIE),
            'grp/lists/trombi'    => $this->make_hook('trombi',    AUTH_COOKIE),
            'grp/lists/archives'  => $this->make_hook('archives',  AUTH_COOKIE),

            'grp/lists/moderate'  => $this->make_hook('moderate',  AUTH_MDP),
            'grp/lists/admin'     => $this->make_hook('admin',     AUTH_MDP),
            'grp/lists/options'   => $this->make_hook('options',   AUTH_MDP),
            'grp/lists/delete'    => $this->make_hook('delete',    AUTH_MDP),

            'grp/lists/soptions'  => $this->make_hook('soptions',  AUTH_MDP),
            'grp/lists/check'     => $this->make_hook('check',     AUTH_MDP),

            /* hack: lists uses that */
            'profile' => $this->make_hook('profile', AUTH_PUBLIC),
        );
    }

    function prepare_client(&$page)
    {
        global $globals;

        require_once 'lists.inc.php';

        $this->client =& lists_xmlrpc(Session::getInt('uid'),
                                      Session::get('password'),
                                      $globals->asso('mail_domain'));

        $page->useMenu();
        $page->assign('asso', $globals->asso());
        $page->setType($globals->asso('cat'));
    }

    function handler_profile(&$page, $user = null)
    {
        redirect('https://www.polytechnique.org/profile/'.$user);
    }
}

?>
