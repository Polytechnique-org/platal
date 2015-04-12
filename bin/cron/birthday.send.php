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

$pids = XDB::query('SELECT  pid
                      FROM  profiles
                     WHERE  birthday BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                  ORDER BY  pid');
$iterator = Profile::iterOverPIDs($pids->fetchColumn());

while($profile = $iterator->next()) {
    $user = $profile->owner();
    $mailer = new PlMailer('profile/birthday.mail.tpl');
    $mailer->assign('sex', $user->isFemale());
    $mailer->assign('yourself', $user->display_name);
    $mailer->assign('groups', $user->groups());
    $mailer->assign('isX', $profile->mainEducation() == 'X');
    $mailer->assign('promoX', $profile->promo());
    $mailer->assign('hrid', $profile->hrid());
    $mailer->assign('recent_update', $profile->last_change > date('Y-m-d', -180*24*60*60));
    $listClient = new MMList(S::user());
    $mlists = $listClient->get_all_user_lists($user->forlifeEmail());
    $mlpromo = false;
    foreach ($mlists as $mlist) {
        $mlpromo = $mlpromo || ($mlist.addr == 'promo@' . $promoX . '.polytechnique.org');
    }
    $mailer->assign('ml_promo', $mlpromo);
    $mailer->assign('nlAX', NewsLetter::forGroup(NewsLetter::GROUP_AX)->subscriptionState($user));
    $mailer->assign('nlXorg', NewsLetter::forGroup(NewsLetter::GROUP_XORG)->subscriptionState($user));
    $req = XDB::fetchOneCell('
                    SELECT a.nom
                      FROM group_members AS m
                INNER JOIN groups AS a ON(m.asso_id = a.id)
                     WHERE m.uid = {?} AND a.status = 'active'
                  GROUP BY m.asso_id ORDER BY RAND() LIMIT 1'
                  , $user->id());
    $mailer->assign('group', $group);
    $mailer->sendTo($user);
    unset($mailer);
    unset($user);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
