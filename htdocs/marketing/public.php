<?php
require("auto.prepend.inc.php");
new_skinned_page('marketing/public.php', AUTH_MDP);

if (! isset($_REQUEST["num"])) { exit; }

$mat = (((integer) $_REQUEST["num"]) + 100) / 2;

$res = $globals->db->query("SELECT nom,prenom,promo FROM identification WHERE matricule = '$mat'");
if (list($nom, $prenom, $promo) = mysql_fetch_row($res)) {
    $page->assign('prenom', $prenom);
    $page->assign('nom', $nom);
    $page->assign('promo', $promo);
}

if (isset($_REQUEST["valide"])) {
	$globals->db->query(
	  "INSERT INTO marketing
       SET expe = {$_SESSION['uid']}, dest = '$mat', email = '{$_REQUEST['mail']}', flags = '".(($_REQUEST["origine"]=="perso") ? "mail_perso" : "")."'"
	);
}

$page->run();
?>
