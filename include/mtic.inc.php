<?php
$mtic_domains = "/etc/postfix/forward-domaines.conf";
$mtic_mailforward = '"|/home/listes/mailforward ';



/** nettoyage du champ email s'il contient la redirection
 * @param $email email
 * @return le email épuré
 * @see utilisée nulle part
 */
function clean_mtic($email) {
	global $mtic_mailforward;
	$len = strlen($mtic_mailforward);
	if (strncmp($mtic_mailforward,$email,$len) == 0) {
		// on vire le debut (mtic_mailforward) et le dernier caractere
		$email = substr($email,$len, -1);
	}
	return $email;
}



/** on regarde l'adresse et on ajoute la redirection si necessaire
 * @param $email email
 * @return true || false
 * @see emails.php
 * @see step4.php
 */
function check_mtic($email) {
	global $mtic_domains,$mtic_mailforward;
	list($local,$domain) = explode("@",$email);
	// lecture du fichier de configuration
	$tab = file($mtic_domains);
	foreach ($tab as $ligne) {
		if ($ligne{0} != '#') { // on saute les commentaires
			// pour chaque ligne, on regarde si la première partie
			// qui correspond au domaine du destinataire
			// matche le domaine de l'email donnée
			$a = explode(':',$ligne);
			$regexp = $a[0];
			if (eregi($regexp,$domain)) {
				// c'est le cas, on revoie true
				return true;
			}
		}
	}
	return false;
}



?>
