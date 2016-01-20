<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

class ReminderGapps extends Reminder
{
    public function HandleAction($action)
    {
        switch ($action) {
          case 'yes':
            $this->UpdateOnDismiss();
            pl_redirect('googleapps');
            break;

          case 'dismiss':
            $this->UpdateOnDismiss();
            break;

          case 'no':
            $this->UpdateOnNo();
            break;
        }
    }

    public function template()
    {
        return 'reminder/gapps.tpl';
    }
    public function title()
    {
        return "Création d'un compte Google Apps";
    }
    public function info()
    {
        return 'Xorg/GoogleApps';
    }

    public static function IsCandidate(User $user, $candidate)
    {
        if (!$user->checkPerms(User::PERM_MAIL)) {
            return false;
        }

        require_once 'googleapps.inc.php';
        $isSubscribed = GoogleAppsAccount::account_status($user->id());
        if ($isSubscribed == 'disabled') {
            $isSubscribed = false;
        }
        if ($isSubscribed) {
            Reminder::MarkCandidateAsAccepted($user->id(), $candidate);
        }
        return !$isSubscribed;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
