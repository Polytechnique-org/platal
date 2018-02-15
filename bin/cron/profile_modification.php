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

require_once 'connect.db.inc.php';
require_once 'plmailer.php';
global $globals;

$res = XDB::iterator('SELECT  p.hrpid, pm.pid, a.full_name, pm.field, pm.oldText, pm.newText, p.sex, pd.yourself, ap.uid
                        FROM  profile_modifications AS pm
                  INNER JOIN  accounts              AS a  ON (pm.uid = a.uid)
                  INNER JOIN  profiles              AS p  ON (pm.pid = p.pid)
                  INNER JOIN  profile_display       AS pd ON (pm.pid = pd.pid)
                  INNER JOIN  account_profiles      AS ap ON (pm.pid = ap.pid AND FIND_IN_SET(\'owner\', ap.perms))
                       WHERE  pm.type = \'third_party\' AND pm.field != \'deathdate\'
                    ORDER BY  pm.pid, pm.field, pm.timestamp');

if ($res->total() > 0) {
    $date = time();
    $values = $res->next();

    $pid = $values['pid'];
    $sex = ($values['sex'] == 'female') ? 1 : 0;
    $yourself = $values['yourself'];
    $user = User::getSilentWithUID($values['uid']);
    $hrpid = $values['hrpid'];
    $modifications = array();
    $modifications[] = array(
        'full_name' => $values['full_name'],
        'field'     => Profile::field_display($values['field']),
        'oldText'   => $values['oldText'],
        'newText'   => $values['newText'],
    );

    while ($values = $res->next()) {
        if ($values['pid'] != $pid) {
            $mailer = new PlMailer('profile/notification.mail.tpl');
            $mailer->addTo($user);
            $mailer->assign('modifications', $modifications);
            $mailer->assign('yourself', $yourself);
            $mailer->assign('hrpid', $hrpid);
            $mailer->assign('sex', $sex);
            $mailer->assign('date', $date);
            $mailer->send();
            $modifications = array();
        }
        $pid = $values['pid'];
        $sex = ($values['sex'] == 'female') ? 1 : 0;
        $yourself = $values['yourself'];
        $user = User::getSilentWithUID($values['uid']);
        $hrpid = $values['hrpid'];
        $modifications[] = array(
            'full_name' => $values['full_name'],
            'field'     => Profile::field_display($values['field']),
            'oldText'   => $values['oldText'],
            'newText'   => $values['newText'],
        );
    }
    $mailer = new PlMailer('profile/notification.mail.tpl');
    $mailer->addTo($user);
    $mailer->assign('modifications', $modifications);
    $mailer->assign('yourself', $yourself);
    $mailer->assign('hrpid', $hrpid);
    $mailer->assign('sex', $sex);
    $mailer->assign('date', $date);
    $mailer->send();

    XDB::execute('DELETE FROM  profile_modifications
                        WHERE  type = \'third_party\'');
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
