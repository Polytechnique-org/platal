<?php
require("auto.prepend.inc.php");
require("domaine.inc.php");

new_skinned_page('domaineperso.tpl', AUTH_MDP);

if (isset($_REQUEST['dnom']))
    $page->assign('result', add_domain($_REQUEST['dnom']));

$domaines = liste_domaines();
    
$page->assign('nb_dom',count($domaines));
$page->assign_by_ref('domaines',$domaines);

$page->run();
?>
