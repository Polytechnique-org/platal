<?php

require 'xnet.inc.php';

new_page('xnet/groupe/asso.tpl', AUTH_PUBLIC);
$page->useMenu();
$page->setType($globals->asso('cat'));

$page->assign('asso', $globals->asso());
if (!$globals->asso('id')) {
    header("Location: ../");
}
// Sélection de toutes les associations ayant la même catégorie et le même domaine que l'activité sélectionnée
$gps = $globals->xdb->iterator(
        "SELECT  diminutif, nom
           FROM  groupex.asso
          WHERE  cat = {?} AND  dom = {?}
       ORDER BY  nom", $globals->asso('cat'), $globals->asso('dom'));
$page->assign('gps', $gps);

$page->run();

?>
