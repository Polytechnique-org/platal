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
$count_mail = 0;
$texte = "";
$liste = "";

$iterator = $pf->iterProfiles();

while($profile = $iterator->next()) {
    // We do not want to send more than max_send_per_min emails a minute.
    if ($limit > $globals->mail->max_send_per_min) {
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
    // A profile is considered recent if the last change happened less than 6 months ago.
    $mailer->assign('recent_update', $profile->last_change > date('Y-m-d', -180*24*60*60));

    //TODO check if the user subscribed to the promo ML
    // $listClient = new MMList(S::user());
    // $mlists = $listClient->get_all_user_lists($user->forlifeEmail()); // Problem here because the cron does not have any plat/al permissions.
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
    $promoGroup = false;

    foreach ($groups as $group_id=>$data) {
        if (($data['nom'] !== 'Test') && ($data['cat'] == Group::CAT_GROUPESX)) {
            $groupx[$group_id] = $data['nom'];
        }
        if ($data['cat'] == Group::CAT_BINETS) {
            $binets[$group_id] = $data['nom'];
        }
        if (($profile->mainEducation() == 'X') && ($data['cat'] == Group::CAT_PROMOTIONS)) {
            $promoGroup = $promoGroup || ($data['diminutif'] == $profile->yearpromo());
        }
    }
    $group = '';
    if (count($groupx)) {
        $group = $groupx[array_rand($groupx)];
    } elseif (count($binets)) {
        $group = $binets[array_rand($binets)];
    }
    $mailer->assign('group', $group);
    $mailer->assign('promoGroup', $promoGroup);
    //In testing phase, only send to Polytechnique.org members
    if ($user->inGroup(Group::get('Polytechnique.org')->id)) {
        $mailer->sendTo($user);

        // We want to keep trace of the emails sent
        $liste .= $user->fullName(true) . "\n";
        $count_mail += 1;
    }

    unset($mailer);
    unset($user);
}

$texte = "L'email anniversaire a bien été envoyé à {$count_mail} destinataires.\n\n"
        ."La liste des destinataires pour le " . date("d/m/Y") . " est la suivante :\n"
        . $liste;

$mailer = new PlMailer();
$mailer->addTo("holiners@staff.polytechnique.org");
$mailer->setFrom('"Webmaster Polytechnique.org" <web@polytechnique.org>');
$mailer->setSubject("Envoi de l'email anniversaire");
$mailer->setTxtBody($texte);
$mailer->send();

unset($mailer);
unset($count_mail);
unset($liste);
unset($pf);
unset($limit);
unset($texte);
unset($liste);
unset($iterator);
unset($profile);
unset($mlpromo);
unset($groups);
unset($groupx);
unset($binets);
unset($group);

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
