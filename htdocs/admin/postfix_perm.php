<?php
require("auto.prepend.inc.php");
new_admin_page('admin/postfix.common.tpl');

if(isset($_REQUEST['nomligne'])) {
    $nomligne = $_REQUEST['nomligne'];
    
    if (!empty($_REQUEST['del'])) {
        exec("/home/web/spam/effacerPermissions $nomligne {$_SESSION['username']}");
        $page->assign('erreur', "Action: DEL($nomligne)");
    } else if (!empty($_REQUEST['add'])) {
        exec("/home/web/spam/ajouterPermissions '".$nomligne."'");
        $page->assign('erreur', "Action: ADD($nomligne)");
    }
}

$permis = Array();
$fd = fopen ("/etc/postfix/spampermis", "r");
while ($fd && !feof ($fd)) {
    $buffer = fgets($fd, 4096);
    if ($buffer[0]!='#' && (strlen($buffer)>1)) { # FIXME $string[i] is deprecated
        $permis[] = $buffer;
    }
}
fclose($fd);

$page->assign_by_ref('list',$blacklist);
$page->assign('title','Permissions de polytechnique.org');
$page->assign('expl','On peut placer dans les cases les emails de personnes pouvant diffuser sans restriction.');
$page->run();
?>
