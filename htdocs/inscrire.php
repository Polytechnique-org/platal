<?php
require("auto.prepend.inc.php");

$erreur = Array();

if(isset($_REQUEST["charte"])) { // ok la variable $_REQUEST["charte"] existe
    if( empty($_REQUEST["promo"])  && empty($_REQUEST["nom"])  && empty($_REQUEST["prenom"]) )  {
        //  id non remplie, affichage form id
        new_skinned_page('inscrire.form_id.tpl', AUTH_PUBLIC, true);
        $page->assign('erreur', $erreur);
        $page->run();
    } elseif (empty($_REQUEST["email"])) { // formulaire identification soumis
        // data pas encore envoyées
        // vérification du format des données rentrées
        if( empty($_REQUEST["nom"]) || empty($_REQUEST["prenom"]) || empty($_REQUEST["promo"])
                || ( strlen(strtok($_REQUEST["nom"],"()")) < strlen($_REQUEST["nom"]) )
          ) {
            //  il manque au moins une donnée
            new_skinned_page('inscrire.form_id.tpl', AUTH_PUBLIC, true);
            $erreur[] = "Il manque des champs ou certains champs contiennent des caractères interdits. Recommence.";
            $page->assign('erreur', $erreur);
            $page->run();
        } else  {
            // $_REQUEST["nom"], $_REQUEST["prenom"] et $_REQUEST["promo"] existent
            new_skinned_page('inscrire.form_data.tpl', AUTH_PUBLIC, true);
            
	    require("identification.inc.php");
            require("applis.func.inc.php");
	    
	    $page->assign('homonyme', $homonyme);
	    $page->assign('loginbis', isset($loginbis) ? $loginbis : '');
	    $page->assign('mailorg', $mailorg);
	    
	    $page->assign('prenom', $prenom);
	    $page->assign('nom', $nom);
	    
            $page->assign('erreur', $erreur);
	    $page->run();
        }
    } else { // $_REQUEST["email"] est renseignée donc l'inscription est finie
        require("identification.inc.php");
        require("xorg.misc.inc.php");
	
	if(!isvalid_email($_REQUEST["email"]))
	    $erreur[] = "Le champ 'E-mail' n'est pas valide.";
	if (!isvalid_email_redirection($_REQUEST["email"]))
	    $erreur[] = "\"$mailorg@polytechnique.org\" doit renvoyer vers un email existant valide. En particulier, il ne peut pas être renvoyé vers lui-même.";
	if (!ereg("[0-3][0-9][0-1][0-9][1][9]([0-9]{2})", $_REQUEST["naissance"]))
	    $erreur[] = "La 'Date de naissance' n'est pas correcte. Elle est obligatoire pour continuer mais ne sera jamais visible sur le site par la suite.";
	
	if(!empty($erreur)) {
	    new_skinned_page('inscrire.form_data.tpl', AUTH_PUBLIC, true);
	    require("applis.func.inc.php");
	    $page->assign('homonyme', $homonyme);
	    $page->assign('loginbis', isset($loginbis) ? $loginbis : '');
	    $page->assign('mailorg', $mailorg);
	    
	    $page->assign('prenom', $prenom);
	    $page->assign('nom', $nom);
	    
	    $page->assign('erreur', $erreur);
	    $page->run();
	}

	$ins_id=rand_url_id(12);
	$pass_clair=rand_pass();
	$password=md5($pass_clair);
	$date=date("Y-m-j");
	
	// on nettoie les appli_type(1|2) si elles ne sont pas affectees
	if (!isset($_REQUEST["appli_type1"])) $_REQUEST["appli_type1"]=0;
	if (!isset($_REQUEST["appli_type2"])) $_REQUEST["appli_type2"]=0;
	if (!isset($loginbis)) $loginbis="";
	
	// nouvelle inscription
	$sql="REPLACE INTO  en_cours
	               SET  ins_id='$ins_id', password='$password', matricule='$matricule', promo='{$_REQUEST['promo']}',
                       nom='".addslashes($nom)."', prenom='".addslashes($prenom)."', email='{$_REQUEST['email']}',
                       naissance='{$_REQUEST['naissance']}', date='$date', nationalite='{$_REQUEST['nationalite']}',
                       appli_id1='{$_REQUEST['appli_id1']}', appli_type1='{$_REQUEST['appli_type1']}',
                       appli_id2='{$_REQUEST['appli_id2']}', appli_type2='{$_REQUEST['appli_type2']}',
                       loginbis='$loginbis', username='$mailorg'";
	$globals->db->query($sql);
        
        $globals->db->query("UPDATE identification SET last_known_email='".$_REQUEST["email"]."' WHERE matricule = " . $matricule);
        
        // si on vient de la page maj.php, on met a jour la table envoidirect
        if(isset($envoidirect))
            $globals->db->query("UPDATE envoidirect SET date_succes='".date("Y-m-j")."' WHERE uid='".$envoidirect."'");

        require("tpl.mailer.inc.php");
        $mymail = new TplMailer('inscrire.mail.tpl');
        $mymail->assign('mailorg',$mailorg);
        $mymail->assign('pass_clair',$pass_clair);
        $mymail->assign('baseurl',$baseurl);
        $mymail->assign('ins_id',$ins_id);
        
        $mymail->assign('subj',$mailorg."@polytechnique.org");
        $mymail->send();

	new_skinned_page('inscrire.preins.tpl', AUTH_PUBLIC, true);
        $page->assign('mailorg', $mailorg);
        $page->run();
    }		
} else {
    // $_REQUEST["charte"] n'existe pas, afficher la charte
    new_skinned_page('inscrire.charte.tpl', AUTH_PUBLIC, true);
    $page->assign('erreur', $erreur);
    $page->run();
}
?>
