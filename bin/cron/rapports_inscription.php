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
        "SELECT  ins_id,username,promo,email,date
           FROM  en_cours
          WHERE  loginbis != 'INSCRIT'
       ORDER BY  date");
if ($b = $res->total()) {
    $MESSAGE.="\n$b INSCRIPTIONS NON CONFIRMEES:\n";
    while (list($code,$usern,$prom,$mail,$quand) = $res->next()) {	
	$MESSAGE.="$quand, X$prom, $usern\n$mail";
	$MESSAGE.="\n";
	$MESSAGE.="http://www.polytechnique.org/step4.php?ref=$code\n";
    }
}

// ---------------------------------------

$res = $globals->xdb->iterRow(
        'SELECT  nom, prenom, promo, email, DATE_FORMAT(date_envoi,"%d.%m.%Y")
           FROM  envoidirect 
     INNER JOIN  auth_user_md5 USING(matricule)
          WHERE  date_succes = ""
       ORDER BY  date_envoi DESC, promo, nom');
if ($c = $res->total()) {
    $MESSAGE.="\n$c INSCRIPTIONS SOLICITÉEs:\n";
    while (list($nom,$prenom,$promo,$mail,$date) = $res->next()) {
	$MESSAGE.="$date: X$promo, $nom $prenom,\t$mail\n";
    }
}

// ---------------------------------------

$MESSAGE=$MESSAGE."\n\n";

require_once('diogenes.hermes.inc.php');
$mailer = new HermesMailer();
$mailer->setSubject("$a confirmées, $b en attente et $c sollicitées");
$mailer->setFrom('register@polytechnique.org');
$mailer->addTo('register@polytechnique.org');
$mailer->addCc('jean-michel.yolin+register@polytechnique.org');
$mailer->setTxtBody($MESSAGE);
$mailer->send();
?>
