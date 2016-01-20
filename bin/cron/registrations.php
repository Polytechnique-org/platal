#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

require 'connect.db.inc.php';

$message = '';

$res = XDB::iterRow("SELECT  DATE(a.registration_date), a.hruid, GROUP_CONCAT(DISTINCT r.redirect SEPARATOR ', ')
                       FROM  accounts               AS a
                 INNER JOIN  account_profiles       AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
                 INNER JOIN  profile_display        AS pd ON (ap.pid = pd.pid)
                  LEFT JOIN  email_source_account   AS s  ON (a.uid = s.uid)
                  LEFT JOIN  email_redirect_account AS r  ON (a.uid = r.uid)
                      WHERE  a.registration_date > {?}
                   GROUP BY  a.hruid
                   ORDER BY  pd.promo",
       date("Ymd000000", strtotime('last Monday')));
if ($count = $res->total()) {
    $message .= "$count INSCRIPTIONS CONFIRMÉES CETTE SEMAINE :\n";
    while (list($date, $hruid, $email, $redirect) = $res->next()) {
	      $message .= "$date, $hruid, $email, $redirect\n";
    }
}

$res = XDB::iterRow("SELECT  r.date, a.hruid, r.email
                       FROM  register_pending AS r
                 INNER JOIN  accounts         AS a ON (a.uid = r.uid)
                 INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
                 INNER JOIN  profile_display  AS pd ON (ap.pid = pd.pid)
                      WHERE  r.hash != 'INSCRIT'
                   GROUP BY  a.hruid
                   ORDER BY  pd.promo");
if ($count = $res->total()) {
    $message .= "\n$count INSCRIPTIONS NON CONFIRMÉES :\n";
    while (list($date, $hruid, $email) = $res->next()) {	
	      $message .= "$date, $hruid, $email\n";
    }
}

$res = XDB::query('SELECT  COUNT(DISTINCT uid), COUNT(*)
                     FROM  register_marketing');
list($a, $b) = $res->fetchOneRow();
$message .= "\nINSCRIPTIONS SOLLICITÉES :\n";
$message .= "    $a utilisateurs\n    $b adresses email\n";

$message .= "\n\n";

$mailer = new PlMailer();
$mailer->setSubject('Rapport des inscriptions de la semaine');
$mailer->setFrom($globals->register->notif);
$mailer->addTo($globals->register->notif);
$mailer->setTxtBody($message);
$mailer->send();

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
