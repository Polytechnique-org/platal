<?php
require("auto.prepend.inc.php");
new_skinned_page('acces_smtp.tpl', AUTH_MDP);

if (isset($_REQUEST['op']) && $_REQUEST['op'] == "Valider"
        && isset($_REQUEST['smtppass1']) && isset($_REQUEST['smtppass2'])
        && $_REQUEST['smtppass1'] == $_REQUEST['smtppass2']
        && strlen($_REQUEST['smtppass1'])>=6) {

    // on change le mot de passe
    $result = $globals->db->query("select smtppass from auth_user_md5 where user_id = ".$_SESSION['uid']);
    list($smtppass_old) = mysql_fetch_row($result);
    mysql_free_result($result);
    $globals->db->query("update auth_user_md5 set smtppass = '{$_REQUEST['smtppass1']}' where user_id = ".$_SESSION['uid']);
    $_SESSION['log']->log("passwd_ssl");

    $page->assign('message','Mot de passe enregistré');

} elseif (isset($_REQUEST['op']) && $_REQUEST['op'] == "Supprimer") {

    $globals->db->query("update auth_user_md5 set smtppass = '' where user_id = ".$_SESSION['uid']);
    $_SESSION['log']->log("passwd_del");
    $page->assign('message','Compte SMTP et NNTP supprimé');

}

$result = $globals->db->query("select smtppass from auth_user_md5 where user_id = ".$_SESSION['uid']);
list($smtppass_old) = mysql_fetch_row($result);
mysql_free_result($result);

$page->assign('actif', ($smtppass_old != ""));

$page->run(($smtppass_old != "") ? "actif" : "");
?>
