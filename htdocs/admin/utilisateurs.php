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
        $Id: utilisateurs.php,v 1.16 2004-09-02 19:39:20 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/utilisateurs.tpl', true);
require("xorg.misc.inc.php");

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
    start_connexion($_SESSION['uid'],true);
    header("Location: ../");
}


/*
 * LE RESTE
 */

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
            $globals->db->query("INSERT INTO emails (uid,num,email,flags) 
				 VALUES ({$_REQUEST['user_id']},{$_REQUEST['num']},'$email','active')");
            $errors[] = "Ajout de $email effectué";
            break;

	case "del_fwd":
	    if(empty($val)) break;
	    $globals->db->query("DELETE FROM emails WHERE uid='{$_REQUEST['user_id']}' AND email='$val'");
	    break;

	case "del_alias":
	    if(empty($val)) break;
	    $globals->db->query("DELETE FROM aliases WHERE id='{$_REQUEST['user_id']}' AND alias='$val' AND type!='a_vie'");
	    $errors[] = $val." a été supprimé";
	    break;

	case "add_alias":
	    $globals->db->query("INSERT INTO aliases (id,alias,type)
				 VALUES ('{$_REQUEST['user_id']}','{$_REQUEST['email']}','alias')");
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

	    // FIXME: recherche
            $f = fopen("/tmp/flag_recherche","w");
            fputs($f,"1");
            fclose($f);

            $errors[] = "updaté correctement.";
            // envoi du mail au webmaster
            $HEADER="From: ADMINISTRATION\nReply-To: webmaster@polytechnique.org\nX-Mailer: PHP/" . phpversion();
            $MESSAGE="Intervention manuelle de l'administrateur login=".$_SESSION['username']." (UID=".$_SESSION['uid'].")\n\nOpérations effectuées\n\n\"".$query."\"\n\nCe rapport a été généré par le script d'administration";
            mail("web@polytechnique.org","INTERVENTION ADMIN",$MESSAGE,$HEADER);
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
	    $globals->db->query("delete from aliases where id=$user_id and type in ('login','epouse','alias')");
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
	    $HEADER="From: ADMINISTRATION\nReply-To: webmaster@polytechnique.org\nX-Mailer: PHP/" . phpversion();
	    $MESSAGE="Intervention manuelle de l'administrateur login=".$_SESSION['username']." (UID=".$_SESSION['uid'].")\n\nOpérations effectuées\n\n\"".$query."\"\n\nCe rapport a été généré par le script d'administration";
	    mail("web@polytechnique.org","INTERVENTION ADMIN",$MESSAGE,$HEADER);
            break;
    }
}


if (!empty($_REQUEST['login'])) {
    $login = $_REQUEST['login'];
    $r=$globals->db->query("SELECT  *
			      FROM  auth_user_md5 AS u
			INNER JOIN  aliases       AS a ON ( a.id = u.user_id AND a.alias='$login' )");
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

    $sql = "SELECT email, num, flags, panne
	    FROM emails
	    WHERE num != 0 AND uid = {$mr['user_id']} order by num";
    $result=$globals->db->query($sql);
    $xorgmails = Array();
    $email_panne = Array();
    while($l = mysql_fetch_assoc($result)) {
	$xorgmails[] = $l;
	if($l['panne']!="0000-00-00")
	    $email_panne[] = "Adresse {$l['email']} signalée comme HS le {$l['panne']}";
	$next_num = $l['num']+1;
    }
    mysql_free_result($result);

    $page->mysql_assign("SELECT  alias, type='a_vie' AS for_life
			   FROM  aliases
			  WHERE  id = {$mr["user_id"]}
		       ORDER BY  type!= 'a_vie'", 'aliases');
    $page->assign_by_ref('xorgmails', $xorgmails);
    $page->assign_by_ref('email_panne', $email_panne);
    $page->assign('next_num', $next_num);
}

$page->assign('errors',$errors);
$page->run();
?>
