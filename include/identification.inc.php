<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: identification.inc.php,v 1.3 2004-09-01 17:59:08 x2000habouzit Exp $
 ***************************************************************************/


function sortie_id($err) {
    global $erreur,$page;
    $erreur[] = $err;
    new_skinned_page('inscrire.form_id.tpl', AUTH_PUBLIC, true);
    $page->assign('erreur', $erreur);
    $page->run();
}

if (strlen($_REQUEST["promo"])<4) {
    sortie_id("La promotion comporte 4 chiffres.");
}

/* on recupere les donnees  */
$prenom=trim(strip_request('prenom'));
$prenom=eregi_replace("[[:space:]]+"," ",$prenom);

$nom=trim(strip_request('nom'));
$nom=eregi_replace("[[:space:]]+"," ",$nom);

// majuscules pour nom et prenom
$nom=strtoupper(replace_accent($nom));
$prenom = make_firstname_case($prenom);

// calcul du login
$mailorg = make_username($prenom,$nom);

// version uppercase du prenom
$prenomup=strtoupper(replace_accent($prenom));

// calcul de la plus longue chaine servant à l'identification
$chaine1=strtok($nom," -'");
$chaine2=strtok(" -'");
if ( strlen($chaine2) > strlen($chaine1) ) {
    $chaine = $chaine2;
} else {
    $chaine = $chaine1;
}

