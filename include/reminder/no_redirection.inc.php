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

class ReminderNoRedirection extends Reminder
{
    public function HandleAction($action)
    {
        if ($action == 'dismiss') {
            $this->UpdateOnDismiss();
        }
    }

    public function template()
    {
        return 'reminder/no_redirection.tpl';
    }
    public function title()
    {
        return "Problème avec ta redirection d'emails";
    }
    public function warning()
    {
        return true;
    }
    public function info()
    {
        return 'Xorg/MesAdressesDeRedirection';
    }

    public static function IsCandidate(User &$user, $candidate)
    {
        return S::v('no_redirect');
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
