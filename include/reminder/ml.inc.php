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

class ReminderMl extends Reminder
{
    public function HandleAction($action)
    {
        switch ($action) {
          case 'suscribe':
            S::assert_xsrf_token();
            $subs = array_keys(Post::v('sub_ml'));

            $res = XDB::iterRow("SELECT  sub, domain
                                   FROM  register_subs
                                  WHERE  uid = {?} AND type = 'list'
                               ORDER BY  domain",
                                S::i('uid'));
            while (list($sub, $domain) = $res->next()) {
                if (array_shift($subs) == "$sub@$domain") {
                    MailingList::subscribeTo($sub, $domain);
                }
            }

            $this->UpdateOnYes();
            pl_redirect('lists');
            break;

          case 'dismiss':
            $this->UpdateOnDismiss();
            break;

          case 'no':
            $this->UpdateOnNo();
            break;
        }
    }

    public function Prepare($page)
    {
        parent::Prepare($page);

        $res = XDB::iterRow("SELECT  sub, domain
                               FROM  register_subs
                              WHERE  uid = {?} AND type = 'list'
                           ORDER BY  domain",
                            S::i('uid'));
        $lists = array();
        while (list($sub, $domain) = $res->next()) {
            $mlist = new MailingList($sub, $domain);
            list($details, ) = $mlist->getMembers();
            $lists["$sub@$domain"] = $details;
        }
        $page->assign_by_ref('lists', $lists);
    }

    public function template()
    {
        return 'reminder/ml.tpl';
    }
    public function title()
    {
        return "Inscription aux listes de diffusion";
    }

    public static function IsCandidate(User $user, $candidate)
    {
        $res = XDB::query("SELECT  COUNT(*) AS lists
                             FROM  register_subs
                            WHERE  uid = {?} AND type = 'list'",
                          $user->id());

        $mlCount = $res->fetchOneCell();
        if (!$mlCount) {
            Reminder::MarkCandidateAsAccepted($user->id(), $candidate);
        }
        return ($mlCount > 0);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
