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
        $Id: step4.php,v 1.5 2004-09-01 20:56:26 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('step4.tpl', AUTH_PUBLIC);

require("inscription_listes_base.inc.php");
require("inscription_forums_base.inc.php");
require('tpl.mailer.inc.php');

define("ERROR_REF", 1);
define("ERROR_ALREADY_SUBSCRIBED", 2);
define("ERROR_DB", 3);

if (!empty($_REQUEST['ref'])) {
    $sql = "SELECT username,loginbis,matricule,promo,password".
	        ",nom,prenom,nationalite,email,naissance,date".
	        ",appli_id1,appli_type1,appli_id2,appli_type2".
	        " FROM en_cours WHERE ins_id='".$_REQUEST["ref"]."'";
    $res = $globals->db->query($sql);
    //vérifions que la référence de l'utilisateur est valide
    if (!list($username, $loginbis, $matricule, $promo, $password, $nom, $prenom,$nationalite, 
    $email, $naissance,$date,$appli_id1,$appli_type1,$appli_id2,$appli_type2) = mysql_fetch_row($res)) {
        $page->assign('error',ERROR_REF);
        $page->run();
    }
    $page->assign('username',$username);
    
    // vérifions qu'il n'y a pas déjà une inscription dans le passé
    // ce qui est courant car les double-clic...
    $sql = "SELECT user_id FROM auth_user_md5 where username='$username'";
    $res = $globals->db->query($sql);      
    if ( mysql_num_rows($res) != 0)  {
        $page->assign('error',ERROR_ALREADY_SUBSCRIBED);
        $page->run();
    }
    
    $nom = stripslashes($nom);
    $prenom = stripslashes($prenom);
    $sql = "INSERT INTO auth_user_md5 SET username='$username',loginbis='$loginbis',matricule='$matricule',promo=$promo,
            password='$password',nom='".addslashes($nom)."',prenom='".addslashes($prenom)."',nationalite=$nationalite,
            date='$date',naissance=$naissance, date_ins = NULL";
    $globals->db->query($sql);

    // on vérifie qu'il n'y a pas eu d'erreur
    if ($globals->db->err()) {
        $page->assign('error',ERROR_DB);
        $page->assign('error_db',$globals->db->error());
        $page->run();
    }
    // ok, pas d'erreur, on continue
    $uid=$globals->db->insert_id();

    /* insertion des alias dans la table de courrier */
    if (empty($loginbis)) {
	$globals->db->query("INSERT INTO aliases VALUES ('$username', 'alias', $uid)");
	// le suivant n'est pas formellement un login, mais c'est celui qu'on utilise
	// lorsque l'on envoit des mails à la personne (prenom.nom.promo) afin qu'elle
	// comprenne que c'est là sa vraie adresse à vie
	$globals->db->query("INSERT INTO aliases VALUES ('$username.$promo', 'login', $uid)");
	$globals->db->query("INSERT INTO aliases VALUES ('$username.".($promo%100)."', 'alias', $uid)");
    } else {
	$globals->db->query("INSERT INTO aliases VALUES ('$username', 'alias', $uid)");
	// le suivant n'est pas formellement un login, mais c'est celui qu'on utilise
	// lorsque l'on envoit des mails à la personne (prenom.nom.promo) afin qu'elle
	// comprenne que c'est là sa vraie adresse à vie
	$globals->db->query("INSERT INTO aliases VALUES ('$loginbis.$promo', 'login', $uid)");
	$globals->db->query("INSERT INTO aliases VALUES ('$loginbis.".($promo%100)."', 'alias', $uid)");
    }
	

    // on cree un objet logger et on log l'evenement
    $logger = new DiogenesCoreLogger($uid);
    $logger->log("inscription",$email);

    /****************** insertion de l'email dans la table emails ***/
    require("mtic.inc.php");
    $flags = 'filtre'.(check_mtic($email) ? ',mtic':'');
    $sql = "insert into emails set uid = $uid, num = 0, email = '\"|maildrop /var/mail/.maildrop_filters/tag_spams $uid\"', flags = 'active'";
    $globals->db->query($sql);
    $sql = "insert into emails set uid = $uid, num = 1, email = '$email', flags = '$flags'";
    $globals->db->query($sql);
    /****************** ajout des formations ****************/
    if (($appli_id1>0)&&($appli_type1))
        $globals->db->query("insert into applis_ins set uid=$uid,aid=$appli_id1,type='$appli_type1',ordre=0");
    if (($appli_id2>0)&&($appli_type2))
        $globals->db->query("insert into applis_ins set uid=$uid,aid=$appli_id2,type='$appli_type2',ordre=1");
    /****************** envoi d'un mail au démarcheur ***************/
    /* si la personne a été marketingnisée, alors on prévient son démarcheur */
    $res = $globals->db->query("SELECT a.username,e.date_envoi FROM envoidirect as e INNER JOIN auth_user_md5 as a ON e.sender = a.user_id 
                                WHERE e.matricule = '".$matricule."' GROUP BY a.username");
    while (list($sender_usern, $sender_date) = mysql_fetch_row($res)) {
        $mymail = new TplMailer('marketing.thanks.tpl');
        $mymail->assign('to', $sender_usern);
        $mymail->assign('prenom', $prenom);
        $mymail->assign('nom',$nom);
        $mymail->assign('promo',$promo);
        $mymail->send();
    }
    
    /****************** inscription à la liste promo ****************/
    $inspromo = inscription_liste_promo($uid,$promo);
    /****************** inscription à la newsletter  ****************/
    $insnewsletter = inscription_newsletter($uid);
    /****************** inscription à la newsletter  ****************/
    $insforumpromo = inscription_forum_promo($uid,$promo);
    /****************** inscription à la newsletter  ****************/
    $insforums = inscription_forums($uid);

    // effacer la pré-inscription devenue inscription
    $globals->db->query("update en_cours set loginbis='INSCRIT' WHERE username='$username'");

    // insérer l'inscription dans la table des inscriptions confirmées
    $globals->db->query("INSERT INTO ins_confirmees SET id=$uid");

    // insérer une ligne dans user_changes pour que les coordonnées complètes
    // soient envoyées a l'AX
    $globals->db->query("insert into user_changes ($uid)");

    // envoi du mail à l'utilisateur
    $mymail = new TplMailer('inscription.reussie.tpl');
    $mymail->assign('username', $username);
    $mymail->assign('prenom', $prenom);
    $mymail->send();

    // s'il est dans la table envoidirect, on le marque comme inscrit
    $globals->db->query("update envoidirect set date_succes=NOW() where matricule = $matricule");
}
else
    $page->assign('error',ERROR_REF);

$page->assign('dev',(isset($site_dev) && $site_dev)?1:0);
$page->run();
