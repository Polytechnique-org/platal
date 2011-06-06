<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

function select_if_homonym(PlUser $user)
{
    return XDB::fetchOneCell('SELECT  email
                                FROM  email_source_account
                               WHERE  uid = {?} AND expire IS NOT NULL',
                             $user->id());
}

function send_warning_homonym(PlUser $user, $email)
{
    $cc = "validation+homonyme@" . Platal::globals()->mail->domain;
    $from = "\"Support Polytechnique.org\" <$cc>";
    $body = Post::has('mailbody') ? Post::t('mailbody') : get_warning_mail_text($user, $email);

    $mymail = new PlMailer();
    $mymail->setFrom($from);
    $mymail->addCc($cc);
    $mymail->setSubject("Dans 1 semaine, suppression de $email@" . $user->mainEmailDomain());
    $mymail->setTxtBody($body);
    $mymail->sendTo($user);
}

function send_robot_homonym(PlUser $user, $email)
{
    $cc = "validation+homonyme@" . Platal::globals()->mail->domain;
    $from = "\"Support Polytechnique.org\" <$cc>";
    $body = Post::has('mailbody') ? Post::t('mailbody') : get_robot_mail_text($user, $email);
    $user = User::getSilentWithUID($user->id());

    $mymail = new PlMailer();
    $mymail->setFrom($from);
    $mymail->setSubject("Mise en place du robot $email@" . $user->mainEmailDomain());
    $mymail->addCc($cc);
    $mymail->setTxtBody($body);
    $mymail->sendTo($user);
}

function get_warning_mail_text(PlUser $user, $email)
{
    $display_name = $user->displayName();
    $former_email = $email . '@' . $user->mainEmailDomain();
    $forlife_email = $user->forlifeEmail();

    return "$display_name,\n\nComme nous t'en avons informé par email il y a "
       . "quelques temps, pour respecter nos engagements en terme d'adresses "
       . "email devinables, tu te verras bientôt retirer l'alias $former_email "
       . "pour ne garder que $forlife_email.\n\nToute personne qui écrira à "
       . "$former_email recevra la réponse d'un robot qui l'informera que "
       . "$former_email est ambigu pour des raisons d'homonymie et signalera "
       . "ton email exact.\n\nCordialement,\n-- \nl'équipe de Polytechnique.org"
       . "\nLe portail des élèves & anciens élèves de l'École polytechnique";
}

function get_robot_mail_text(PlUser $user, $email)
{
    $display_name = $user->displayName();
    $former_email = $email . '@' . $user->mainEmailDomain();

    return "$display_name,\n\nComme nous t'en avons informé par email il y a "
        . "quelques temps, nous t'avons retiré de façon définitive l'adresse "
        . "$former_email.\n\nToute personne qui écrit à $former_email reçoit "
        . "la réponse d'un robot qui l'informe que $former_email est ambigu "
        . "pour des raisons d'homonymie et indique ton email exact.\n\nTu peux "
        . "faire l'essai toi-même en écrivant à $former_email.\n\nCordialement,"
        . "\n-- \nl'équipe de Polytechnique.org"
        . "\nLe portail des élèves & anciens élèves de l'École polytechnique";
}

function fix_homonym(PlUser $user, $email)
{
    XDB::execute('DELETE FROM  email_source_account
                        WHERE  email = {?} AND type = \'alias\'',
                 $email);

    $hrmid = User::makeHomonymHrmid($email);
    XDB::execute('INSERT INTO  email_source_other (hrmid, email, domain, type, expire)
                       SELECT  {?}, {?}, id, \'homonym\', NOW()
                         FROM  email_virtual_domains
                        WHERE  name = {?}',
                 $hrmid, $email, $user->mainEmailDomain());
    XDB::execute("INSERT INTO  email_redirect_other (hrmid, redirect, type, action)
                       VALUES  ({?}, '', 'homonym', 'homonym')",
                 $hrmid);


    require_once 'emails.inc.php';
    fix_bestalias($user);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
