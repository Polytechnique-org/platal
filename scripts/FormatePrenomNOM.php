<?php echo "L'usage de cette page est réservé à un root"; exit; ?>
<?php
require("xorg.common.inc.php");

$result=mysql_query("SELECT user_id,prenom,nom FROM auth_user_md5");

while($myrow = mysql_fetch_array($result)) {

	$prenom = $myrow["prenom"];
	$old_prenom = $prenom;
	$nom = $myrow["nom"];
	$old_nom = $nom;
	$uid = $myrow["user_id"];

	$pre1=strtok($prenom,"-");
	$pre2=strtok(" ");
	$pre1=ucwords(strtolower($pre1));
	$pre2=ucwords(strtolower($pre2));
	if ($pre2) {
		$prenom = $pre1."-".$pre2;
	} else {
		$prenom = $pre1;
	}
	$nom = strtoupper(strtr($nom,"éèëêàäâïîôöùûüç","eeeeaaaiioouuuc"));

	if ($old_prenom!=$prenom || $old_nom!=$nom) {
		echo "$old_prenom $old_nom >>>>> $prenom $nom<br>"; 
		$prenom = addslashes($prenom);
		$nom = addslashes($nom);
		//mysql_query("UPDATE auth_user_md5 SET prenom='$prenom',nom='$nom' WHERE user_id=$uid");
	}

}

?>
