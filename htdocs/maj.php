<?php
require("auto.prepend.inc.php");

$erreur = Array();

if (isset($_REQUEST['n'])) {
    $sql = "SELECT * FROM envoidirect WHERE uid='".$_REQUEST["n"]."'";
    $result = $globals->db->query($sql);
    $ligne = mysql_fetch_array($result); 
}

if (isset($ligne) && $ligne) {
    // il faut remettre le matricule dans son format de saisie
    $_REQUEST['matricule'] = strlen($ligne['matricule']>6) ?
	substr($ligne['matricule'],2) : $ligne['matricule'];
    $_REQUEST['promo'] = $ligne['promo'];
    $_REQUEST['nom'] = $ligne['nom'];
    $_REQUEST['prenom'] = $ligne['prenom'];
    $_REQUEST['email'] = $ligne['email'];

    // on veut basculer sur inscrire.php

    require("identification.inc.php");

    new_skinned_page('inscrire.form_data.tpl', AUTH_PUBLIC, true);
    $page->assign('gotourl', $gotourl);
    $gotourl = "inscrire.php";
    $page->run();
}

new_skinned_page('maj.tpl', AUTH_PUBLIC);
$page->run();
?>
