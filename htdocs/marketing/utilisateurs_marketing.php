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
        $Id: utilisateurs_marketing.php,v 1.15 2004/11/22 20:04:51 x2000habouzit Exp $
 ***************************************************************************/

require_once("xorg.inc.php");
$id_actions = array('Mailer');
require_once("select_user.inc.php");

//actions possible une fois un X désigné par son matricule
switch ($_REQUEST["submit"]) {
    case "Mailer":
   	$result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule={$_REQUEST['xmat']} AND perms!='pending'");
	if ($myrow = mysql_fetch_assoc($result)) 
            exit_error("Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5.");
  
	$result = $globals->db->query("SELECT * FROM auth_user_md5 WHERE matricule=".$_REQUEST["xmat"]);
	$myrow = mysql_fetch_array($result);

        new_admin_page('marketing/utilisateurs_form.tpl');

        $page->assign('row', $myrow);

	$prenom=$myrow["prenom"];
	$nom=$myrow["nom"];
	$promo=$myrow["promo"];
	$from = "Equipe Polytechnique.org <register@polytechnique.org>";

        $page->run();
  	break;

    case "Envoyer le mail":
        require_once('xorg.misc.inc.php');
	$result=$globals->db->query("SELECT user_id FROM auth_user_md5 where matricule={$_REQUEST['xmat']} AND perms!='pending'");
  	if ($myrow = mysql_fetch_assoc($result))
            exit_error("Le matricule existe d&eacute;j&agrave; dans la table auth_user_md5.");
			
	if (!isvalid_email_redirection(stripslashes($_REQUEST["mail"])))
            exit_error("L'email n'est pas valide.");
		
	$result=$globals->db->query("SELECT prenom,nom,promo,FIND_IN_SET('femme', flags)
                                     FROM auth_user_md5
                                     WHERE matricule=".$_REQUEST['xmat']);
	if (!list($prenom,$nom,$promo,$femme) = mysql_fetch_row($result))
            exit_error("Le matricule n'a pas été trouvé dans table auth_user_md5.");
			
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
	$mailorg = make_forlife($prenom,$nom,$promo);
			
	$globals->db->query("UPDATE  auth_user_md5
                                SET  last_known_email='{$_REQUEST['mail']}'
                              WHERE  matricule='{$_REQUEST['xmat']}'");
	$requete="INSERT INTO  envoidirect
                          SET  matricule='{$_REQUEST['xmat']}',uid='$user_id',promo='$promo',
                               prenom='".addslashes($prenom)."',nom='".addslashes($nom)."',
                               email='{$_REQUEST['mail']}',sender='{$_REQUEST['sender']}',date_envoi='$date'";
	$globals->db->query($requete);
	// pas d'erreur pour l'insert

	// envoi du mail à l'utilisateur
	require_once('xorg.mailer.inc.php');
	$mymail = new XOrgMailer('marketing.utilisateur.tpl');

	$mymail->assign('from', $_REQUEST["from"]);
	$mymail->assign('to', stripslashes($_REQUEST["mail"]));
	$mymail->assign('femme', $femme);
	$mymail->assign('baseurl', $globals->baseurl);
	$mymail->assign('user_id', $user_id);
	$mymail->assign('num_users', $num_users);
	$mymail->assign('mailorg', $mailorg);
	$mymail->assign('envoyeur', $envoyeur);
	$mymail->send();
	
	new_admin_page('marketing/utilisateurs_marketing.tpl');
	$page->run();
	break;
}

?>
