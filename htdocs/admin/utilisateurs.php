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
        $Id: utilisateurs.php,v 1.11 2004-09-01 21:15:00 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/utilisateurs.tpl', true, 'admin/utilisateurs.head.tpl');
require("xorg.misc.inc.php");

$assignates = Array(
	'add_email', 'email', 'fwd', 'alias', 'hashpass', 'homonyme', 'login',
	'matricule', 'naissanceN', 'newpass_clair', 'nomN', 'num', 'oldlogin', 'olduid',
	'passw', 'password1', 'perms', 'permsN', 'prenomN', 'promoN', 'remove_email',
	'select', 'suid_button', 'user_id', 'u_edit', 'u_kill', 'u_kill_conf'
);
foreach($assignates as $ass) $$ass=isset($_REQUEST[$ass]) ? $_REQUEST[$ass] : '';

$errors = Array();
$succes = Array();
function my_error($msg) { global $erreur; $erreur[] = "<p class='erreur'>Erreur: $msg</p>"; }
function my_msg($msg)   { global $succes; $succes[] = "<p class='succes'>O.K.: $msg</p>"; }

/*
 * LOGS de l'utilisateur
 */

if(isset($_REQUEST['logs_button'])) {
    header("Location: logger.php?loguser={$_REQUEST['login']}&year=".date('Y')."&month=".date('m'));
}


/*
 * SUID
 */
if(isset($_REQUEST['suid_button']) and isset($_REQUEST['login'])
        and !isset($_SESSION['suid']) // pas de su imbriqués
  ) {
    $res = @$globals->db->query( "SELECT user_id,prenom,nom,promo,perms FROM auth_user_md5 WHERE username='{$_REQUEST['login']}'");
    if(@mysql_num_rows($res) != 0) {
        list($uid,$prenom,$nom,$promo,$perms)=mysql_fetch_row($res);
        // on déplace le log de l'admin dans slog, et on crée un log de suid en log
        // on loggue le démarrage de la session suid pour l'admin et l'utilisateur
        $log_data = $_REQUEST['login']." by ".$_SESSION['username'];
        $_SESSION['log']->log("suid_start",$log_data);
        $_SESSION['slog'] = $_SESSION['log'];
        $_SESSION['log'] = new DiogenesCoreLogger($uid,$_SESSION['uid']);
        $_SESSION['log']->log("suid_start",$log_data);
        // on modifie les variables de session suffisantes pour faire un su
        // rem : la skin n'est pas modifiée
        $_SESSION['suid'] = $_SESSION['uid'];
        $_SESSION['username'] = $_REQUEST['login'];
        $_SESSION['perms'] = $perms;
        $_SESSION['uid'] = $uid;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['promo'] = $promo;
    }
    header("Location: ../");
}

if(isset($_REQUEST['password']))  $pass_clair = $_REQUEST['password'];

