<?php
require("auto.prepend.inc.php");
require("domaine.inc.php");

new_skinned_page('groupex/index.tpl',AUTH_COOKIE);

$domaines = liste_domaines();
    
$page->assign('nb_dom',count($domaines));
$page->assign_by_ref('domaines',$domaines);

$page->display();
?>
