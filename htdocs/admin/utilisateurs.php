<?php
require("auto.prepend.inc.php");
new_admin_page('admin/utilisateurs.tpl', true, 'admin/utilisateurs.head.tpl');
require("xorg.misc.inc.php");

$assignates = Array(
        'add_email', 'email', 'fwd', 'hashpass', 'homonyme',
        'login', 'loginbis', 'matricule', 'naissanceN', 'newpass_clair', 'nomN', 'num',
        'oldlogin', 'olduid', 'passw', 'password1', 'perms', 'permsN', 'prenomN', 'promoN',
        'remove_email', 'select', 'suid_button', 'user_id', 'u_edit',
        'u_kill', 'u_kill_conf'
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
    header("Location: logger.php?logauth=native&loguser={$_REQUEST['login']}");
}


/*
 * SUID
 */
if(isset($_REQUEST['suid_button']) and isset($_REQUEST['login'])
        and !isset($_SESSION['suid']) // pas de su imbriqués
  ) {
    $res = @mysql_query( "SELECT user_id,prenom,nom,promo,perms FROM auth_user_md5 WHERE username='{$_REQUEST['login']}'",$conn);
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
            mysql_query("INSERT INTO emails (uid,num,email,flags) VALUES ($user_id,$num,'$email','active')",$conn);
            my_msg("Ajout de $email effectué"); 
            break;

    // supprime un email

        case "remove_email":
            mysql_query("delete from emails where uid=$user_id and email = '$email'",$conn);
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
                        loginbis='$loginbis',
                        prenom='$prenomN',
                        nom='$nomN',
                        promo=$promoN,
                        alias='$alias'
                      WHERE user_id=$olduid";

            mysql_query($query,$conn);
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

            $result=mysql_query("select user_id from auth_user_md5 where username='$login'",$conn);
            if(list($user_id) = mysql_fetch_row($result)) {
                $query = "DELETE FROM auth_user_md5 WHERE username='$login'";
                mysql_query($query,$conn);
                mysql_query("delete from emails where uid=$user_id",$conn);
                mysql_query("delete from binets_ins where user_id=$user_id",$conn);
                mysql_query("delete from groupesx_ins where guid=$user_id",$conn);
                mysql_query("delete from photo where uid=$user_id",$conn);
                mysql_query("delete from perte_pass where uid=$user_id",$conn);
                mysql_query("delete from user_changes where user_id=$user_id",$conn);
                mysql_query("delete from aliases where id=$user_id and type in ('login','epouse','alias')",$conn);
                mysql_query("delete from listes_ins where idu=$user_id",$conn);
                mysql_query("delete from listes_mod where idu=$user_id",$conn);
                mysql_query("delete from forums_abo where uid=$user_id",$conn);
                mysql_query("delete from applis_ins where uid=$user_id",$conn);
                mysql_query("delete from contacts where uid=$user_id",$conn);
                mysql_query("delete from contacts where contact=$user_id",$conn);
                // on purge les entrees dans logger
                $res=mysql_query("select id from logger.sessions where uid=$user_id",$conn);
                while (list($session_id)=mysql_fetch_row($res)) 
                    mysql_query("delete from logger.events where session=$session_id",$conn);
                mysql_query("delete from logger.sessions where uid=$user_id",$conn);	

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
    $r=mysql_query("select * from auth_user_md5 where $looking_field='$login' order by username",$conn);
    if ($mr=mysql_fetch_assoc($r)){
        if ($numeric_login) $login = $mr['username'];
        $param=mysql_query("SELECT UNIX_TIMESTAMP(MAX(start)) FROM logger.sessions WHERE uid={$mr['user_id']} AND suid=0 GROUP BY uid'",$conn);
        list($lastlogin) = mysql_fetch_row($param);
        mysql_free_result($param);

        $page->assign_by_ref('mr',$mr);

        $str=false;
        
        $sql = "SELECT email, num, flags, panne
                FROM emails
                WHERE num != 0 AND uid = {$mr['user_id']} order by num";
        $result=mysql_query($sql,$conn);
        $xorgmails = Array();
        $email_panne = "";
        while($l = mysql_fetch_assoc($result)) {
            $xorgmails[] = $l;
            if($l['panne']!="0000-00-00")
                $email_panne .= "Adresse {$l['email']} signalée comme HS le {$l['panne']}<br />";
            $next_num = $l['num']+1;
        }
        mysql_free_result($result);
        
        $page->assign_by_ref('xorgmails', $xorgmails);
        $page->assign('email_panne', $email_panne);
        $page->assign('next_num', $next_num);
    } // if(mysql_fetch_row)
}

$page->run();
?>
