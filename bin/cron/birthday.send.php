#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

require_once 'connect.db.inc.php';
require_once 'plmailer.php';
ini_set('memory_limit', '128M');

// This cron sends a birthday email once a year to all users who have a profile.
// It is useful technically (for 'unsubscribed users' to get at least one broken level a year) ;
// and for the community management (it is very easy to unsubscribe and then difficult tu subscribe again).
$pf = new ProfileFilter(new PFC_And(
                new UFC_Birthday('=', time()),
                new UFC_HasValidEmail(),
                new PFC_Not(new UFC_Dead())
                ));
$limit = 0;

$iterator = $pf->iterProfiles();

$pids = XDB::query('SELECT  pid
                      FROM  profiles
                      WHERE pid = 42318
                  ORDER BY  pid');
$iterator = Profile::iterOverPIDs($pids->fetchColumn());

while($profile = $iterator->next()) {
    // We do not want to send more than BATCH_SIZE emails a minute.
    if ($limit > NLIssue::BATCH_SIZE) {
        sleep(60);
        $limit = 0;
    }
    $limit += 1;

    $user = $profile->owner();
    $mailer = new PlMailer('profile/birthday.mail.tpl');

    // This is a social email so we want to use several info on the user to be specific.
    $mailer->assign('sex', $user->isFemale());
    $mailer->assign('yourself', $user->display_name);
    $mailer->assign('groups', $user->groups());
    $mailer->assign('isX', $profile->mainEducation() == 'X');
    $mailer->assign('promoX', $profile->yearpromo());
    $mailer->assign('hrid', $profile->hrid());
    $mailer->assign('recent_update', $profile->last_change > date('Y-m-d', -180*24*60*60));
    // $listClient = new MMList(S::user());
    // $mlists = $listClient->get_all_user_lists($user->forlifeEmail());
    $mlpromo = false;
    // foreach ($mlists as $mlist) {
    //    $mlpromo = $mlpromo || ($mlist.addr == 'promo@' . $promoX . '.polytechnique.org');
    // }
    $mailer->assign('ml_promo', $mlpromo);
    $mailer->assign('nlAX', NewsLetter::forGroup(NewsLetter::GROUP_AX)->subscriptionState($user));
    $mailer->assign('nlXorg', NewsLetter::forGroup(NewsLetter::GROUP_XORG)->subscriptionState($user));
    // We are going to pick up a random Groupe X (preferably) or Binet from the $user.
    $groups = $user->groups();
    $groupx = array();
    $binets = array();
    foreach ($groups as $group_id=>$data) {
        if ($data['cat'] == Group::CAT_GROUPESX) {
            $groupx[$id] = $data['nom'];
        }
        if ($data['cat'] == Group::CAT_BINETS) {
            $binets[$id] = $data['nom'];
        }
    }
    if (count($groupx)) {
        $group = $groupx[array_rand($groupx)];
    } elseif (count($binets)) {
        $group = $binets[array_rand($binets)];
    }
    $mailer->assign('group', $group);
    $mailer->sendTo($user);
    unset($mailer);
    unset($user);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
