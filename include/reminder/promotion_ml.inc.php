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

class ReminderPromotionMl extends Reminder
{
    public function HandleAction($action)
    {
        $user = S::user();
        switch ($action) {
          case 'yes':
            XDB::execute('INSERT IGNORE INTO  group_members (uid, asso_id)
                                      SELECT  {?}, id
                                        FROM  groups
                                       WHERE  diminutif = {?}',
                         $user->id(), $user->profile()->yearPromo());
            MailingList::subscribePromo($user->profile()->yearPromo());

            $this->UpdateOnYes();
            break;

          case 'dismiss':
            $this->UpdateOnDismiss();
            break;

          case 'no':
            $this->UpdateOnNo();
            break;
        }
    }

    public function text()
    {
        return "La liste de diffusion de ta promotion permet de recevoir les
            informations plus spécifiques de ta promotion pour pouvoir
            participer plus facilement aux événements qu'elle organise. Tu
            seras aussi inscrit dans le groupe de la promotion " .
            $this->user->promo() . '.';
    }
    public function title()
    {
        return "Inscription à la liste de diffusion de ta promotion";
    }

    public static function IsCandidate(User $user, $candidate)
    {
        $profile = $user->profile();
        if (!$profile) {
            return false;
        }

        // We only test if the user is in her promotion group for it is too
        // expensive to check if she is in the corresponding ML as well.
        $res = XDB::query('SELECT  COUNT(*)
                             FROM  group_members
                            WHERE  uid = {?} AND asso_id = (SELECT  id
                                                              FROM  groups
                                                             WHERE  diminutif = {?})',
                          $user->id(), $user->profile()->yearPromo());
        $mlCount = $res->fetchOneCell();
        if ($mlCount) {
            Reminder::MarkCandidateAsAccepted($user->id(), $candidate);
        }
        if ($mlCount == 0) {
            $mlist = MailingList::promo($user->profile()->yearPromo());
            try {
                $mlist->getMembersLimit(0, 0);
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
