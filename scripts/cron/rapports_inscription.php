#!/usr/bin/php4 -q
<?php
require('./connect.db.inc.php');
$MESSAGE = '';

// ---------------------------------------

$result=mysql_query("SELECT  a.alias, u.promo, email
                       FROM  ins_confirmees AS i
		  LEFT JOIN  auth_user_md5  AS u ON( i.id = u.user_id )
		  LEFT JOIN  aliases        AS a ON( i.id=a.id AND a.type='a_vie' )
		  LEFT JOIN  emails         AS e ON( e.uid = u.user_id AND NOT FIND_IN_SET('filter',e.flags) )
		   GROUP BY  alias
		   ORDER BY  promo");
$a = mysql_num_rows($result);
if ($a > 0) {
    $MESSAGE.="$a INSCRIPTIONS CONFIRMEES:\n";
    while (list($usern,$promo,$mail) = mysql_fetch_row($result)) {
	$MESSAGE.="X$promo, $usern, $mail\n";
    }
}
mysql_free_result($result);

// ---------------------------------------

$result=mysql_query("SELECT  ins_id,username,promo,email,date
		       FROM  en_cours
		      WHERE  loginbis != 'INSCRIT'
		   ORDER BY  date");
$b = mysql_num_rows($result);
if ($b > 0) {
    $MESSAGE.="\n$b INSCRIPTIONS NON CONFIRMEES:\n";
    while (list($code,$usern,$prom,$mail,$quand) = mysql_fetch_row($result)) {	
	$MESSAGE.="$quand, X$prom, $usern\n$mail";
	$MESSAGE.="\n";
	$MESSAGE.="http://www.polytechnique.org/step4.php?ref=$code\n";
    }
}
mysql_free_result($result);

// ---------------------------------------

$result=mysql_query("SELECT  nom, prenom, promo, email, DATE_FORMAT(date_envoi,'%d/%m/%Y')
                       FROM  envoidirect 
		      WHERE  date_succes = ''
		   ORDER BY  date_envoi DESC, promo, nom");
$c = mysql_num_rows($result);
if ($c > 0) {
    $MESSAGE.="\n$c INSCRIPTIONS SOLICITÉEs:\n";
    while (list($nom,$prenom,$promo,$mail,$date) = mysql_fetch_row($result)) {
	$MESSAGE.="$date: X$promo, $nom $prenom,\t$mail\n";
    }
}
mysql_free_result($result);

// ---------------------------------------

$MESSAGE=$MESSAGE."\n\n";

// envoi du mail à register@
$HEADER="From: register\nReply-To: register@polytechnique.org\n".
	"X-Mailer: PHP/" . phpversion()."\n".
        "Mime-Version: 1.0\n".
        "Content-Type: text/plain; charset=iso-8859-1\n".
        "Content-Disposition: inline\n".
        "Content-Transfer-Encoding: 8bit\n";
mail("register@polytechnique.org, jean-michel.yolin+register@polytechnique.org","$a confirmées, $b en attente et $c sollicitées",$MESSAGE,$HEADER);
mysql_query("DELETE FROM ins_confirmees");
?>
