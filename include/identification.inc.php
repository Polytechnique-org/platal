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
        $Id: identification.inc.php,v 1.19 2004/12/01 07:39:55 x2000habouzit Exp $
 ***************************************************************************/

require_once('xorg.misc.inc.php');

function sortie_id($err) {
    global $page;
    new_skinned_page('inscription/step1.tpl', AUTH_PUBLIC);
    $page->assign('erreur', $err);
    $page->run();
}

$promo = intval($_REQUEST["promo"]);
if ($promo<1900 || $promo>2100) {
    sortie_id("La promotion doit comporter 4 chiffres.");
}

/* on recupere les donnees  */
$prenom = preg_replace('/ +/',' ',trim(strip_request('prenom')));
$nom    = preg_replace('/ +/',' ',trim(strip_request('nom')));

// majuscules pour nom et prenom
$nom    = strtoupper(replace_accent($nom));
$prenom = make_firstname_case($prenom);

// calcul du login
$mailorg = make_username($prenom,$nom);
$forlife = make_forlife($prenom,$nom,$promo);

// version uppercase du prenom
$prenomup=strtoupper(replace_accent($prenom));

@list($chaine1,$chaine2) = preg_split("/[ \-']/",$nom);
$chaine = strlen($chaine2) > strlen($chaine1) ? $chaine2 : $chaine1;

// c'est parti pour l'identification, les champs étant corrects
if ($promo > 1995)  {

    if (strlen($_REQUEST["matricule"]) != 6) {
	sortie_id("Le matricule qu'il faut que tu  rentres doit comporter 6 chiffres.");
    }

    /* transformation du matricule afin de le rendre Y2K compliant
     * (i.e. de la forme PPPP0XXX où PPPP est l'année d'inscription à l'école
     * (i.e. le numéro de promotion sauf pour les étrangers voie 2) et XXX le numéro d'entrée cette année-là
     */

    $matrcondense = $_REQUEST["matricule"];
    $rangentree = intval(substr($_REQUEST["matricule"], 3, 3));
    $anneeimmatric = intval(substr($_REQUEST["matricule"],0,3));
    if($anneeimmatric > 950) $anneeimmatric/=10;
    if ($anneeimmatric < 96) {
	sortie_id("ton matricule est incorrect");
    } elseif ($anneeimmatric < 100) {
	// jusqu'à la promo 99 c'est 9?0XXX
	$year = 1900 + intval(substr($_REQUEST["matricule"], 0, 2));
    }  elseif($anneeimmatric < 200) {
	// depuis les 2000 c'est 10?XXX
	$year = 2000 + intval(substr($_REQUEST["matricule"], 1, 2));
    } else {
	sortie_id("la gestion des promotions >= 2100 n'est pas prête !");
    }

    $matricule = sprintf('%04u%04u', $year, $rangentree);

    // on vérifie que le matricule n'est pas déjà dans auth_user_md5
    // sinon le même X pourrait s'inscrire deux fois avec le même matricule
    // exemple yann.buril et yann.buril-dupont seraient acceptés ! alors que
    // le matricule est unique
    $result=$globals->db->query("SELECT user_id FROM auth_user_md5 WHERE matricule=$matricule AND perms IN('admin','user')");
    if (mysql_num_rows($result))  {
	$str="Matricule déjà existant. Causes possibles\n"
	    ."- tu t'es trompé de matricule\n"
	    ."- tu t'es déjà inscrit une fois";
	sortie_id($str);
    }

    // promotion jeune
    $result=$globals->db->query("SELECT  nom, prenom
			           FROM  auth_user_md5
				  WHERE  matricule='$matricule' AND promo='$promo' AND deces=0");
    list($mynom, $myprenom) = mysql_fetch_row($result);
    $mynomup=strtoupper(replace_accent($mynom));
    $myprenomup=strtoupper(replace_accent($myprenom));
    $autorisation = FALSE;

    if (strlen($chaine2)>0)  {        // il existe au moins 2 chaines
	// on teste l'inclusion des deux chaines
	$autorisation = ( strstr($mynomup,$chaine1) && strstr($mynomup,$chaine2) && ($myprenomup == $prenomup) );
    }  else   {
	// la chaine2 est vide, on n'utilise que chaine
	$autorisation = ( strstr($mynomup,$chaine) && ($myprenomup == $prenomup) );
    }

    if (!$autorisation) {
	$str="Echec dans l'identification. Réessaie, il y a une erreur quelque part !";
	sortie_id($str);
    }

} else {
    // CODE SPECIAL POUR LES X DES PROMOTIONS AVANT 1996
    $sql = "SELECT nom,prenom,matricule FROM auth_user_md5 WHERE promo='$promo' AND deces=0";
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
    } else {                       // une seule chaine

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
	$result=$globals->db->query("SELECT * FROM auth_user_md5 WHERE matricule='".$matricule."' AND perms IN ('admin','user')");
	if ($myrow = mysql_fetch_array($result))  {
	    $str="Tu t'es déjà inscrit une fois.\n"
		."Ecris à <a href=\"mailto:support@polytechnique.org\">support@polytechnique.org</a> pour tout problème.";
	    sortie_id($str);
	}
    }

    if (!$autorisation)  {
	$str="Echec dans l'identification. Réessaie, il y a une erreur quelque part !";
	sortie_id($str);
    }
    // identification < 1991 OK
}

