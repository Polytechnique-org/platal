<?php
require("auto.prepend.inc.php");
new_skinned_page('routage-mail.tpl',AUTH_MDP);
require("email.classes.inc.php");

$redirect = new Redirect();

if (!$no_update_bd && isset($_REQUEST['emailop'])) {
    if ($_REQUEST['emailop']=="retirer" && isset($_REQUEST['num'])) {
        $page->assign('retour', $redirect->delete_email($_REQUEST['num']));
    }
    elseif ($_REQUEST['emailop']=="ajouter" && isset($_REQUEST['email'])) {
        $page->assign('retour', $redirect->add_email(trim($_REQUEST['email'])));
    }
    elseif (!isset($_REQUEST['emails_actifs']) || !is_array($_REQUEST['emails_actifs'])
        || count($_REQUEST['emails_actifs'])==0) {
        $page->assign('retour', ERROR_INACTIVE_REDIRECTION);
    }
    elseif (isset($_REQUEST['emails_actifs']) && is_array($_REQUEST['emails_actifs'])
        && isset($_REQUEST['emails_rewrite']) && is_array($_REQUEST['emails_rewrite'])) {
        $page->assign('retour',
        $redirect->modify_email($_REQUEST['emails_actifs'],$_REQUEST['emails_rewrite']));
    }
}
$sql = "SELECT domain FROM groupex.aliases WHERE id=12 AND email like'".$_SESSION['username']."'";
$res = $globals->db->query($sql);
list($grx) = mysql_fetch_row($res);
$page->assign('grx',$grx);
$page->assign('domaine',substr($grx,0,-3));
$sql = "SELECT alias FROM auth_user_md5 WHERE user_id=".$_SESSION["uid"];
$res = $globals->db->query($sql);
list($alias) = mysql_fetch_row($res);
$page->assign('alias',$alias);
foreach ($redirect->emails as $mail)
    $emails[] = $mail;
$page->assign('emails',$emails);
$page->assign('no_update_bd',$no_update_bd);

$page->run();
?>
