<?php
require("auto.prepend.inc.php");
new_admin_page('admin/newsletter_pattecassee.tpl');

function valide_email($str) {

   $em = trim(rtrim($str));
   list($ident, $dom) = explode("@", $em);
   if ($dom == "m4x.org" or $dom == "polytechnique.org") {
       list($ident1) = explode("_", $ident);
       list($ident) = explode("+", $ident1);
   }
   return $ident . "@" . $dom;

}

require("tpl.mailer.inc.php");

if (array_key_exists('email', $_GET) && array_key_exists('action', $_GET)) {
    $email = valide_email($_GET['email']);
    // vérifications d'usage
    $sel = $globals->db->query(
      "SELECT a.username
       FROM emails AS e
       INNER JOIN auth_user_md5 AS a ON e.uid = a.user_id
       WHERE e.email='$email'");
       
    $mailer = new TplMailer('templates/mails/pattecasser.nl.tpl');
    $mailer->assign('email', $email);
    
    if (list($dest) = mysql_fetch_row($sel)) {
        $mailer->assign('dest', $dest);
        $mailer->send();
        $page->assign('erreur', "<p class='erreur'>Mail envoyé ! :o)</p>");
    }
} else if (array_key_exists('email', $_POST)) {
    $email = valide_email($_POST['email']);
    $sel = $globals->db->query(
      "SELECT e.uid, e.panne, a.nom, a.prenom, a.promo, a.username
       FROM emails AS e
       INNER JOIN auth_user_md5 AS a ON e.uid = a.user_id
       WHERE e.email = '$email'");
    if (list($puid, $ppanne, $pnom, $pprenom, $ppromo, $pusername) = mysql_fetch_row($sel)) {
        // on écrit dans la base que l'adresse est cassée
        if ($ppanne == '0000-00-00')
            $globals->db->query("UPDATE emails SET panne='".date("Y-m-d")."' WHERE email =  '$email'");
        // on regarde s'il y a d'autres redirections actives
        $sel = $globals->db->query("SELECT * FROM emails WHERE uid = " . $puid . " AND FIND_IN_SET('active', flags) AND email != '$email'");
        $nb_emails = mysql_num_rows($sel);
        $page->assign('nb_emails', $nb_emails);
        $page->assign('username', $pusername);
        $page->assign('prenom', $pprenom);
        $page->assign('nom', $pnom);
        $page->assign('promo', $ppromo);
    } else
        $page->assign('no_more', 1);
    $page->assign('email', $email);
}

$page->run();
?>
