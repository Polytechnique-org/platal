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

class ReminderEmailWarning extends Reminder
{
    public function HandleAction($action)
    {
        if ($action == 'dismiss') {
            $this->UpdateOnDismiss();
        }
    }

    public function template()
    {
        return 'reminder/email_warning.tpl';
    }
    public function title()
    {
        return "Problème avec ta redirections d'emails";
    }
    public function warning()
    {
        return true;
    }

    public static function IsCandidate(User $user, $candidate)
    {
        if (!$user->checkPerms(User::PERM_MAIL)) {
            return false;
        }

        return count(S::v('mx_failures', array())) > 0;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
