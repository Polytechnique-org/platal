<?php
require 'xnet.inc.php';

new_group_page('xnet/groupe/evenements.tpl');

$page->assign('logged', logged());
$page->assign('admin', may_update());

$moments = range(1, 4);
$page->assign('moments', $moments);

if ($eid = Env::get('eid')) {
	$res = $globals->xdb->query("SELECT asso_id FROM groupex.evenements WHERE eid = {?}", $eid);
	if ($res->fetchOneCell() != $globals->asso('id')) unset($eid);
}

if (may_update() && Post::get('intitule')) {
	$globals->xdb->execute("REPLACE INTO groupex.evenements VALUES (
		{?}, {?}, {?}, {?},
		{?}, {?},
		{?},
		{?},
		{?}, {?}, {?})",
		$eid, $globals->asso('id'), Session::get('uid'), Post::get('intitule'),
		Post::get('paiement')?Post::get('paiement'):NULL, Post::get('descriptif'),
		Post::get('deb_Year')."-".Post::get('deb_Month')."-".Post::get('deb_Day')." ".Post::get('deb_Hour').":".Post::get('deb_Minute').":00",
		Post::get('fin_Year')."-".Post::get('fin_Month')."-".Post::get('fin_Day')." ".Post::get('fin_Hour').":".Post::get('fin_Minute').":00",
		Post::get('membres_only'), Post::get('advertise'), Post::get('show_participants'));

	if (!$eid) {
		$res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
		$eid = $res->fetchOneCell();
	}

	$nb_moments = 0;
	foreach ($moments as $i) if (Post::get('titre'.$i)) {
		$nb_moments++;
		$globals->xdb->execute("
		REPLACE INTO groupex.evenements_items VALUES (
		{?}, {?},
		{?}, {?}, {?})",
		$eid, $i,
		Post::get('titre'.$i), Post::get('details'.$i), strtr(Post::get('montant'.$i), ',', '.'));
	} else {
		$globals->xdb->execute("DELETE FROM groupex.evenements_items WHERE eid = {?} AND item_id = {?}", $eid, $i);
	}
	
	// events with no sub-event
	if ($nb_moments == 0)
		$globals->xdb->execute("INSERT INTO groupex.evenements_items VALUES ({?}, {?}, '', '', 0)", $eid, 1);
}

if (may_update() && Env::has('sup') && $eid) {
	$globals->xdb->execute("DELETE FROM groupex.evenements WHERE eid = {?} AND asso_id = {?}", $eid, $globals->asso('id'));

	$globals->xdb->execute("DELETE FROM groupex.evenements_items WHERE eid = {?}", $eid);

	$globals->xdb->execute("DELETE FROM groupex.evenements_participants WHERE eid = {?}", $eid);
}

if (may_update() && (Env::has('add') || (Env::has('mod') && $eid))) {
	$page->assign('get_form', true);
	$res = $globals->xdb->iterator
		("SELECT id, text FROM {$globals->money->mpay_tprefix}paiements WHERE asso_id = {?}", $globals->asso('id'));
	$paiements = array();
	while ($a = $res->next()) $paiements[$a['id']] = $a['text'];
	$page->assign('paiements', $paiements);
}

if (may_update() && Env::has('mod') && $eid) {
	$res = $globals->xdb->query(
		"SELECT	eid, intitule, descriptif, debut, fin, membres_only, advertise, show_participants, paiement_id
		   FROM	groupex.evenements
		  WHERE eid = {?}", $eid);
	$evt = $res->fetchOneAssoc();
	$page->assign('evt', $evt);
	
	$res = $globals->xdb->iterator(
		"SELECT item_id, titre, details, montant
		   FROM groupex.evenements_items AS ei
	     INNER JOIN groupex.evenements AS e ON(e.eid = ei.eid)
		  WHERE e.eid = {?}
	       ORDER BY item_id", $eid);
	$items = array();
	while ($item = $res->next()) $items[$item['item_id']] = $item;
	$page->assign('items', $items);
} else {

	$evenements = $globals->xdb->iterator(
           "SELECT  e.eid, e.intitule, e.descriptif, e.debut, e.fin, e.show_participants, u.nom, u.prenom, u.promo, a.alias, MAX(ep.nb)>=1 AS inscrit
	      FROM  groupex.evenements AS e
	INNER JOIN  x4dat.auth_user_md5 AS u ON u.user_id = e.organisateur_uid
	 LEFT JOIN  x4dat.aliases AS a ON (a.type = 'a_vie' AND a.id = u.user_id)
	 LEFT JOIN  groupex.evenements_participants AS ep ON (ep.eid = e.eid AND ep.uid = {?})
	     WHERE  asso_id = {?}
	  GROUP BY  e.eid
	  ORDER BY  debut",Session::get('uid'),$globals->asso('id'));

	$page->assign('evenements', $evenements);

	$page->assign('nb_evt', $evenements->total());
}

$page->run();

?>
