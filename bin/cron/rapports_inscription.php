#!/usr/bin/php4 -q
<?php
require('./connect.db.inc.php');
$MESSAGE = '';

// ---------------------------------------

$res = $globals->xdb->iterRow(
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

$res = $globals->xdb->iterRow(
        "SELECT  hash, forlife, email, date
           FROM  register_pending
          WHERE  hash != 'INSCRIT'
       ORDER BY  date");
if ($b = $res->total()) {
    $MESSAGE.="\n$b INSCRIPTIONS NON CONFIRMEES:\n";
    while (list($code, $usern, $mail, $quand) = $res->next()) {	
	$MESSAGE.="$quand, $usern,\n            $mail";
	$MESSAGE.="\n";
	$MESSAGE.="https://www.polytechnique.org/register/end.php?hash=$code\n";
    }
}

// ---------------------------------------

$res = $globals->xdb->query('SELECT COUNT(DISTINCT uid), COUNT(*) FROM register_marketing');
list($a, $b) = $res->fetchOneRow();
$MESSAGE .= "\n$c INSCRIPTIONS SOLICITÉES :\n";
$MESSAGE .= "    $a utilisateurs\n    $b adresses mails\n";

// ---------------------------------------

$MESSAGE .= "\n\n";

require_once('diogenes/diogenes.hermes.inc.php');
$mailer = new HermesMailer();
$mailer->setSubject("$a confirmées, $b en attente et $c sollicitées");
$mailer->setFrom('register@polytechnique.org');
$mailer->addTo('register@polytechnique.org');
$mailer->addCc('jean-michel.yolin+register@polytechnique.org');
$mailer->setTxtBody($MESSAGE);
$mailer->send();
?>
