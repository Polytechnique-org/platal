#!/usr/bin/php5 -q
<?php

require 'connect.db.inc.php';

$message = '';

$res = XDB::iterRow("SELECT  al.alias, pd.promo, e.email  //a.alias, u.promo, email
                       FROM  accounts AS a
                 INNER JOIN  account_profile AS ap ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
                 INNER JOIN  profile_display AS pd ON (ap.pid = pd.pid)
                  LEFT JOIN  aliases         AS al ON (a.uid =a.uid AND al.type = 'a_vie')
                  LEFT JOIN  emails          AS e  ON (a.uid = e.uid AND NOT FIND_IN_SET('filter', e.flags))
                      WHERE  a.registration_date > {?}
                   GROUP BY  al.alias
                   ORDER BY  pd.promo",
       date("Ymd000000", strtotime('last Monday')));
if ($count = $res->total()) {
    $message .= "$count INSCRIPTIONS CONFIRMÉES :\n";
    while (list($forlife, $promo, $email) = $res->next()) {
	      $message .= "$promo, $forlife, $email\n";
    }
}

$res = XDB::iterRow("SELECT  a.hruid, r.email, r.date
                       FROM  register_pending AS r
                 INNER JOIN  accounts         AS a ON (a.uid = r.uid)
                      WHERE  r.hash != 'INSCRIT'
                   ORDER BY  r.date");
if ($count = $res->total()) {
    $message .= "\n$count INSCRIPTIONS NON CONFIRMÉES :\n";
    while (list($forlife, $email, $date) = $res->next()) {	
	      $message .= "$date, $forlife,\n            $email\n";
    }
}

$res = XDB::query('SELECT  COUNT(DISTINCT uid), COUNT(*)
                     FROM  register_marketing');
list($a, $b) = $res->fetchOneRow();
$message .= "\nINSCRIPTIONS SOLICITÉES :\n";
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
