<?
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
        $Id: step4.php,v 1.10 2004-11-07 11:54:07 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('inscription/step4.tpl', AUTH_PUBLIC);

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
    //vérifions que la référence de l'utilisateur est 
    if (!list($forlife, $alias, $matricule, $promo, $password, $nom, $prenom,$nationalite, 
              $email, $naissance,$date,$appli_id1,$appli_type1,$appli_id2,$appli_type2) = mysql_fetch_row($res)) {
        $page->assign('error',ERROR_REF);
        $page->run();
    }
    $page->assign('forlife',$forlife);
    
    // vérifions qu'il n'y a pas déjà une inscription dans le passé
    // ce qui est courant car les double-clic...
    $res = $globals->db->query("SELECT alias FROM aliases WHERE alias='$forlife'");
    if ( mysql_num_rows($res) != 0)  {
        $page->assign('error',ERROR_ALREADY_SUBSCRIBED);
        $page->run();
    }
    
    $nom = stripslashes($nom);
    $prenom = stripslashes($prenom);
    $sql = "UPDATE auth_user_md5 SET password='$password', nationalite=$nationalite, perms='user',
            date='$date', naissance=$naissance, date_ins = NULL WHERE matricule='$matricule'";
    $globals->db->query($sql);
    
    // on vérifie qu'il n'y a pas eu d'erreur
    if ($globals->db->err()) {
        $page->assign('error',ERROR_DB);
        $page->assign('error_db',$globals->db->error());
        $page->run();
    }
    // ok, pas d'erreur, on continue
    $resbis=$globals->db->query("SELECT user_id FROM auth_user_md5 WHERE matricule='$matricule'");
    if ((list($uid) = mysql_fetch_row($resbis)) === false) {
        $page->assign('error',ERROR_DB);
        $page->assign('error_db',$globals->db->error());
        $page->run();
    }

    $globals->db->query("INSERT INTO aliases (id,alias,type) VALUES ($uid,'$forlife','a_vie')");
    if($alias) {
	$p2 = sprintf("%02i",($promo%100));
	$globals->db->query("INSERT INTO aliases (id,alias,type) VALUES ($uid,'$alias','alias')");
	$globals->db->query("INSERT INTO aliases (id,alias,type) VALUES ($uid,'$alias.$p2','alias')");
    }

    // on cree un objet logger et on log l'
    $logger = new DiogenesCoreLogger($uid);
    $logger->log("inscription",$email);

    /****************** insertion de l'email dans la table emails + bogofilter ***/
    require("email.classes.inc.php");
    $redirect = new Redirect($uid);
    $redirect->add_email($email);
    /****************** ajout des formations ****************/
    if (($appli_id1>0)&&($appli_type1))
        $globals->db->query("insert into applis_ins set uid=$uid,aid=$appli_id1,type='$appli_type1',ordre=0");
    if (($appli_id2>0)&&($appli_type2))
        $globals->db->query("insert into applis_ins set uid=$uid,aid=$appli_id2,type='$appli_type2',ordre=1");
    /****************** envoi d'un mail au démarcheur ***************/
    /* si la personne a été marketingnisée, alors on prévient son démarcheur */
    $res = $globals->db->query("SELECT  DISTINCT a.alias,e.date_envoi
                                  FROM  envoidirect AS e
			    INNER JOIN  aliases     AS a ON ( a.id = e.sender AND a.type='a_vie' )
                                 WHERE  e.matricule = '$matricule'");
    while (list($sender_usern, $sender_date) = mysql_fetch_row($res)) {
        $mymail = new TplMailer('marketing.thanks.tpl');
        $mymail->assign('to', $sender_usern);
        $mymail->assign('prenom', $prenom);
        $mymail->assign('nom',$nom);
        $mymail->assign('promo',$promo);
        $mymail->send();
    }
   

    /****************** inscription à la liste promo +nl ****************/
    $inspromo = inscription_listes_base($uid,$password,$promo);
    /****************** inscription aux forums de base   ****************/
    $insforumpromo = inscription_forum_promo($uid,$promo);
    $insforums = inscription_forums($uid);

    // effacer la pré-inscription devenue 
    $globals->db->query("update en_cours set loginbis='INSCRIT' WHERE username='$forlife'");

    // insérer l'inscription dans la table des inscriptions confirmé
    $globals->db->query("INSERT INTO ins_confirmees SET id=$uid");
    require_once('notifs.inc.php');
    register_watch_op($uid,WATCH_INSCR);
    inscription_notifs_base($uid);

    // insérer une ligne dans user_changes pour que les coordonnées complè
    // soient envoyées a l'
    $globals->db->query("insert into user_changes ($uid)");

    // envoi du mail à l'
    $mymail = new TplMailer('inscription.reussie.tpl');
    $mymail->assign('forlife', $forlife);
    $mymail->assign('prenom', $prenom);
    $mymail->send();

    // s'il est dans la table envoidirect, on le marque comme 
    $globals->db->query("update envoidirect set date_succes=NOW() where matricule = $matricule");
    start_connexion($uid,false);
} else
    $page->assign('error',ERROR_REF);

$page->assign('dev',(isset($site_dev) && $site_dev)?1:0);
$page->run();
?>
