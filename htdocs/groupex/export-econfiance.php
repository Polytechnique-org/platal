<?php

/* Script permettant l'export de la liste des membres de la mailing list eConfiance, pour intégration par J-P Figer dans la liste des membres de X-Informatique */

session_start();

$cle = "186357043dcbe666ba6cb8.04581835";

if (isset($_SESSION["chall"]) && $_SESSION["chall"] != "" && $_GET["PASS"] == md5($_SESSION["chall"].$cle)) {

require("db_connect.inc.php");

$all = $globals->db->query("SELECT prenom,nom,username FROM auth_user_md5 as u,listes_ins as i WHERE i.idu=u.user_id AND i.idl=174 AND i.idu != 0 ORDER BY nom");

$res = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n<membres>\n\n";

while (list ($prenom1,$nom1,$email1) = mysql_fetch_row($all)) {
        $res .= "<membre>\n";
	$res .= "\t<nom>".$nom1."</nom>\n";
	$res .= "\t<prenom>".$prenom1."</prenom>\n";
	$res .= "\t<email>".$email1."</email>\n";
	$res .= "</membre>\n\n";
}
mysql_free_result($all);

$res .= "</membres>\n\n";

echo $res;

}

?>
