<?php
require("auto.prepend.inc.php");

if(empty($_REQUEST["xmat"]) || empty($_REQUEST["submit"])) {
    if (empty($_REQUEST["xmat"]) && (empty($_REQUEST["prenomR"]) || empty($_REQUEST["nomR"]))) {
        new_admin_page('marketing/utilisateurs_recherche.tpl');
        $page->run();
    }

    if (!empty($_REQUEST["xmat"])) {
	// on a un matricule, on affiche juste l'entrée correspondante
	$where = "id.matricule={$_REQUEST['xmat']}";
    } else {
	// on n'a pas le matricule, essayer de le trouver moi-meme, de le proposer
	// et de reafficher le formulaire avec les propositions de matricules

	// suppression accents et passage en majuscules des champs entrés
	$nomUS=replace_accent($_REQUEST["nomR"]);
	$nomup=strtoupper($nomUS);
	$nomup=str_replace("\'","'",$nomup);
	$prenomUS=replace_accent($_REQUEST["prenomR"]);
	$prenomup=strtoupper($prenomUS);
	$prenomup=str_replace("\'","'",$prenomup);

	// calcul de la plus longue chaine servant à l'identification
	$chaine1=strtok($nomup," -'");
	$chaine2=strtok(" -'");
	if ( strlen($chaine2) > strlen($chaine1) ) {
	    $chaine = $chaine2;
	}  else  {
	    $chaine = $chaine1;
	}

	if(strlen($_REQUEST["promoR"])==4) {
	    $rq="AND id.promo=".$_REQUEST["promoR"];
	} else {
	    $rq="";
	}

	$where = "id.prenom LIKE '%{$_REQUEST['prenomR']}%' AND id.nom LIKE '%$chaine%' $rq ORDER BY id.promo,id.nom";
    } // a-t-on xmat

    $sql = "SELECT  id.*,user_id
              FROM  identification AS id
         LEFT JOIN  auth_user_md5 USING(matricule)
             WHERE  user_id IS NULL AND $where";

    new_admin_page('marketing/utilisateurs_select.tpl');
    $page->mysql_assign($sql, 'nonins');
    $page->assign('id_actions', array('Mailer'));
    $page->run();
}

function exit_error($err) {
    global $page;
    new_admin_page('marketing/utilisateurs_recherche.tpl');
    $page->assign('err', $err);
    $page->run();
}

//actions possible une fois un X désigné par son matricule
switch ($_REQUEST["submit"]) {
    case "Mailer":
   	$result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule=".$_REQUEST["xmat"]);
	if ($myrow = mysql_fetch_assoc($result)) 
            exit_error("Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5.");
  
	$result = $globals->db->query("SELECT * FROM identification WHERE matricule=".$_REQUEST["xmat"]);
	$myrow = mysql_fetch_array($result);

        new_admin_page('marketing/utilisateurs_form.tpl');

        $page->assign('row', $myrow);

	$prenom=$myrow["prenom"];
	$nom=$myrow["nom"];
	$promo=$myrow["promo"];
	$from=$_SESSION['username']."@polytechnique.org";

        $page->run();
  	break;

    case "Envoyer le mail":
        require('xorg.misc.inc.php');
	$result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule=".$_REQUEST["xmat"]);
  	if ($myrow = mysql_fetch_assoc($result))
            exit_error("Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5.");
			
	if (!isvalid_email_redirection(stripslashes($_REQUEST["mail"])))
            exit_error("L'email n'est pas valide.");
		
	$result=$globals->db->query("SELECT prenom,nom,promo,FIND_IN_SET('femme', flags)
                                     FROM identification 
                                     WHERE matricule=".$_REQUEST['xmat']);
	if (!list($prenom,$nom,$promo,$femme) = mysql_fetch_row($result))
            exit_error("Le matricule n'a pas été trouvé dans table identification.");
			
  	// calcul de l'envoyeur
        list($envoyeur) = explode('@', $_REQUEST["from"]);

	$prenom_envoyeur=strtok($envoyeur,".");
	$prenom_envoyeur=ucfirst($prenom_envoyeur);
	$nom_envoyeur=strtok(" ");
	$nom_env1=strtok($nom_envoyeur,"-");
	$nom_env2=strtok(" ");
	if($nom_env2) {
            $envoyeur=$prenom_envoyeur." ".$nom_env1." ".ucfirst($nom_env2);
	} else {
            $envoyeur=$prenom_envoyeur." ".ucfirst($nom_env1);
	}

	$nom_envoyeur=ucfirst($nom_envoyeur);
			
	// tirage aléatoire de UID et mot de passe
	$user_id=rand_url_id(12);
	$date=date("Y-m-j");

	// decompte du nombre d'utilisateurs;
	$result=$globals->db->query("SELECT COUNT(*) FROM auth_user_md5");
	$num_users=mysql_result($result,0,"count(*)");
			
	// calcul du login
	$mailorg = make_username($prenom,$nom);
			
	$globals->db->query("UPDATE  identification
                                SET  last_known_email='{$_REQUEST['mail']}'
                              WHERE  matricule='{$_REQUEST['xmat']}'");
	$requete="INSERT INTO  envoidirect
                          SET  matricule='{$_REQUEST['xmat']}',uid='$user_id',promo='$promo',
                               prenom='".addslashes($prenom)."',nom='".addslashes($nom)."',
                               email='{$_REQUEST['mail']}',sender='{$_REQUEST['sender']}',date_envoi='$date'";
	$result = mysql_query($requete);
	if (mysql_errno() == 0  || mysql_affected_rows() == 1) {
            // pas d'erreur pour l'insert

            // envoi du mail à l'utilisateur
            require('tpl.mailer.inc.php');
            $mymail = new TplMailer('marketing.utilisateur.tpl');

            $mymail->assign('from', $_REQUEST["from"]);
            $mymail->assign('to', stripslashes($_REQUEST["mail"]));
            $mymail->assign('femme', $femme);
            $mymail->assign('baseurl', $baseurl);
            $mymail->assign('user_id', $user_id);
            $mymail->assign('num_users', $num_users);
            $mymail->assign('mailorg', $mailorg);
            $mymail->assign('envoyeur', $envoyeur);
            $mymail->send();
            
            new_admin_page('marketing/utilisateurs_marketing.tpl');
            $page->run();
	} else {
            exit_error("Une erreur s'est produite lors de la tentative d'insertion de la pré-inscription.");
	}
	break;
}

?>
