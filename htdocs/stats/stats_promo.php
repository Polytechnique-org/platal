<?php
require("auto.prepend.inc.php");
new_skinned_page("stats/stats_promo.tpl", AUTH_COOKIE);

$promo = isset($_REQUEST["promo"]) ? intval($_REQUEST["promo"]) : $_SESSION["promo"];
$page->assign('promo',$promo);
$page->display();
?>
