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
        $Id: utilisateurs_identification.php,v 1.5 2004-09-03 00:15:49 x2000bedo Exp $
 ***************************************************************************/

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
	$page->assign('mailorg',make_forlife($myrow['prenom'],$myrow['nom'],$myrow['promo']));
    $page->run();
	break;
		
case "Creer le login":
	//on commence par vérifier:
	// - que le matricule n'existe pas parmi les inscrits
  	// - que le login n'existe pas déjà
  	// - que la date de naissance est bien remplie
    
	$result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule='".$_REQUEST["xmat"]."'");
  	if ($myrow = mysql_fetch_array($result))
		exit_error("Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5.");
				
	$result=$globals->db->query("SELECT user_id FROM aliases AS a 
    WHERE a.type='a_vie' AND a.alias='".$_REQUEST["mailorg"]."'");
  	if ($myrow = mysql_fetch_array($result))
		exit_error("Le login existe d&eacute;j&agrave; dans la table auth_user_md5.");

	$naissance=(isset($_REQUEST['naissance']))?$naissance:0;

	// tirage aléatoire de UID et mot de passe
	$pass_clair=rand_pass();
	$password=md5($pass_clair);
	$date=date("Y-m-j");
				
	$sql = "INSERT INTO auth_user_md5 SET matricule='".$_REQUEST['xmat']."',promo='".$_REQUEST['promoN']."',password='$password',nom='".$_REQUEST['nomN']."',prenom='".$_REQUEST['prenomN']."',date='$date',naissance='$naissance', date_ins = NULL";
	$result=$globals->db->query($sql);

	if (!$globals->db->err()) {
		$newuid = mysql_insert_id();
        $sql = "INSERT INTO aliases SET alias='".$_REQUEST['mailorg']."',type='a_vie',id='$newuid'";
        $result=$globals->db->query($sql);
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
