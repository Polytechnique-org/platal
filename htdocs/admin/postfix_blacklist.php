<?php
require("auto.prepend.inc.php");
new_admin_page('admin/postfix.common.tpl');

if(isset($_REQUEST['nomligne'])) {
    $nomligne = $_REQUEST['nomligne'];

    if (!empty($_REQUEST['del'])) {
        exec("/home/web/spam/effacerBlacklist $nomligne {$_SESSION['username']}");
        $page->assign('erreur', "Action: DEL($nomligne)");
    } else if (!empty($_REQUEST['add'])) {
        exec("/home/web/spam/ajouterBlacklist ".$nomligne);
        $page->assign('erreur', "Action: ADD($nomligne)");
    }
}

$blacklist = Array();
$fd = @fopen ("/etc/postfix/spamaccess", "r");
while ($fd && !feof ($fd)) {
    $buffer = fgets($fd, 4096);
    if ($buffer[0]!='#' && (strlen($buffer)>1)) { # FIXME $string[i] is deprecated
        $blacklist[] = $buffer;
    }
}
@fclose($fd);

$page->assign_by_ref('list',$blacklist);
$page->assign('title','Bannis de polytechnique.org');
$page->assign('expl','Ne mettre dans les cases que des emails (aucun espace ni commentaire supplémentaire)');
$page->run();
?>
