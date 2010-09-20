#!/usr/bin/php5 -q
<?php

require 'connect.db.inc.php';

$message = '';

$res = XDB::iterRow("SELECT  a.registration_date, a.hruid, e.email
                       FROM  accounts AS a
                 INNER JOIN  account_profiles AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
                 INNER JOIN  profile_display  AS pd ON (ap.pid = pd.pid)
                  LEFT JOIN  emails           AS e  ON (a.uid = e.uid AND NOT FIND_IN_SET('filter', e.flags))
                      WHERE  a.registration_date > {?}
                   GROUP BY  a.hruid
                   ORDER BY  pd.promo",
       date("Ymd000000", strtotime('last Monday')));
if ($count = $res->total()) {
    $message .= "$count INSCRIPTIONS CONFIRMÉES CETTE SEMAINE :\n";
    while (list($date, $hruid, $email) = $res->next()) {
	      $message .= "$date, $hruid, $email\n";
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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
