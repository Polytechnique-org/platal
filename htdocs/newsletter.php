<?php
require("auto.prepend.inc.php");
new_skinned_page('newsletter.tpl', AUTH_COOKIE);

if (isset($_REQUEST['last']))
    $res=$globals->db->query("SELECT UNIX_TIMESTAMP(date),titre,text FROM newsletter ORDER BY id DESC LIMIT 1");
elseif (isset($_REQUEST['nl_id'])) 
    $res=$globals->db->query("SELECT UNIX_TIMESTAMP(date),titre,text FROM newsletter WHERE id='{$_REQUEST['nl_id']}'");
else
    $res="";

if (($res)&&(list($nl_date, $nl_titre, $nl_text) = mysql_fetch_row($res))) {
    $page->assign('nl_date', $nl_date);
    $page->assign('nl_titre', $nl_titre);
    $page->assign('nl_text', $nl_text);

    if (isset($_REQUEST['send_mail'])) {
        require('mailer.inc.php');
        $mymail = new mailer("info_newsletter@polytechnique.org",
                $_SESSION['username']."@polytechnique.org",
                "[polytechnique.org] ".$nl_titre);
        $mymail->addHeader("From: \"Equipe polytechnique.org\" <info_newsletter@polytechnique.org>");
        $mymail->setBody("Suite à ta demande sur le site web, nous te réexpédions cette lettre d'informations archivée.\r\n\r\n".strip_tags($nl_text));
        $mymail->send();
        $page->assign('erreur', '<p class="erreur">Mail envoyé.</p>');
    }
}

$sql = "SELECT id,date,titre FROM newsletter ORDER BY date DESC";
$page->mysql_assign($sql, 'nl_list');

$page->run();
?>
