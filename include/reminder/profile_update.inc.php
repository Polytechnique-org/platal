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

        $res = XDB::query('SELECT  date < DATE_SUB(NOW(), INTERVAL 365 DAY) AS is_profile_old,
                                   date AS profile_date, LENGTH(p.attach) > 0 AS has_photo
                             FROM  auth_user_md5     AS u
                        LEFT JOIN  photo             AS p ON (u.user_id = p.uid)
                            WHERE  user_id = {?}',
                          $this->user->id());
        list($is_profile_old, $profile_date, $has_photo) = $res->fetchOneRow();

        $page->assign('profile_incitation', $is_profile_old);
        $page->assign('profile_last_update', $profile_date);
        $page->assign('photo_incitation', !$has_photo);

        $res = XDB::query('SELECT  COUNT(*)
                             FROM  profile_addresses
                            WHERE  pid = {?} AND accuracy = 0',
                          $this->user->id());
        $page->assign('geocoding_incitation', $res->fetchOneCell());
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
        $res = XDB::query('SELECT  date < DATE_SUB(NOW(), INTERVAL 365 DAY) AS is_profile_old,
                                   p.attach AS photo
                             FROM  auth_user_md5 AS u
                        LEFT JOIN  photo         AS p ON (u.user_id = p.uid)
                            WHERE  user_id = {?}',
                          $user->id());
        list($is_profile_old, $has_photo) = $res->fetchOneRow();

        $res = XDB::query('SELECT  COUNT(*)
                             FROM  profile_addresses
                            WHERE  pid = {?} AND accuracy = 0',
                          $user->id());

        return ($res->fetchOneCell() || !$has_photo || $is_profile_old);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
