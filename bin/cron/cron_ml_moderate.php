#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require('./connect.db.inc.php');
ini_set('max_execution_time', '75');
$sent_mails = 0;
$handler    = time();

while ($sent_mails < $globals->lists->max_mail_per_min
       && time() - $handler < 60) {
    // take a lock on a mail
    XDB::execute("UPDATE  ml_moderate
                     SET  handler = {?}
                   WHERE  handler IS NULL
                ORDER BY  ts
                LIMIT  1", $handler);
    if (XDB::affectedRows() == 0) {
        break;
    }
    $query = XDB::query("SELECT  nom, prenom, user_id, password,
                                 ml, domain, mid, action, message
                           FROM  auth_user_md5 AS u
                     INNER JOIN  ml_moderate AS ml ON (u.user_id = ml.uid)
                          WHERE  ml.handler = {?}", $handler);
    list($nom, $prenom, $uid, $password, $list, $domain, $mid, $action, $reason) = $query->fetchOneRow();

    // build the client
    $client = new MMList($uid, $password, $domain);

    // send the mail
    $mail    = $client->get_pending_mail($list, $mid);
    list($det,$mem,$own) = $client->get_members($list);
    $count = 0;
    switch ($action) {
      case 'accept':
        $action = 1;    /** 1 = ACCEPT **/
        $subject = "Message accepté";
        $append  = "a été accepté par $prenom $nom.\n";
        $count += count($mem) + count($own);
        break;
      case 'refuse':
        $action = 2;    /** 2 = REJECT **/
        $subject = "Message refusé";
        $append  = "a été refusé par $prenom $nom avec la raison :\n\n" . $reason;
        $count += count($own) + 1;
        break;
      case 'delete':
        $action = 3;    /** 3 = DISCARD **/
        $subject = "Message supprimé";
        $append  = "a été supprimé par $prenom $nom.\n\n"
                 . "Rappel: il ne faut utiliser cette opération "
                 . "que dans le cas de spams ou de virus !\n";
        $count += count($own);
        break;
    }

    if ($client->handle_request($list, $mid, $action, $reason)) {
        $sent_mails += $count;
        $texte = "le message suivant :\n\n"
               . "    Auteur: {$mail['sender']}\n"
               . "    Sujet : « {$mail['subj']} »\n"
               . "    Date  : ".strftime("le %d %b %Y à %H:%M:%S", (int)$mail['stamp'])."\n\n"
               . $append;
        $mailer = new PlMailer();
        $mailer->addTo("$list-owner@{$domain}");
        $mailer->setFrom("$list-bounces@{$domain}");
        $mailer->addHeader('Reply-To', "$list-owner@{$domain}");
        $mailer->setSubject($subject);
        $mailer->setTxtBody($texte);
        $mailer->send();
    }

    // release the lock
    XDB::execute("DELETE FROM ml_moderate WHERE handler = {?}",
                 $handler);
    sleep(60 * $count / $globals->lists->max_mail_per_min);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
