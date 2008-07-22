#!/usr/bin/php5 -q
<?php
require('./connect.db.inc.php');
$MESSAGE = '';

// ---------------------------------------

$res = XDB::iterRow(
        "SELECT  a.alias, u.promo, email
           FROM  auth_user_md5  AS u
      LEFT JOIN  aliases        AS a ON( u.user_id=a.id AND a.type='a_vie' )
      LEFT JOIN  emails         AS e ON( e.uid = u.user_id AND NOT FIND_IN_SET('filter',e.flags) )
          WHERE  u.date_ins > {?}
       GROUP BY  alias
       ORDER BY  promo", date("Ymd000000", strtotime ("last Monday")));
if ($a = $res->total()) {
    $MESSAGE.="$a INSCRIPTIONS CONFIRMEES:\n";
    while (list($usern,$promo,$mail) = $res->next()) {
	$MESSAGE.="X$promo, $usern, $mail\n";
    }
}

// ---------------------------------------

$res = XDB::iterRow(
        "SELECT  forlife, email, date
           FROM  register_pending
          WHERE  hash != 'INSCRIT'
       ORDER BY  date");
if ($b = $res->total()) {
    $MESSAGE.="\n$b INSCRIPTIONS NON CONFIRMEES:\n";
    while (list($usern, $mail, $quand) = $res->next()) {	
	$MESSAGE.="$quand, $usern,\n            $mail\n";
    }
}

// ---------------------------------------

$res = XDB::query('SELECT COUNT(DISTINCT uid), COUNT(*) FROM register_marketing');
list($a, $b) = $res->fetchOneRow();
$MESSAGE .= "\n$c INSCRIPTIONS SOLICITÉES :\n";
$MESSAGE .= "    $a utilisateurs\n    $b adresses email\n";

// ---------------------------------------

$MESSAGE .= "\n\n";

require_once('../../classes/plmailer.php');
$mailer = new PlMailer();
$mailer->setSubject("$a confirmées, $b en attente et $c sollicitées");
$mailer->setFrom($globals->register->notif);
$mailer->addTo($globals->register->notif);
$mailer->setTxtBody($MESSAGE);
$mailer->send();
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
