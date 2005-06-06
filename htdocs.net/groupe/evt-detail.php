<?php

require 'xnet.inc.php';

new_group_page('xnet/groupe/evt-detail.tpl');

if (!Env::get("eid"))
    header("Location: evenements.php");

$may_participate = !$members_only || is_member() || may_update(); 
$page->assign('may_participate', $may_participate);

for ($i=1; Env::has('item_id'.$i); $i++) {
	$res = $globals->xdb->query("SELECT paid FROM groupex.evenements_participants WHERE eid = {?} AND uid = {?}", Env::get("eid"), Session::get("uid"));
	$paid = $res->fetchOneCell();
	if (!$paid) $paid = 0;
	$j = Env::get('item_id'.$i);
	$nb = Env::get('item_'.$j);
	if ($nb == '+') $nb = Env::get('itemnb_'.$j)+1;
	if ($nb > 0)
		$globals->xdb->execute(
	"REPLACE INTO groupex.evenements_participants
	       VALUES ({?}, {?}, {?}, {?}, {?})",
	       Env::get("eid"), Session::get("uid"), $j, $nb, $paid);
	else
		$globals->xdb->execute(
	"DELETE FROM groupex.evenements_participants
		WHERE eid = {?} AND uid = {?} AND item_id = {?}",
	       Env::get("eid"), Session::get("uid"), $j);		
}

// return to the main page after modifying
if (Env::has("ins"))
	header("Location: evenements.php");

$res = $globals->xdb->query(
        "SELECT  e.eid, a.nom, a.prenom, a.promo, intitule, descriptif, debut AS deb,
	         fin, membres_only, paiement_id
	   FROM  groupex.evenements AS e
	  INNER  JOIN x4dat.auth_user_md5 AS a ON a.user_id = e.organisateur_uid
	  WHERE  e.eid = {?}", Env::get("eid"));

$evt = $res->fetchOneAssoc();
$page->assign('evt', $evt);

$moments = $globals->xdb->iterator(
        "SELECT  titre, i.item_id, details, montant, nb
	   FROM  groupex.evenements_items AS i
	   LEFT  JOIN groupex.evenements_participants AS p
	   	 ON(i.eid = p.eid AND i.item_id = p.item_id AND uid = {?})
	  WHERE  i.eid = {?}",
	  Session::get('uid'), Env::get('eid'));

$page->assign('moments', $moments);

if (!$paid) {
	$res = $globals->xdb->query("SELECT paid FROM groupex.evenements_participants WHERE eid = {?} AND uid = {?}", Env::get("eid"), Session::get("uid"));
	$paid = $res->fetchOneCell();
}
if ($evt['paiement_id']) {
	$res = $globals->xdb->query(
		"SELECT montant
		   FROM {$globals->money->mpay_tprefix}transactions AS t
		  WHERE ref = {?} AND uid = {?}",
		  	$evt['paiement_id'], Session::getInt('uid', -1));
	$montants = $res->fetchColumn();
	foreach ($montants as $m) {
		$p = strtr(substr($m, 0, strpos($m, "EUR")), ",", ".");
		$paid += trim($p);
	}
}
$page->assign('paid', $paid);

$page->run();

?>