/*****************************************************************************/
/***************************** IDENTIFICATION OK *****************************/
/*****************************************************************************/

$result = $globals->db->query("SELECT id,type,expire FROM aliases WHERE alias='$mailorg'");
$homonyme = mysql_num_rows($result) > 0;

if ( $homonyme ) {
    list($h_id,$h_type,$expire) = mysql_fetch_row($result);
    mysql_free_result($result);

    $result = $globals->db->query("SELECT alias FROM aliases WHERE alias='$forlife'");
    if ( mysql_num_rows($result) > 0 ) {
	sortie_id("Tu as un homonyme dans ta promo, il faut traiter ce cas manuellement.\n".
		"envoie un mail à <a href=\"mailto:support@polytechnique.org\">support@polytechnique.org</a>");
    }
    mysql_free_result($result);

    if ( $h_type != 'homonyme' and empty($expire) ) {
	$globals->db->query("UPDATE aliases SET expire=ADDDATE(NOW(),INTERVAL 1 MONTH) WHERE alias='$mailorg'");
	$globals->db->query("REPLACE INTO homonymes (homonyme_id,user_id) VALUES ($h_id,$h_id)");
	require_once('diogenes.hermes.inc.php');
	$mailer = new HermesMailer();
	$mailer->setFrom('"Support Polytechnique.org" <support@polytechnique.org>');
	$mailer->addTo("$mailorg@polytechnique.org");
	$mailer->setSubject("perte de ton alias $mailorg dans un mois !");
	$mailer->addCc('"Support Polytechnique.org" <support@polytechnique.org>');
	$msg =
	    "Un homonyme s'est inscrit, nous ne pouvons donc garder ton alias '$mailorg'.\n\n".
	    "Tu gardes tout de même l'usage de cet alias pour 1 mois encore à compter de ce jour.\n\n".
	    "Lorsque cet alias sera désactivé, l'adresse :\n".
	    "    $mailorg@polytechnique.org\n".
	    "renverra vers un robot qui indique qu'il y a plusieurs personnes portant le même nom ; cela évite que l'un des homonymes reçoive des courriels destinés à l'autre.\n\n".
	    "Cordialement\n\n".
	    "-- \n".
	    "Polytechnique.org\n".
	    "\"Le portail des élèves & anciens élèves de l'X\"";
	$mailer->SetTxtBody(wordwrap($msg,72));
	$mailer->send();
    }
    unset($mailorg);
}

?>
