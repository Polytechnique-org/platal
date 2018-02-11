#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2018 Polytechnique.org                              *
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

require './connect.db.inc.php';
ini_set('max_execution_time', '75');
ini_set('memory_limit', '128M');
$sent_mails = 0;
$handler    = time();

/* Cleanup dead locks */
XDB::execute('UPDATE  email_list_moderate
                 SET  handler = NULL
               WHERE  handler < NOW() - 300');

while ($sent_mails < $globals->lists->max_mail_per_min
       && time() - $handler < 60) {
    // take a lock on a mail
    XDB::execute('UPDATE  email_list_moderate
                     SET  handler = {?}
                   WHERE  handler IS NULL
                ORDER BY  ts
                LIMIT  1', $handler);
    if (XDB::affectedRows() == 0) {
        break;
    }
    $query = XDB::query('SELECT  uid, ml, domain, mid, action, message
                           FROM  email_list_moderate
                          WHERE  handler = {?}', $handler);
    list($uid, $list, $domain, $mid, $action, $reason) = $query->fetchOneRow();
    $user = User::get($uid);
    $fullname = $user->fullName();
    $mlist = new MailingList($list, $domain, $user);

    // send the mail
    $mail = $mlist->getPendingMail($mid);
    list($det,$mem,$own) = $mlist->getMembers();
    $count = 0;
    switch ($action) {
      case 'accept':
        $action = MailingList::REQ_ACCEPT;
        $subject = "Message accepté";
        $append  = "a été accepté par $fullname.\n";
        $type = 'nonspam';
        $count += count($mem) + count($own);
        break;
      case 'refuse':
        $action = MailingList::REQ_REJECT;
        $subject = "Message refusé";
        $append  = "a été refusé par $fullname avec la raison :\n\n" . $reason;
        $type = 'nonspam';
        $count += count($own) + 1;
        break;
      case 'delete':
        $action = MailingList::REQ_DISCARD;
        $subject = "Message supprimé";
        $append  = "a été supprimé par $fullname.\n\n"
                 . "Rappel : il ne faut utiliser cette opération "
                 . "que dans le cas de spams ou de virus !\n";
        $type = 'spam';
        $count += count($own);
        break;
    }

    // if the mail was classified as Unsure, feed bogo
    $raw_mail = html_entity_decode($mlist->getPendingMail($mid, 1));
    // search for the X-Spam-Flag header
    $end_of_headers = strpos($raw_mail, "\r\n\r\n");
    if ($end_of_headers === false) {   // sometimes headers are separated by \n
        $end_of_headers = strpos($raw_mail, "\n\n");
    }
    $x_spam_flag = '';
    if (preg_match('/^X-Spam-Flag: ([a-zA-Z]+), tests=bogofilter/m', substr($raw_mail, 0, $end_of_headers + 1), $matches)) {
        $x_spam_flag = $matches[1];
    }
    if ($x_spam_flag == 'Unsure') {
        $mailer = new PlMailer();
        $mailer->addTo($type . '@' . $globals->mail->domain);
        $mailer->setFrom('"' . $fullname . '" <web@' . $globals->mail->domain . '>');
        $mailer->setTxtBody($type . ' soumis par ' . $fullname . ' via la modération de la liste ' . $list . '@' . $domain);
        $mailer->addAttachment($raw_mail, 'message/rfc822', $type . '.mail', false);
        $mailer->send();
    }

    // send feedback to the mailing list owners
    if ($mlist->handleRequest($action, $mid, $reason)) {
        $sent_mails += $count;
        $texte = "Le message suivant :\n\n"
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
    XDB::execute('DELETE FROM  email_list_moderate
                        WHERE  handler = {?}',
                 $handler);
    sleep(60 * $count / $globals->lists->max_mail_per_min);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
