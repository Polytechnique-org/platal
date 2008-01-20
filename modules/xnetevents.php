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

define('NB_PER_PAGE', 25);

class XnetEventsModule extends PLModule
{
    function handlers()
    {
        return array(
            '%grp/events'       => $this->make_hook('events',  AUTH_MDP),
            '%grp/events/sub'   => $this->make_hook('sub',     AUTH_MDP),
            '%grp/events/csv'   => $this->make_hook('csv',     AUTH_MDP, 'user', NO_HTTPS),
            '%grp/events/ical'  => $this->make_hook('ical',    AUTH_MDP, 'user', NO_HTTPS),
            '%grp/events/edit'  => $this->make_hook('edit',    AUTH_MDP, 'groupadmin'),
            '%grp/events/admin' => $this->make_hook('admin',   AUTH_MDP, 'groupmember'),
        );
    }

    function handler_events(&$page, $group)
    {
        $page->changeTpl('xnetevents/index.tpl');
        require_once dirname(__FILE__) . '/xnetevents/xnetevents.inc.php';
        $events = XNetEvent::listEvents($group == 'prepare', $group == 'archive');
    }

    function handler_sub(&$page, $eid = null)
    {
    }

    function handler_csv(&$page, $eid = null, $item_id = null)
    {
    }

    function handler_ical(&$page, $eid = null)
    {
    }

    function handler_edit(&$page, $eid = null)
    {
    }

    function handler_admin(&$page, $eid = null, $item_id = null)
    {
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
