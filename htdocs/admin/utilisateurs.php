<?php
require("auto.prepend.inc.php");
new_admin_page('admin/utilisateurs.tpl', true, 'admin/utilisateurs.head.tpl');
require("db_connectpolyedu.inc.php");
require("xorg.misc.inc.php");

$assignates = Array(
        'add_email', 'add_polyedu_alias', 'aliasalias_edu', 'email', 'fwd', 'hashpass', 'homonyme',
        'id_edu', 'login', 'loginbis', 'matricule', 'naissanceN', 'newpass_clair', 'nomN', 'num',
        'oldlogin', 'olduid', 'passw', 'password1', 'perms', 'permsN', 'prenomN', 'promoN',
        'remove_email', 'remove_polyedu_alias', 'select', 'suid_button', 'user_id', 'u_edit',
        'u_kill', 'u_kill_conf'
);
foreach($assignates as $ass) $$ass=isset($_REQUEST[$ass]) ? $_REQUEST[$ass] : '';

$errors = Array();
$succes = Array();
function my_error($msg) { global $erreur; $erreur[] = "<p class='erreur'>Erreur: $msg</p>"; }
function my_msg($msg)   { global $succes; $succes[] = "<p class='succes'>O.K.: $msg</p>"; }


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

    // ajoute un alias sur polyedu
        case "add_polyedu_alias":
            $db_edu = connect_polyedu();
            if($db_edu) {
            // récupération de l'id_edu
                $result=mysql_query("select id from x where matricule='$matricule'",$db_edu);
                $id_edu=false;
                if(!$result) {
                    my_error("Erreur select dans x: ".mysql_error($db_edu));
                } elseif (mysql_num_rows($result) == 0) {
                    // pas d'X du matricule correspondant dans la base !
                    // il faut l'ajouter
                    $id_edu=0;
                } elseif (list($id_edu) = mysql_fetch_row($result)) {
                    // rien à faire, id_edu a la bonne valeur
                } else {
                    my_error("Impossible d'obtenir l'id_edu, recommence ".mysql_error($db_edu));
                }
                if($result) mysql_free_result($result);
                if(is_bool($id_edu) and !$id_edu) {
                    mysql_close($db_edu);
                    break;
                }
                // ajout de l'entrée dans aliases
                if($alias_edu == '') {
                    $alias_edu = $login;
                }
                // vérification de la présence d'un alias
                $alias_exist=false;
                $alias_pris=false;
                $exist_id_edu=0;
                $result = mysql_query("select a.id, x.matricule, u.prenom, u.nom from aliases as a LEFT JOIN x USING(id) LEFT JOIN users as u ON(u.id=a.id) where a.alias = '$alias_edu'",$db_edu);
                if(!$result) {
                    my_error("Erreur select dans aliases, x et users: ".mysql_error($db_edu));
                    mysql_close($db_edu);
                    break;
                } elseif (mysql_num_rows($result) == 0) {
                    // pas d'alias, il faut l'ajouter
                } elseif (list($exist_id_edu,$xmatricule_edu,$prenom_edu, $nom_edu) = mysql_fetch_row($result)) {
                    $alias_exist=true;
                    if(isset($xmatricule_edu) and $xmatricule_edu != $matricule) {
                        my_error("Alias $login déjà pris par un autre X : $xmatricule_edu");
                        $alias_pris=true;
                    } elseif(isset($nom_edu)) {
                        my_error("Alias $login déjà pris par un non-X : $prenom_edu $nom_edu ");
                        $alias_pris=true;
                    }
                } else {
                    my_error("Impossible d'obtenir l'alias_edu, recommence ".mysql_error($db_edu));
                    mysql_close($db_edu);
                    break;
                }
                if($result) mysql_free_result($result);
                if($alias_pris) {
                    mysql_close($db_edu);
                    break;
                }
                if(!$alias_exist) { // l'alias n'existe pas, on l'ajoute
                    mysql_query("insert into aliases (id,type,alias) VALUES ($id_edu,'X','$alias_edu')",$db_edu);
                    if (mysql_errno($db_edu) != 0) {
                        my_error("Failed: ".mysql_errno($db_edu).", ".mysql_error($db_edu));
                        mysql_close($db_edu);
                        break;
                    }
                    $exist_id_edu = ($id_edu?$id_edu:mysql_insert_id($db_edu));
                }
                // ajout de l'entrée dans la table X si nécessaire
                // arrive typiquement en mode réparation
                if ($id_edu == 0) {
                    // il faut ajouter l'enregistrement dans la table X
                    mysql_query("insert into x (id,matricule) values ($exist_id_edu,$matricule)",$db_edu);
                    if (mysql_errno($db_edu) != 0) {
                        my_error("Erreur ajout dans la table X: ".mysql_errno($db_edu).", ".mysql_error($db_edu));
                        mysql_close($db_edu);
                        break;
                    }
                    $id_edu = $exist_id_edu;
                }
                // on vérifie que le champ email est bien présent dans la table emails
                $result=mysql_query("select email, flags, FIND_IN_SET('active', flags) from emails where id='$id_edu'",$db_edu);
                if(!$result) {
                    my_error("Erreur select dans emails: ".mysql_error($db_edu));
                    mysql_close($db_edu);
                    break;
                }
                if (list($email_edu, $flags_edu, $active_edu) = mysql_fetch_row($result)
                        and $email_edu == ($login."@m4x.org")
                        and $active_edu != 0) {
                    // c'est ok
                    my_msg("Ajout de $alias_edu sur polyedu effectué"); 
                    mysql_free_result($result);
                    mysql_close($db_edu);
                    break;
                }
                mysql_free_result($result);
                // pas d'email ou mauvais email pour l'X
                // il faut supprimer l'ancien et ajouter le nouveau
                mysql_query("delete from emails where id = $id_edu",$db_edu);
                mysql_query("insert into emails (id,email,flags) values ($id_edu,'$login@m4x.org','active,$flags_edu')",$db_edu);
                if (mysql_errno($db_edu) != 0) {
                    my_error("Erreur ajout dans la table email: ".mysql_errno($db_edu).", ".mysql_error($db_edu));
                } else {
                    my_msg("Ajout de $alias_edu et email sur polyedu effectué"); 
                }
                mysql_close($db_edu);
            } // if($db_edu)
            else {
                my_error("Connexion à la BD polyedu impossible");
            } // if(!$db_edu)
            break;

    // supprime un alias sur polyedu
        case "remove_polyedu_alias":
            $db_edu = connect_polyedu();
            if($db_edu) {
                mysql_query("delete from aliases where id='$id_edu' and alias = '$alias_edu'",$db_edu);
                if (mysql_errno($db_edu) != 0) {
                    my_error("Failed: ".mysql_error($db_edu));
                } else {
                    my_msg("Suppression de $alias_edu effectué"); 
                }
                mysql_close($db_edu);
            } // if($db_edu)
            else {
                my_error("Connexion à la BD polyedu impossible: ".mysql_error($db_edu));
            } // if(!$db_edu)
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
        $param=mysql_query("SELECT UNIX_TIMESTAMP(lastlogin) FROM auth_user_md5 WHERE username='$login'",$conn);
        list($lastlogin) = mysql_fetch_row($param);
        mysql_free_result($param);

        $page->assign_by_ref('mr',$mr);

        $str=false;
        
        $db_edu = connect_polyedu();
        if($db_edu) {
            $page->assign('db_edu', 1);
            $result=mysql_query("SELECT x.id, a.alias, e.email, FIND_IN_SET('active', e.flags) AS act
                                 FROM x LEFT JOIN aliases AS a USING(id)
                                 LEFT JOIN emails as e ON(e.id=x.id)
                                 WHERE x.matricule = {$mr['matricule']}",$db_edu);
            if(!$result) {
                $str="Erreur sur la requ&ecirc;te: ".mysql_error($db_edu);
            } elseif(mysql_num_rows($result) == 0) {
                $str="Pas d'entrée dans la base !";
            } else {
                $alias_edu = Array();
                while($alias_edu[] = mysql_fetch_assoc($result));
                array_pop($alias_edu);
                $page->assign_by_ref('alias_edu', $alias_edu);
            } // mysql_num_rows != 0
            mysql_free_result($result);
            mysql_close($db_edu);
        }

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

$page->display();
?>
