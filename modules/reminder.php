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

class ReminderModule extends PLModule
{
    function handlers()
    {
        return array(
            'ajax/reminder' => $this->make_hook('reminder', AUTH_COOKIE, 'user'),
        );
    }

    function handler_reminder($page, $reminder_name = null, $action = null)
    {
        require_once 'reminder.inc.php';
        $user = S::user();

        // If no reminder name was passed, or if we don't know that reminder name,
        // just drop the request.
        if (!$reminder_name ||
            !($reminder = Reminder::GetByName($user, $reminder_name))) {
            return PL_NOT_FOUND;
        }

        // Otherwise, the request is dispatched, and a new reminder, if any, is
        // displayed.
        $reminder->HandleAction($action);

        $previous_reminder = $reminder->title();

        if (($new_reminder = Reminder::GetCandidateReminder($user))) {
            $new_reminder->DisplayStandalone($page, $previous_reminder);
        } else {
            $reminder->NotifiesAction($page);
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
