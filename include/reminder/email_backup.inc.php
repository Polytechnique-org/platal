<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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
            $user = S::user();
            $storage = new EmailStorage($user, 'imap');
            $storage->activate();

            $this->UpdateOnYes();
        }

        if ($action == 'dismiss') {
            $this->UpdateOnDismiss();
        }

        if ($action == 'no') {
            $this->UpdateOnNo();
        }
    }

    protected function GetDisplayText()
    {
        return "Tu peux bénéficier d'une sauvegarde des emails. Cela permet
            d'avoir un accès de secours aux 30 derniers jours d'emails reçus
            sur ton adresse Polytechnique.org.";
    }

    public static function IsCandidate(User &$user)
    {
        require_once 'emails.inc.php';
        $storage = new EmailStorage($user, 'imap');
        return $storage->active;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
