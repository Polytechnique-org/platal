<?php
require("../../include/xorg.misc.inc.php");
require("../../include/config.xorg.inc.php");

mysql_connect($globals->dbhost,$globals->dbuser,$globals->dbpwd);
mysql_select_db($globals->dbdb);

$result = mysql_query("SELECT matricule,nom1,nom2,nom3,prenom1,prenom2,promo FROM recherche");

while ($row = mysql_fetch_row($result)) {
    list($matricule,$nom1,$nom2,$nom3,$prenom1,$prenom2,$promo) = $row;
    $sql = "INSERT INTO recherche_soundex
    (matricule,nom1_soundex,nom2_soundex,nom3_soundex,prenom1_soundex,prenom2_soundex,promo)
    VALUES($matricule,'".soundex_fr($nom1)."','".soundex_fr($nom2)."','".soundex_fr($nom3)."','".
    soundex_fr($prenom1)."','".soundex_fr($prenom2)."',$promo)";
    mysql_query($sql);
}
?>
