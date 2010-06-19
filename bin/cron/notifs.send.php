#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
require_once 'notifs.inc.php';

$uids = XDB::query('SELECT  uid
                      FROM  watch
                     WHERE  FIND_IN_SET(\'mail\', flags)
                  ORDER BY  uid');
$iterator = User::iterOverUIDs($uids->fetchColumn());

$mailer = new PlMailer('carnet/notif.mail.tpl');
while($user = $iterator->next()) {
    $watch = new Watch($user);
    if ($watch->count() > 0) {
        $notifs = $watch->events();
        $mailer->assign('sex', $user->isFemale());
        $mailer->assign('yourself', $user->display_name);
        $mailer->assign('week', date('W - Y'));
        $mailer->assign('notifs', $notifs);
        $mailer->sendTo($user);
        unset($notifs);
    }
    unset($watch);
    unset($user);
}

XDB::execute("UPDATE  watch_profile
                 SET  ts = NOW()
               WHERE  field = 'broken'");
XDB::execute('DELETE FROM  watch_profile
                    WHERE  ts < DATE_SUB(CURRENT_DATE, INTERVAL 15 DAY)');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
