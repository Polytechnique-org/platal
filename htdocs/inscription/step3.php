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
        $Id: step3.php,v 1.2 2004-10-31 16:02:46 x2000chevalier Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");

$erreur = Array();

require("identification.inc.php");

if(!isvalid_email($_REQUEST["email"]))
    $erreur[] = "Le champ 'E-mail' n'est pas valide.";
if (!isvalid_email_redirection($_REQUEST["email"]))
    $erreur[] = "\"$forlife@polytechnique.org\" doit renvoyer vers un email existant valide. En particulier, il ne peut pas être renvoyé vers lui-même.";
if (!ereg("[0-3][0-9][0-1][0-9][1][9]([0-9]{2})", $_REQUEST["naissance"]))
    $erreur[] = "La 'Date de naissance' n'est pas correcte. Elle est obligatoire pour continuer mais ne sera jamais visible sur le site par la suite.";

if(!empty($erreur)) {
    new_skinned_page('inscription/step2.tpl', AUTH_PUBLIC);
    require("applis.func.inc.php");
    $page->assign('homonyme', $homonyme);
    $page->assign('loginbis', isset($loginbis) ? $loginbis : '');
    $page->assign('mailorg', $mailorg);
    
    $page->assign('prenom', $prenom);
    $page->assign('nom', $nom);
    
    $page->assign('erreur', join("\n",$erreur));
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
	       SET  ins_id='$ins_id', password='$password', matricule='$matricule', promo='$promo',
	       nom='".addslashes($nom)."', prenom='".addslashes($prenom)."', email='{$_REQUEST['email']}',
	       naissance='{$_REQUEST['naissance']}', date='$date', nationalite='{$_REQUEST['nationalite']}',
	       appli_id1='{$_REQUEST['appli_id1']}', appli_type1='{$_REQUEST['appli_type1']}',
	       appli_id2='{$_REQUEST['appli_id2']}', appli_type2='{$_REQUEST['appli_type2']}',
	       loginbis='$mailorg', username='$forlife'";
$globals->db->query($sql);

$globals->db->query("UPDATE auth_user_md5 SET last_known_email='{$_REQUEST['email']}' WHERE matricule = $matricule");
// si on venait de la page maj.php, on met a jour la table envoidirect
if(isset($_REQUEST['envoidirect']))
    $globals->db->query("UPDATE envoidirect SET date_succes='NOW()' WHERE uid='{$_REQUEST['envoidirect']}'");

require("tpl.mailer.inc.php");
$mymail = new TplMailer('inscrire.mail.tpl');
$mymail->assign('forlife',$forlife);
$mymail->assign('lemail',$_REQUEST['email']);
$mymail->assign('pass_clair',$pass_clair);
$mymail->assign('baseurl',$baseurl);
$mymail->assign('ins_id',$ins_id);
$mymail->assign('subj',$forlife."@polytechnique.org");
$mymail->send();

new_skinned_page('inscription/step3.tpl', AUTH_PUBLIC);
$page->assign('mailorg', $mailorg);
$page->assign('forlife', $forlife);
$page->run();
?>
