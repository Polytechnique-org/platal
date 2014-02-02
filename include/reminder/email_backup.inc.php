<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

class ReminderEmailBackup extends Reminder
{
    public function HandleAction($action)
    {
        if ($action == 'yes') {
            require_once 'emails.inc.php';
            Email::activate_storage($this->user, 'imap', Bogo::IMAP_DEFAULT);
            $this->UpdateOnYes();
        }

        if ($action == 'dismiss') {
            $this->UpdateOnDismiss();
        }

        if ($action == 'no') {
            $this->UpdateOnNo();
        }
    }

    public function text()
    {
        return "Tu peux bénéficier d'une sauvegarde des emails. Cela permet
            d'avoir un accès de secours aux 30 derniers jours d'emails reçus
            sur ton adresse Polytechnique.org.";
    }
    public function title()
    {
        return 'Sauvegarde de tes emails';
    }
    public function info()
    {
        return 'Xorg/IMAP';
    }

    public static function IsCandidate(User $user, $candidate)
    {
        if (!$user->checkPerms(User::PERM_MAIL)) {
            return false;
        }

        require_once 'emails.inc.php';
        $active = Email::is_active_storage($user, 'imap');
        if ($active) {
            Reminder::MarkCandidateAsAccepted($user->id(), $candidate);
        }
        return !$active;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
