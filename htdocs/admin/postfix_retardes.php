<?php
require("auto.append.inc.php");
new_admin_page('admin/postfix.common.tpl');

if (isset($_REQUEST["del"]) && !empty($_REQUEST["del"])) {
    exec("/home/web/spam/effacerRetardes ".$_REQUEST["nomligne"]);
    $page->assign('erreur', "Action: DEL({$_REQUEST['nomligne']})");
}
	 
$retard = Array();
$fd = fopen ("/etc/postfix/spamdefer", "r");

while (!feof ($fd)) {
    $buffer = fgets($fd, 4096);
    if ($buffer[0]!='#' && (strlen($buffer)>1) { # FIXME $string[i] is deprecated
        $retard[] = $buffer;
    }
}
fclose($fd);

$page->assign_by_ref('list',$blacklist);
$page->assign('title','Mails retardés de polytechnique.org');
$page->assign('expl','Les envoyeurs ici présents verront leurs mails retardés toutes les heures jusqu\'au délai de 5j (bounce). Placer ici uniquement des emails (pas de commentaires).');
$page->display();
?>