// c'est parti pour l'identification, les champs étant corrects
if ($_REQUEST["promo"] > 1995)  {

    if (strlen($_REQUEST["matricule"]) != 6) {
	sortie_id("Le matricule qu'il faut que tu  rentres doit comporter 6 chiffres.");
    }

    /* transformation du matricule afin de le rendre Y2K compliant (i.e. de la forme PPPP0XXX où PPPP est l'année d'inscription à l'école (i.e. le numéro de promotion sauf pour les étrangers voie 2) et XXX le numéro d'entrée cette année-là */

    $matrcondense = $_REQUEST["matricule"];
    $rangentree = substr($_REQUEST["matricule"], 3, 3);
    $anneeimmatric = substr($_REQUEST["matricule"],0,2);
    if ($anneeimmatric >= 96 && $anneeimmatric <= 99) {
	// jusqu'à la promo 99 c'est 9?0XXX
	$matricule = "19" . substr($_REQUEST["matricule"], 0, 3) . $rangentree;
    }  else  {
	// depuis les 2000 c'est 10?XXX
	$matricule = "20" . substr($_REQUEST["matricule"], 1, 2) . "0" . $rangentree;
    }

    // on vérifie que le matricule n'est pas déjà dans auth_user_md5
    // sinon le même X pourrait s'inscrire deux fois avec le même matricule
    // exemple yann.buril et yann.buril-dupont seraient acceptés ! alors que
    // le matricule est unique
    $result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule=$matricule");
    if ($myrow = mysql_fetch_array($result))  {
	$str="Matricule déjà existant. Causes possibles<br />\n"
	    ."- tu t'es trompé de matricule<br />\n"
	    ."- tu t'es déjà inscrit une fois";
	$matricule = $matrcondense;
	sortie_id($str);
    }
    // promotion jeune
    $result=$globals->db->query("SELECT nom, prenom FROM identification where matricule='".$matricule."' AND promo='".$_REQUEST["promo"]."' AND deces=0");
    list($mynom, $myprenom) = mysql_fetch_row($result);
    $mynomup=strtoupper(replace_accent($mynom));
    $myprenomup=strtoupper(replace_accent($myprenom));
    $autorisation = FALSE;

    if (strlen($chaine2)>0)  {        // il existe au moins 2 chaines
	// on teste l'inclusion des deux chaines
	if ( strstr($mynomup,$chaine1) && strstr($mynomup,$chaine2) && ($myprenomup == $prenomup )) 
	    $autorisation = TRUE;
    }  else   {
	// la chaine2 est vide, on n'utilise que chaine
	if ( strstr($mynomup,$chaine) && ($myprenomup == $prenomup) )  
	    $autorisation = TRUE;
    }

    if (!$autorisation) {
	$str="Echec dans l'identification. Réessaie, il y a une erreur quelque part !";
	sortie_id($str);
    }

    // identification > 1990 OK

} else {       // promotion avant 1996 pas de matricule !

    // CODE SPECIAL POUR LES X DES PROMOTIONS AVANT 1996
    $sql = "SELECT nom,prenom,matricule FROM identification WHERE promo='".$_REQUEST["promo"]."' AND deces=0";
    $result = $globals->db->query($sql);
    $autorisation = FALSE;

    if (strlen($chaine2)>0)  {        // il existe au moins 2 chaines

	while (list($mynom,$myprenom,$mymat) = mysql_fetch_array($result))  {
	    // verification de toute la promo !
	    $mynomup=strtoupper(replace_accent($mynom));
	    $myprenomup=strtoupper(replace_accent($myprenom));

	    if ( strstr($mynomup,$chaine1) && strstr($mynomup,$chaine2) && ($myprenomup==$prenomup) )  {
		$autorisation = TRUE;
		$matricule=$mymat;
		break;
	    }
	}
    } else  {                       // une seule chaine

	while (list($mynom,$myprenom,$mymat) = mysql_fetch_array($result))  {
	    // verification de toute la promo !
	    $mynomup=strtoupper(replace_accent($mynom));
	    $myprenomup=strtoupper(replace_accent($myprenom));
	    if ( strstr($mynomup,$chaine) && ($myprenomup==$prenomup) )  {
		$autorisation = TRUE;
		$matricule=$mymat;
		break;
	    }
	}
    }
    mysql_free_result($result);

    // on vérifie que le matricule n'est pas déjà dans auth_user_md5
    // sinon le même X pourrait s'inscrire deux fois avec le même matricule
    // exemple yann.buril et yan.buril seraient acceptés ! alors que le matricule
    // est unique
    if (! empty($matricule)) { 
	$result=$globals->db->query("SELECT * FROM auth_user_md5 where matricule='".$matricule."'");
	if ($myrow = mysql_fetch_array($result))  {
	    $str="Tu t'es déjà inscrit une fois. "
		."Ecris à <a href=\"mailto:support@polytechnique.org\">support@polytechnique.org</a> "
		."pour tout problème.";
	    sortie_id($str);
	}
    }

    if (!$autorisation)  {
	$str="Echec dans l'identification. Réessaie, il y a une erreur quelque part !";
	sortie_id($str);
    }
    // identification < 1991 OK
}

