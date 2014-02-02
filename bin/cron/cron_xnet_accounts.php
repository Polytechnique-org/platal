#!/usr/bin/php5 -q
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

require_once 'connect.db.inc.php';
require_once 'plmailer.php';
$limit = 60;

$users = XDB::fetchAllAssoc('SELECT  a.uid, a.hruid, r.hash, r.group_name, r.sender_name, r.email
                               FROM  register_pending_xnet AS r
                         INNER JOIN  accounts              AS a ON (r.uid = a.uid)
                              WHERE  a.state = \'disabled\'
                           ORDER BY  r.date, a.uid');

$mailer = new PlMailer('xnet/account.mail.tpl');
$mailer->addCc('validation+xnet_account@polytechnique.org');

$i = 0;
foreach ($users as $user) {
    $mailer->setTo($user['email']);
    $mailer->assign('hash', $user['hash']);
    $mailer->assign('email', $user['email']);
    $mailer->assign('group', $user['group_name']);
    $mailer->assign('sender_name', $user['sender_name']);
    $mailer->assign('again', false);
    $mailer->assign('baseurl', Platal::globals()->baseurl);
    $mailer->send();

    XDB::execute('UPDATE  accounts
                     SET  state = \'pending\'
                   WHERE  uid = {?}',
                 $user['uid']);

    if ($i == $limit) {
        $i = 0;
        sleep(60);
    } else {
        ++$i;
    }
}


// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
