<?php
	
	global $globals;
        //chemin de stockage de la clé privée  A MODIFIER
        $chemin_cle_privee = $globals->webservice->private_key_ax;
        //phrase cryptant la clé privée
        $phrase = $globals->webservice->pass_ax;
//	"Cryptage de la cle pour polytechnique.org";
            
        //paramètres serveurs  
        $nom_script_renvoi_challenge = $globals->webservice->host_ax."serveur_genere_challenge.php";
        $nom_script_renvoi_infos = $globals->webservice->host_ax."serveur_renvoie_infos_ancien_direct.php";
?>
