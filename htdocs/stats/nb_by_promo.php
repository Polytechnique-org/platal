<?php
require("auto.prepend.inc.php");
new_skinned_page('stats/nb_by_promo.tpl', AUTH_COOKIE);

$result = $globals->db->query("SELECT promo,COUNT(*) FROM auth_user_md5 WHERE promo > 1900 GROUP BY promo ORDER BY promo");
$max=0; $min=3000;
while(list($promo,$nb)=mysql_fetch_row($result)) {
    $promo=intval($promo);
    if(!isset($nbpromo[$promo/10]))
        $nbpromo[$promo/10] = Array('','','','','','','','','',''); // tableau de 10 cases vides
    $nbpromo[$promo/10][$promo%10]=Array('promo' => $promo, 'nb' => $nb);
}

$page->assign_by_ref('nbs', $nbpromo);
$page->assign('min', $min-$min % 10);
$page->assign('max', $max+10-$max%10);

$page->run();
?>
