<?php
require("auto.prepend.inc.php");
new_skinned_page('index.tpl',AUTH_MDP);

if (isset($_SESSION['suid'])) {
    $res = @$globals->db->query( "SELECT username,prenom,nom,promo,perms FROM auth_user_md5 WHERE user_id='{$_SESSION['suid']}'");
    if(@mysql_num_rows($res) != 0) {
        list($username,$prenom,$nom,$promo,$perms)=mysql_fetch_row($res);
        // on rétablit les loggers
        // on loggue la fermeture de la session de su
        $log_data = $_SESSION['username']." by ".$username;
        $_SESSION['log']->log("suid_stop",$log_data);
        $_SESSION['log'] = $_SESSION['slog'];
        unset($_SESSION['slog']);
        $_SESSION['log']->log("suid_stop",$log_data);
        // on remet en place les variables de sessions modifiées par le su
        $_SESSION['uid']  = $_SESSION['suid'];
        unset($_SESSION['suid']);
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['promo'] = $promo;
        $_SESSION['username'] = $username;
        $_SESSION['perms'] = $perms;
    }
}

header("Location: login.php");
?>
