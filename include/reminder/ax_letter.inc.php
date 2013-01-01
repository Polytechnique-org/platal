<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
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

class ReminderAxLetter extends Reminder
{
    public function HandleAction($action)
    {
        if ($action == 'yes') {
            require_once 'newsletter.inc.php';
            NewsLetter::forGroup(NewsLetter::GROUP_AX)->subscribe();
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
        return "La lettre de l'AX te permet de recevoir régulièrement les
            informations importantes de l'AX.";
    }
    public function title()
    {
        return "Inscription à la lettre de l'AX";
    }
    public function info()
    {
        return 'Xorg/MailsAX';
    }

    public static function IsCandidate(User $user, $candidate)
    {
        require_once 'newsletter.inc.php';
        $isSubscribed = NewsLetter::forGroup(NewsLetter::GROUP_AX)->subscriptionState();
        if ($isSubscribed) {
            Reminder::MarkCandidateAsAccepted($user->id(), $candidate);
        }
        return !$isSubscribed;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
