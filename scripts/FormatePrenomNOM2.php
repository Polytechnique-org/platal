<?php echo "L'usage de cette page est réservé à un root"; exit; ?>
<?php
require("xorg.common.inc.php");

$result=mysql_query("SELECT matricule,prenom,nom FROM identification");

while($myrow = mysql_fetch_array($result)) {

	$prenom = rtrim($myrow["prenom"]);
	$old_prenom = $prenom;
	$nom = rtrim($myrow["nom"]);
	$old_nom = $nom;
	$uid = $myrow["matricule"];

	$pre1=strtok($prenom,"-");
	$pre2=strtok(" -");
	$pre3=strtok(" -");
	$pre1=ucwords(strtolower($pre1));
	$pre2=ucwords(strtolower($pre2));
	$pre3=ucwords(strtolower($pre3));
	if ($pre3) {
		$prenom = $pre1."-".$pre2."-".$pre3;
	} else if ($pre2) {
		$prenom = $pre1."-".$pre2;
	} else {
		$prenom = $pre1;
	}
	$nom = strtoupper(strtr($nom,"éèëêàäâïîôöùûüç","eeeeaaaiioouuuc"));

	if ($old_prenom != $prenom || $old_nom != $nom) {
		echo "$old_prenom $old_nom >>>>> $prenom $nom<br>"; 
		$prenom = addslashes($prenom);
		$nom = addslashes($nom);
		//mysql_query("UPDATE identification SET prenom='$prenom',nom='$nom' WHERE matricule='$uid'");
	}

}

?>
