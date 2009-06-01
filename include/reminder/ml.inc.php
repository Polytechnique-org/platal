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

class ReminderMl extends Reminder
{
    public function HandleAction($action)
    {
        switch ($action) {
          case 'suscribe':
            S::assert_xsrf_token();
            $subs = array_keys(Post::v('sub_ml'));
            $current_domain = null;

            $res = XDB::iterRow("SELECT  sub, domain
                                   FROM  register_subs
                                  WHERE  uid = {?} AND type = 'list'
                               ORDER BY  domain",
                                S::i('uid'));
            while (list($sub, $domain) = $res->next()) {
                if (array_shift($subs) == "$sub@$domain") {
                    list($sub, $domain) = explode('@', $list);
                    if ($domain != $current_domain) {
                        $current_domain = $domain;
                        $client = new MMList(S::v('uid'), S::v('password'), $domain);
                    }
                    $client->subscribe($sub);
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

    protected function GetDisplayText() {}

    public function Display(&$page)
    {
        header('Content-Type: text/html; charset=utf-8');
        $page->changeTpl('reminder/ml.tpl', NO_SKIN);
        $page->assign('baseurl', $this->GetBaseUrl());

        $res = XDB::iterRow("SELECT  sub, domain
                               FROM  register_subs
                              WHERE  uid = {?} AND type = 'list'
                           ORDER BY  domain",
                            S::i('uid'));
        $current_domain = null;
        $lists = array();
        while (list($sub, $domain) = $res->next()) {
            if ($current_domain != $domain) {
                $current_domain = $domain;
                $client = new MMList(S::v('uid'), S::v('password'), $domain);
            }
            list($details, ) = $client->get_members($sub);
            $lists["$sub@$domain"] = $details;
        }
        $page->assign_by_ref('lists', $lists);
    }

    public static function IsCandidate(User &$user, $candidate)
    {
        $res = XDB::execute("SELECT  COUNT(*) AS lists
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