// test si homonyme
$result=$globals->db->query("SELECT nom, prenom, promo FROM identification WHERE nom = '".addslashes($nom)."' AND prenom = '".addslashes($prenom)."' AND matricule <> '".$matricule."'");
// (les comparaisons sont indépendantes de la case et des accents en mysql)
$homonyme = 0;
if ( mysql_num_rows($result) > 0 ) {
    // on a un homonyme
    $homonyme = 1;
    $loginbis = $mailorg;
    $mailorg = $mailorg . substr($_REQUEST["promo"],-2);
    while ( list($mynom,$myprenom,$mypromo) = mysql_fetch_row($result) ) {
	if (($mypromo % 100) == ($_REQUEST["promo"] % 100)) {
	    sortie_id("Tu as un homonyme dans ta promo, il "
		    ."faut traiter ce cas manuellement, envoie un mail à "
		    ."<a href=\"mailto:support@polytechnique.org\">support@polytechnique.org</a>");
	}
    }
    $result=$globals->db->query("SELECT user_id, promo FROM auth_user_md5 where username='$loginbis'");
    if ( list($uid,$mypromo) = mysql_fetch_row($result) ) {
	// un homonyme est déjà enregistré, le prévenir
	// (la promo ne peut pas être pareille, cas déjà testé)
	mysql_free_result($result);
	$newlogin = $loginbis.".".(($mypromo >= 2000) ? $mypromo : ($mypromo%100)));
	$sql = "UPDATE auth_user_md5 SET loginbis='$loginbis', username = '$newlogin', alias='$loginbis', date_mise_alias_temp = NOW() WHERE user_id = $uid";
	$globals->db->query($sql);
	if ( mysql_affected_rows() == 0 ) {
	    // pb de mise à jour
	    $MESSAGE =
		"Pb lors de l'execution de \"$sql\" avec le message".mysql_error().", a corriger";
	    mail("support","Pb d'update lors de l'inscription d'un homonyme",$MESSAGE);
	} else {
	    // mise à jour OK
	    $HEADER =
		"From: support@polytechnique.org\nCc: support@polytechnique.org";
	    $MESSAGE =
		"Un homonyme s'est inscrit, nous ne pouvons donc garder  ton  identifiant"
		."\n($loginbis) unique, il devient $newlogin ."
		."\n\nTu dois dès maintenant l'utiliser pour te connecter sur le site mais"
		."\nton adresse de courriel :"
		."\n    $loginbis@polytechnique.org"
		."\nreste encore valable pour 1 mois, le temps que tu passes sur ta nouvelle"
		."\nadresse :"
		."\n    $newlogin@polytechnique.org"
		."\nqui est déjà utilisable."
		."\n\nQuand ton identifiant sera désactivé, l'adresse :"
		."\n    $loginbis@polytechnique.org"
		."\nrenverra vers un robot qui indique qu'il y a plusieurs personnes portant"
		."\nle même nom ; cela évite que l'un des homonymes  reçoive  des  courriels"
		."\ndestinés à l'autre."
		."\n\nSache que tu peux aussi demander un alias de ton choix qui te donne  une"
		."\nautre adresse qui te conviendra peut-être mieux."
		."\n\nCordialement"
		."\n\n-- \nPolytechnique.org"
		."\n\"Le portail des élèves & anciens élèves de l'X\"";
	    mail($loginbis,"Changement de ton login",$MESSAGE,$HEADER);
	} // END IF if ( mysql_affected_rows() == 0 ) THEN ELSE
    } // END IF
} // END IF

// on teste si il n'y a pas d'alias
if (isset($loginbis))
    $result=$globals->db->query("SELECT username FROM auth_user_md5 where alias='$loginbis'");
    else
    $result=$globals->db->query("SELECT username FROM auth_user_md5 where alias='$mailorg'");
    while ( list($autre_user) = mysql_fetch_row($result) ) {
	// mise à jour OK
	$HEADER="From: support@polytechnique.org\nBcc: support@polytechnique.org";
	$MESSAGE="Un homonyme s'est inscrit, nous ne pouvons donc garder ton alias "
	    .$loginbis. "\n\n"
	    ."Dès que tu auras pu prévenir tes correspondants fais nous signe, "
	    ."nous supprimerons ton alias.\n\n"
	    ."-- \nPolytechnique.org\n"
	    ."\"Le portail des élèves & anciens élèves de l'X\"";
	mail($autre_user,"Changement de ton login",$MESSAGE,$HEADER);
    }
mysql_free_result($result);

// on vérifie l'adresse n'existe pas déjà dans auth_user_md5 !!
$result=$globals->db->query("SELECT * FROM auth_user_md5 where username='$mailorg'");
if ( mysql_num_rows($result) > 0 ) {
    // le même login existe déjà
    $str="L'adresse ".$mailorg."@polytechnique.org est déjà prise. "
	."Seule une inscription manuelle est possible avec une autre adresse.<br />"
	."Envoie un mail &agrave; <a href=\"mailto:support@polytechnique.org\">"
	."support@polytechnique.org</a>";
    sortie_id($str);
}

?>