// Check if there was a submission
foreach($_POST as $key => $val) {
    switch ($key) {
    // ajout d'email
        case "add_email":
            if (!isvalid_email_redirection($email)) {
                my_error("invalid email");
                break;
            }
            $globals->db->query("INSERT INTO emails (uid,num,email,flags) VALUES ($user_id,$num,'$email','active')");
            my_msg("Ajout de $email effectué"); 
            break;

    // supprime un email

        case "remove_email":
            $globals->db->query("delete from emails where uid=$user_id and email = '$email'");
            my_msg("Suppression de $email effectué"); 
            break;

    // Faire un suid (une partie du code se trouve tout là-haut pour affecter l'affichage du menu)
        case "suid_button":
            if(isset($_SESSION['suid'])) {
                my_msg("SUID effectué, clique sur exit pour quitter."); 
            } else {
                my_error("login inconnu, suid non effectué.");
            }
            break;


        // Editer un profil
        case "u_edit":
            if ($newpass_clair != "********")  {
                $pass_md5B=md5($newpass_clair);
            } else {
                $pass_md5B=$passw;
            }

            $query = "UPDATE auth_user_md5 SET
                        username='$login',
                        naissance=$naissanceN,
                        password='$pass_md5B',
                        perms='$permsN',
                        prenom='$prenomN',
                        nom='$nomN',
                        promo=$promoN,
                        alias='$alias'
                      WHERE user_id=$olduid";

            $globals->db->query($query);
            if (mysql_errno($conn) != 0) {
                my_error("<b>Failed:</b> $query");
                break;
            }
            
            $f = fopen("/tmp/flag_recherche","w");
            fputs($f,"1");
            fclose($f);

            my_msg("\"$login\" updaté correctement.");
            // envoi du mail au webmaster
            $HEADER="From: ADMINISTRATION\nReply-To: webmaster@polytechnique.org\nX-Mailer: PHP/" . phpversion();
            $MESSAGE="Intervention manuelle de l'administrateur login=".$_SESSION['username']." (UID=".$_SESSION['uid'].")\n\nOpérations effectuées\n\n\"".$query."\"\n\nCe rapport a été généré par le script d'administration";
            mail("web@polytechnique.org","INTERVENTION ADMIN",$MESSAGE,$HEADER);
            break;

    // DELETE FROM auth_user_md5
        case "u_kill":

            $result=$globals->db->query("select user_id from auth_user_md5 where username='$login'");
            if(list($user_id) = mysql_fetch_row($result)) {
                $query = "DELETE FROM auth_user_md5 WHERE username='$login'";
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
                $globals->db->query("delete from forums_abo where uid=$user_id");
                $globals->db->query("delete from applis_ins where uid=$user_id");
                $globals->db->query("delete from contacts where uid=$user_id");
                $globals->db->query("delete from contacts where contact=$user_id");
                // on purge les entrees dans logger
                $res=$globals->db->query("select id from logger.sessions where uid=$user_id");
                while (list($session_id)=mysql_fetch_row($res)) 
                    $globals->db->query("delete from logger.events where session=$session_id");
                $globals->db->query("delete from logger.sessions where uid=$user_id");	

                my_msg(" \"$login\" a été supprimé !<BR>");
                $HEADER="From: ADMINISTRATION\nReply-To: webmaster@polytechnique.org\nX-Mailer: PHP/" . phpversion();
                $MESSAGE="Intervention manuelle de l'administrateur login=".$_SESSION['username']." (UID=".$_SESSION['uid'].")\n\nOpérations effectuées\n\n\"".$query."\"\n\nCe rapport a été généré par le script d'administration";
                mail("web@polytechnique.org","INTERVENTION ADMIN",$MESSAGE,$HEADER);
            } else {
                my_error("pas de login $login");
            }
            break;
    }
}


$page->assign('login', $login);

if (!empty($_REQUEST['select'])) {
    $numeric_login = false;
    $looking_field = 'username';
    if (preg_match("/^\d*$/",$login)) {
        $numeric_login = true;
        $looking_field = 'user_id';
    }
    $r=$globals->db->query("select * from auth_user_md5 where $looking_field='$login' order by username");
    if ($mr=mysql_fetch_assoc($r)){
        if ($numeric_login) $login = $mr['username'];
        $param=$globals->db->query("SELECT UNIX_TIMESTAMP(MAX(start)) FROM logger.sessions WHERE uid={$mr['user_id']} AND suid=0 GROUP BY uid");
        list($lastlogin) = mysql_fetch_row($param);
        mysql_free_result($param);

        $page->assign_by_ref('mr',$mr);

        $str=false;
        
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
       
	$page->mysql_assign("SELECT alias FROM aliases WHERE id = {$mr["user_id"]}", 'aliases');
        $page->assign_by_ref('xorgmails', $xorgmails);
        $page->assign_by_ref('email_panne', $email_panne);
        $page->assign('next_num', $next_num);
    } // if(mysql_fetch_row)
}

$page->run();
?>
