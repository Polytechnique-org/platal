<?php

require_once 'xnet.inc.php';

new_group_page('xnet/groupe/evt-detail.tpl');

if (!Env::get("eid"))
    header("Location: evenements.php");

$may_participate = !$members_only || is_member() || may_update(); 
$page->assign('may_participate', $may_participate);

if (Env::get('ins')) {
	$total = 0;
	for ($i=1; Env::has('item_id'.$i); $i++)
	$total += (Env::get('item_'.Env::get('item_id'.$i)) > 0)?Env::get('item_'.Env::get('item_id'.$i)):0;

	$participate = $total > 0;
	$res  = $globals->xdb->query("SELECT paid FROM groupex.evenements_participants WHERE eid = {?} AND uid = {?}", Env::get("eid"), Session::get("uid"));
	$paid = $res->fetchOneCell();
	if (!$paid) $paid = 0;

	// prevent desinscription if there is a manual paiement
	if (!$participate && $paid) {
	    $page->trig("Impossible de te désinscrire complètement parce que tu as fait un paiement par chèque ou par liquide. Contacte un administrateur du groupe si tu es sûr de ne pas venir");
	    $participate = true;
	    $page->assign('no_ins', true);
	} else 
	for ($i=1; Env::has('item_id'.$i); $i++)
	{
	    $j    = Env::get('item_id'.$i);
	    $nb   = Env::get('item_'.$j);
	    if ($nb == '+') $nb = Env::get('itemnb_'.$j)+1;
	    if ($nb > 0) {
		$globals->xdb->execute(
		    "REPLACE INTO  groupex.evenements_participants
			   VALUES  ({?}, {?}, {?}, {?}, {?})",
		    Env::get("eid"), Session::get("uid"), $j, $nb, $paid);
	    } else {
		$globals->xdb->execute(
		    "DELETE FROM  groupex.evenements_participants
			   WHERE  eid = {?} AND uid = {?} AND item_id = {?}",
		    Env::get("eid"), Session::get("uid"), $j);		
	    }
	}
}

require_once('xnet/evenements.php');
$evt = get_event_detail(Env::get('eid'));
if (Env::has('ins')) {
    subscribe_lists_event($participate, Session::get("uid"), $evt['participant_list'], $evt['absent_list']);
    header("Location: evenements.php?backfrom=".Env::get('eid'));
}

$page->assign('participate', $participate);
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
    $page->assign('paid_manual', $paid);
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
