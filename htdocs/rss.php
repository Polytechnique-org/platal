<?php
require("nonhtml.inc.php");

$requete="SELECT e.id,e.titre,e.texte FROM evenements AS e WHERE FIND_IN_SET(flags, 'valide') AND peremption >= NOW()";

if (isset($_REQUEST["promo"])) {
    $requete.=" AND (e.promo_min = 0 || e.promo_min <= {$_REQUEST['promo']}) AND (e.promo_max = 0 || e.promo_max >= {$_REQUEST['promo']})";
    $page->assign('promo',$_REQUEST["promo"]);
}

$requete.=" ORDER BY (e.promo_min != 0 AND e.promo_max != 0) DESC,  e.peremption";
mysql_assign($page,$requete,'rss');

header("Content-Type: text/xml");
$page->display('rss.tpl');
?> 
