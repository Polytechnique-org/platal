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
        $Id: utilisateurs.php,v 1.30 2004-11-17 10:49:50 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/utilisateurs.tpl');
require("email.classes.inc.php");

/*
 * LOGS de l'utilisateur
 */

if(isset($_REQUEST['logs_button'])) {
    header("Location: logger.php?loguser={$_REQUEST['login']}&year=".date('Y')."&month=".date('m'));
}


/*
 * SUID
 */
if(isset($_REQUEST['suid_button']) and isset($_REQUEST['login']) and !isset($_SESSION['suid'])) {
    $log_data = $_REQUEST['login']." by ".$_SESSION['forlife'];
    $_SESSION['log']->log("suid_start",$log_data);
    $_SESSION['slog'] = $_SESSION['log'];
    $_SESSION['suid'] = $_SESSION['uid'];
    $r=$globals->db->query("SELECT id FROM aliases WHERE alias='{$_REQUEST['login']}'");
    if(list($uid) = mysql_fetch_row($r)) {
	start_connexion($uid,true);
	header("Location: ../");
    }
    mysql_free_result($r);
}


/*
 * LE RESTE
 */

if (!empty($_REQUEST['login'])) {
    $login = $_REQUEST['login'];
    $r=$globals->db->query("SELECT  *
			      FROM  auth_user_md5 AS u
			INNER JOIN  aliases       AS a ON ( a.id = u.user_id AND a.alias='$login' AND type!='homonyme' )");
    if($tmp = mysql_fetch_assoc($r)) $mr=$tmp;
    mysql_free_result($r);
}

if (!empty($_REQUEST['user_id'])) {
    $r=$globals->db->query("SELECT  *
			      FROM  auth_user_md5
 			     WHERE  user_id='{$_REQUEST['user_id']}'");
    if($tmp = mysql_fetch_assoc($r)) $mr=$tmp;
    mysql_free_result($r);
}

if(isset($mr)) {
    $redirect = new Redirect($mr['user_id']);

    $errors = Array();

    if(isset($_REQUEST['password']))  $pass_clair = $_REQUEST['password'];

    // Check if there was a submission
    foreach($_POST as $key => $val) {
	switch ($key) {
	    case "add_fwd":
		$email = $_REQUEST['email'];
		if (!isvalid_email_redirection($email)) {
		    $errors[] = "invalid email $email";
		    break;
		}
		$redirect->add_email(trim($email));
		$errors[] = "Ajout de $email effectué";
		break;

	    case "del_fwd":
		if(empty($val)) break;
		$redirect->delete_email($val);
		break;

	    case "del_alias":
		if(empty($val)) break;
		$globals->db->query("DELETE FROM aliases WHERE id='{$_REQUEST['user_id']}' AND alias='$val'
								AND type!='a_vie' AND type!='homonyme'");
		fix_bestalias($_REQUEST['user_id']);
		$errors[] = $val." a été supprimé";
		break;

	    case "add_alias":
		$globals->db->query("INSERT INTO aliases (id,alias,type)
				     VALUES ('{$_REQUEST['user_id']}','{$_REQUEST['email']}','alias')");
		break;

	    case "best":
		$globals->db->query("UPDATE  aliases SET flags='' WHERE flags='bestalias' AND id='{$_REQUEST['user_id']}'");
		$globals->db->query("UPDATE  aliases SET flags='epouse' WHERE flags='epouse,bestalias' AND id='{$_REQUEST['user_id']}'");
		$globals->db->query("UPDATE  aliases
					SET  flags=CONCAT(flags,',','bestalias')
				      WHERE  id='{$_REQUEST['user_id']}' AND alias='$val'");
		break;


	    // Editer un profil
	    case "u_edit":
		$pass_md5B = $_REQUEST['newpass_clair'] != "********" ? md5($_REQUEST['newpass_clair']) : $_REQUEST['passw'];

		$query = "UPDATE auth_user_md5 SET
			    naissance='{$_REQUEST['naissanceN']}',
			    password='$pass_md5B',
			    perms='{$_REQUEST['permsN']}',
			    prenom='{$_REQUEST['prenomN']}',
			    nom='{$_REQUEST['nomN']}',
			    promo='{$_REQUEST['promoN']}'
			  WHERE user_id='{$_REQUEST['user_id']}'";
		$globals->db->query($query);
		$r=$globals->db->query("SELECT  *
					  FROM  auth_user_md5 AS u
				    INNER JOIN  aliases       AS a ON ( a.id = u.user_id AND a.id='{$_REQUEST['user_id']}' )");
		if($tmp = mysql_fetch_assoc($r)) $mr=$tmp;
		mysql_free_result($r);

		// FIXME: recherche
		$f = fopen("/tmp/flag_recherche","w");
		fputs($f,"1");
		fclose($f);

		$errors[] = "updaté correctement.";
		// envoi du mail au webmaster
		require_once("diogenes.hermes.inc.php");
		$mailer = new HermesMailer();
		$mailer->setFrom("webmaster@polytechnique.org");
		$msg = "Intervention manuelle de l'administrateur login=".$_SESSION['forlife']." (UID=".$_SESSION['uid'].")\n\n"
		    .  "Opérations effectuées\n\n\"".$query
		    .  "\"\n\nCe rapport a été généré par le script d'administration";
		$mailer->addTo("web@polytechnique.org");
		$mailer->setSubject("INTERVENTION ADMIN",$msg);
		$mailer->send();
		break;

	// DELETE FROM auth_user_md5
	    case "u_kill":

		$user_id = $_REQUEST['user_id'];

		$query = "DELETE FROM auth_user_md5 WHERE user_id='$user_id'";
		$globals->db->query($query);
		$globals->db->query("delete from emails where uid=$user_id");
		$globals->db->query("delete from binets_ins where user_id=$user_id");
		$globals->db->query("delete from groupesx_ins where guid=$user_id");
		$globals->db->query("delete from photo where uid=$user_id");
		$globals->db->query("delete from perte_pass where uid=$user_id");
		$globals->db->query("delete from user_changes where user_id=$user_id");
		$globals->db->query("delete from aliases where id=$user_id and type in ('a_vie','alias')");
		$globals->db->query("delete from listes_ins where idu=$user_id");
		$globals->db->query("delete from listes_mod where idu=$user_id");
		$globals->db->query("delete from applis_ins where uid=$user_id");
		$globals->db->query("delete from contacts where uid=$user_id");
		$globals->db->query("delete from contacts where contact=$user_id");
		// on purge les entrees dans logger
		$res=$globals->db->query("select id from logger.sessions where uid=$user_id");
		while (list($session_id)=mysql_fetch_row($res))
		    $globals->db->query("delete from logger.events where session=$session_id");
		$globals->db->query("delete from logger.sessions where uid=$user_id");	

		$errors[] = "'$user_id' a été supprimé !";
		require_once("diogenes.hermes.inc.php");
		$mailer = new HermesMailer();
		$mailer->setFrom("webmaster@polytechnique.org");
		$msg = "Intervention manuelle de l'administrateur login=".$_SESSION['forlife']." (UID=".$_SESSION['uid'].")\n\n"
		    .  "Opérations effectuées\n\n\"".$query
		    .  "\"\n\nCe rapport a été généré par le script d'administration";
		$mailer->addTo("web@polytechnique.org");
		$mailer->setSubject("INTERVENTION ADMIN",$msg);
		$mailer->send();
		break;
	}
    }


    
    $r=$globals->db->query("SELECT alias FROM aliases WHERE ( id = {$mr['user_id']} AND type='a_vie' )");
    list($forlife) = mysql_fetch_row($r);
    mysql_free_result($r);
    $mr['forlife'] = $forlife;
    $page->assign('mr',$mr);

    $result=$globals->db->query("SELECT  UNIX_TIMESTAMP(s.start), s.host
				   FROM  auth_user_md5   AS u
			      LEFT JOIN  logger.sessions AS s ON(s.uid=u.user_id AND s.suid=0)
				  WHERE  user_id={$mr['user_id']}
			       ORDER BY  s.start DESC
				  LIMIT  1");
    list($lastlogin,$host) = mysql_fetch_row($result);
    mysql_free_result($result);
    $page->assign('lastlogin', $lastlogin);
    $page->assign('host', $host);

    $page->mysql_assign("SELECT  alias, type='a_vie' AS for_life,FIND_IN_SET('bestalias',flags) AS best,expire
			   FROM  aliases
			  WHERE  id = {$mr["user_id"]} AND type!='homonyme'
		       ORDER BY  type!= 'a_vie'", 'aliases');
    $page->assign_by_ref('xorgmails', $xorgmails);
    $page->assign_by_ref('email_panne', $email_panne);    
    $page->assign('emails',$redirect->emails);
    $page->assign('errors',$errors);
}

$page->run();
?>
