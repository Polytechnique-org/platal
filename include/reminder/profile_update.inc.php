<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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

class ReminderProfileUpdate extends Reminder
{
    public function HandleAction($action)
    {
        switch ($action) {
          case 'dismiss':
            $this->UpdateOnDismiss();
            break;

          case 'profile':
            $this->UpdateOnDismiss();
            pl_redirect('profile/edit');
            break;

          case 'photo':
            $this->UpdateOnDismiss();
            pl_redirect('photo/change');
            break;

          case 'geoloc':
            $this->UpdateOnDismiss();
            pl_redirect('profile/edit/adresses');
            break;
        }
    }

    public function Prepare(&$page)
    {
        parent::Prepare($page);
        $profile = $this->user->profile();

        $page->assign('profile_incitation', $profile->is_old);
        $page->assign('profile_last_update', $profile->last_change);
        $page->assign('photo_incitation', !$profile->has_photo);
        $page->assign('geocoding_incitation', Geocoder::countNonGeocoded($profile->id()));
    }

    public function template()
    {
        return 'reminder/profile_update.tpl';
    }
    public function title()
    {
        return "Mise à jour de ton profil";
    }
    public function warning()
    {
        return true;
    }

    public static function IsCandidate(User &$user, $candidate)
    {
        $profile = $user->profile();
        if (!$profile) {
            return false;
        }
        return !$profile->has_photo || $profile->is_old;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
