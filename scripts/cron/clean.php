#!/usr/bin/php4 -q
<?php
require("../../include/xorg.misc.inc.php");
require("../../include/config.xorg.inc.php");
mysql_connect($globals->dbhost,$globals->dbuser,$globals->dbpwd);
mysql_select_db($globals->dbdb);

function query ($sql) {
    mysql_query($sql);
    if (mysql_errno() != 0) {
	echo "error in \"$sql\" :\n", mysql_error(),"\n";
    }
}

// la table en_cours est nettoyée
query("DELETE FROM en_cours WHERE TO_DAYS(NOW()) - TO_DAYS(date) >= 365");
query("delete from en_cours where loginbis = 'INSCRIT'");

// la table envoidirect est nettoyée
query("update envoidirect set uid = CONCAT('+',uid) where uid not like '+%' and date_succes != 0");

// quelques tables sont triées pour que la lecture triée soit plus facile
query("alter table nationalites order by text");
query("alter table applis_def order by text");
query("alter table binets_def order by text");
query("alter table groupesx_def order by text");
query("alter table secteur order by text");
query("alter table sections order by text");

// on regarde si qqun a fait bcp de requêtes dans l'annuaire, puis on remete à 0
//$res = mysql_query("SELECT nom,prenom,promo,nb_recherches FROM auth_user_md5 as u INNER JOIN nb_recherches as r ON(u.user_id = r.uid) WHERE r.nb_recherches > 90 AND u.perms != 'admin' order by r.nb_recherches");
//while (list($n, $p, $pr, $nbr) = mysql_fetch_row($res))
//    echo $n." ".$p.", X".$pr." : ".$nbr." recherches dans l'annuaire !\n";
//query("UPDATE nb_recherches SET nb_recherches = 0");

?>
