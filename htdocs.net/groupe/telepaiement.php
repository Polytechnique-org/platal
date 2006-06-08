<?php

require 'xnet.inc.php';

new_group_page('xnet/groupe/telepaiement.tpl');

$res = $globals->xdb->query("SELECT id, text FROM {$globals->money->mpay_tprefix}paiements
    WHERE asso_id = {?} AND NOT FIND_IN_SET(flags, 'old')
    ORDER BY id DESC",
    $globals->asso('id'));
$tit = $res->fetchAllAssoc();
$page->assign('titres', $tit);

$order = Env::get('order', 'timestamp');
$orders = array('timestamp', 'nom', 'promo', 'montant');
if (!in_array($order, $orders)) $order = 'timestamp';
$inv_order = Env::get('order_inv', 0);
$page->assign('order', $order);
$page->assign('order_inv', !$inv_order);
if ($order == 'timestamp') $inv_order = !$inv_order;

if ($inv_order) $inv_order = ' DESC'; else $inv_order = '';
if ($order == 'montant') $order = 'LENGTH(montant) '.$inv_order.', montant';

$orderby = 'ORDER BY '.$order.$inv_order;
if ($order != 'nom') { $orderby .= ', nom'; $inv_order = ''; }
$orderby .= ', prenom'.$inv_order;
if ($order != 'timestamp') $orderby .= ', timestamp DESC';

if (may_update()) {
    $trans = array();
    foreach($tit as $foo) {
        $pid = $foo['id'];
        $res = $globals->xdb->query(
                "SELECT  IF(u.nom_usage<>'', u.nom_usage, u.nom) as nom, u.prenom, u.promo, a.alias, timestamp AS `date`, montant 
                   FROM  {$globals->money->mpay_tprefix}transactions AS t
             INNER JOIN  auth_user_md5                               AS u ON ( t.uid = u.user_id )
             INNER JOIN  aliases                                     AS a ON ( t.uid = a.id AND a.type='a_vie' )
                  WHERE  ref = {?} ".$orderby, $pid);
        $trans[$pid] = $res->fetchAllAssoc();
        $sum = 0;
        foreach ($trans[$pid] as $i => $t)
            $sum += strtr(substr($t['montant'], 0, strpos($t['montant'], "EUR")), ",", ".");
        $trans[$pid][] = array("nom" => "somme totale", "montant" => strtr($sum, ".", ",")." EUR");
    }
    $page->assign('trans', $trans);
}

$page->run();
?>
