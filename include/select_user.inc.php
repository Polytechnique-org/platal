<?php
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
    $page->assign('id_actions', $id_actions);
    $page->run();
}

function exit_error($err) {
    global $page;
    new_admin_page('marketing/utilisateurs_recherche.tpl');
    $page->assign('err', $err);
    $page->run();
}
?>
