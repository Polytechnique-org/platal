<?php

require 'xnet.inc.php';

new_group_page('xnet/groupe/telepaiement.tpl');

$res = $globals->xdb->query("SELECT id, text FROM {$globals->money->mpay_tprefix}paiements WHERE asso_id = {?}", $globals->asso('id'));
$tit = $res->fetchAllAssoc();
$page->assign('titres', $tit);

if (may_update()) {
    $trans = array();
    foreach($tit as $foo) {
        $pid = $foo['id'];
        $res = $globals->xdb->query(
                "SELECT  IF(u.nom_usage<>'', u.nom_usage, u.nom) as nom, u.prenom, u.promo, a.alias, timestamp, montant
                   FROM  {$globals->money->mpay_tprefix}transactions AS t
             INNER JOIN  auth_user_md5                               AS u ON ( t.uid = u.user_id )
             INNER JOIN  aliases                                     AS a ON ( t.uid = a.id AND a.type='a_vie' )
                  WHERE  ref = {?}
               ORDER BY  timestamp DESC", $id);
        $trans[$pid] = $res->fetchAllAssoc();
    }
    $page->assign('trans', $trans);
}

$page->run();
?>
