<?php
require("auto.prepend.inc.php");
$id_actions = array('Editer','Inscrire');
require("select_user.inc.php");
require("inscription_listes_base.inc.php");

//actions possible une fois un X désigné par son matricule
switch ($_REQUEST["submit"]) {
case "Editer":
	$result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule=".$_REQUEST["xmat"]);
  	if ($myrow = mysql_fetch_array($result))
			exit_error(">Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5, refus d'&eacute;dition.");
	$result = $globals->db->query("select * from identification where matricule=".$_REQUEST["xmat"]);
	$myrow = mysql_fetch_array($result);
    new_admin_page('marketing/utilisateurs_edit.tpl');
    $page->assign('row', $myrow);
    $page->run();
	break;
		  
case "Modifier la base":
	// gestion des flags
    $flags = (empty($_REQUEST['flag_femmeN']))?"":"femme";
	// on fait la requete
	$sql="update identification set prenom='".$_REQUEST["prenomN"]."',nom='".$_REQUEST["nomN"]."',promo=".$_REQUEST["promoN"].",deces='".$_REQUEST["decesN"]."',flags='".$flags."' where matricule=".$_REQUEST["xmat"];
	$globals->db->query($sql);
    new_admin_page('marketing/utilisateurs_edit.tpl');
    $page->assign('success',1);
    $page->run();
	break;
		  
case "Inscrire":
  	$result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule=".$_REQUEST["xmat"]);
  	if ($myrow = mysql_fetch_array($result))
		exit_error("Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5.");
			
	$result = $globals->db->query("select * from identification where matricule=".$_REQUEST["xmat"]);
	$myrow = mysql_fetch_array($result);
    new_admin_page('marketing/utilisateurs_inscrire.tpl');
    $page->assign('row', $myrow);
	//calcul du login de l'inscrit
	$page->assign('mailorg',make_username($myrow['prenom'],$myrow['nom']));
    $page->run();
	break;
		
case "Creer le login":
	//on commence par vérifier:
	// - que le matricule n'existe pas parmi les inscrits
  	// - que le login n'existe pas déjà
  	// - que la date de naissance est bien remplie
    
	$result=mysql_query("SELECT user_id FROM auth_user_md5 where matricule='".$_REQUEST["xmat"]."'");
  	if ($myrow = mysql_fetch_array($result))
		exit_error("Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5.");
				
	$result=mysql_query("SELECT user_id FROM auth_user_md5 where username='".$_REQUEST["mailorg"]."'");
  	if ($myrow = mysql_fetch_array($result))
		exit_error("Le login existe d&eacute;j&agrave; dans la table auth_user_md5.");

	$naissance=(isset($_REQUEST['naissance']))?$naissance:0;

	// tirage aléatoire de UID et mot de passe
	$pass_clair=rand_pass();
	$password=md5($pass_clair);
	$date=date("Y-m-j");
				
	$sql = "INSERT INTO auth_user_md5 SET username='".$_REQUEST['mailorg']."',matricule='".$_REQUEST['xmat']."',promo='".$_REQUEST['promoN']."',password='$password',nom='".$_REQUEST['nomN']."',prenom='".$_REQUEST['prenomN']."',date='$date',naissance='$naissance', date_ins = NULL";
	$result=$globals->db->query($sql);

	if (!$globals->db->err()) {
		$newuid = mysql_insert_id();
		/** inscription à la liste promo ****************/
		$inspromo = inscription_liste_promo($newuid,$_REQUEST['promoN']);
		/** inscription à la newsletter  ***************/
		$insnewsletter = inscription_newsletter($newuid);

  		// pas d'erreur pour l'insert
        new_admin_page('marketing/utilisateurs_inscrire.tpl');
        $page->assign('mailorg',$_REQUEST['mailorg']);
        $page->assign('pass_clair',$pass_clair);
        $page->assign('success',1);
        $page->run();
	} else {
		exit_error("Une erreur s'est produite lors de la tentative d'insertion.");
	}
	break;
}
?>
