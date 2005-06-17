<?php
require 'xnet.inc.php';

new_group_page('xnet/groupe/evenements.tpl');

$page->assign('logged', logged());
$page->assign('admin', may_update());

$moments = range(1, 4);
$page->assign('moments', $moments);

if ($eid = Env::get('eid')) {
	$res = $globals->xdb->query("SELECT asso_id, short_name FROM groupex.evenements WHERE eid = {?}", $eid);
	$infos = $res->fetchOneAssoc();
	if ($infos['asso_id'] != $globals->asso('id')) {
		unset($eid);
		unset($infos);
	}
}

if (may_update() && Post::get('intitule')) {
	$short_name = Env::get('short_name');
	//Quelques vérifications sur l'alias (caractères spéciaux)
	if ($short_name && !preg_match( "/^[a-zA-Z0-9\-.]{3,20}$/", $short_name)) {
		$page->trig("Le raccourci demandé n'est pas valide.
			    Vérifie qu'il comporte entre 3 et 20 caractères
			    et qu'il ne contient que des lettres non accentuées,
			    des chiffres ou les caractères - et .");
		$short_name = $infos['short_name'];
	}
	//vérifier que l'alias n'est pas déja pris
	if ($short_name && $short_name != $infos['short_name']) {
		$res = $globals->xdb->query('SELECT COUNT(*) FROM virtual WHERE alias LIKE {?}', $short_name."-%");
		if ($res->fetchOneCell() > 0) {
			$page->trig("Le raccourci demandé est déjà utilisé. Choisis en un autre.");
			$short_name = $infos['short_name'];
		}
	}
	if ($short_name && $infos['short_name'] && $short_name != $infos['short_name']) {
		$globals->xdb->execute("UPDATE virtual SET alias = REPLACE(alias, {?}, {?}) WHERE type = 'evt' AND alias LIKE {?}",
			$infos['short_name'], $short_name, $infos['short_name']."-%");
	} elseif ($short_name && !$infos['short_name']) {
		$globals->xdb->execute("INSERT INTO virtual SET type = 'evt', alias = {?}", $short_name."-participants@".$globals->mail->domain);
		$res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
		$globals->xdb->execute("INSERT INTO virtual_redirect (
			SELECT {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
			  FROM groupex.evenements_participants AS ep
		     LEFT JOIN groupex.membres AS m ON (ep.uid = m.uid)
		     LEFT JOIN auth_user_md5 AS u ON (u.user_id = ep.uid)
		     LEFT JOIN aliases AS a ON (a.id = ep.uid AND a.type = 'a_vie')
		         WHERE ep.eid = {?}
		      GROUP BY ep.uid)",
			 $res->fetchOneCell(), "@".$globals->mail->domain, $eid);

		$globals->xdb->execute("INSERT INTO virtual SET type = 'evt', alias = {?}", $short_name."-absents@".$globals->mail->domain);
		$res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
		$globals->xdb->execute("INSERT INTO virtual_redirect (
			SELECT {?} AS vid, IF(u.nom IS NULL, m.email, CONCAT(a.alias, {?})) AS redirect
		          FROM groupex.membres AS m
		     LEFT JOIN groupex.evenements_participants AS ep ON (ep.uid = m.uid)
		     LEFT JOIN auth_user_md5 AS u ON (u.user_id = m.uid)
		     LEFT JOIN aliases AS a ON (a.id = m.uid AND a.type = 'a_vie')
		         WHERE m.asso_id = {?} AND ep.uid IS NULL
		      GROUP BY m.uid)",
			 $res->fetchOneCell(), "@".$globals->mail->domain, $globals->asso('id'));
	} elseif (!$short_name && $infos['short_name']) {
		$globals->xdb->execute("DELETE virtual, virtual_redirect FROM virtual LEFT JOIN virtual_redirect USING(vid) WHERE virtual.alias LIKE {?}",
			$infos['short_name']."-%");
	}

	$globals->xdb->execute("REPLACE INTO groupex.evenements VALUES (
		{?}, {?}, {?}, {?},
		{?}, {?},
		{?},
		{?},
		{?}, {?}, {?}, {?})",
		$eid, $globals->asso('id'), Session::get('uid'), Post::get('intitule'),
		(Post::get('paiement')>0)?Post::get('paiement'):NULL, Post::get('descriptif'),
		Post::get('deb_Year')."-".Post::get('deb_Month')."-".Post::get('deb_Day')." ".Post::get('deb_Hour').":".Post::get('deb_Minute').":00",
		Post::get('fin_Year')."-".Post::get('fin_Month')."-".Post::get('fin_Day')." ".Post::get('fin_Hour').":".Post::get('fin_Minute').":00",
		Post::get('membres_only'), Post::get('advertise'), Post::get('show_participants'), $short_name);

	if (!$eid) {
		$res = $globals->xdb->query("SELECT LAST_INSERT_ID()");
		$eid = $res->fetchOneCell();
	}
	$nb_moments = 0;
	$money_defaut = 0;
	foreach ($moments as $i) if (Post::get('titre'.$i)) {
		$nb_moments++;
		if (!($money_defaut > 0)) $money_defaut = strtr(Post::get('montant'.$i), ',', '.');
		$globals->xdb->execute("
		REPLACE INTO groupex.evenements_items VALUES (
		{?}, {?},
		{?}, {?}, {?})",
		$eid, $i,
		Post::get('titre'.$i), Post::get('details'.$i), strtr(Post::get('montant'.$i), ',', '.'));
	} else {
		$globals->xdb->execute("DELETE FROM groupex.evenements_items WHERE eid = {?} AND item_id = {?}", $eid, $i);
	}

	// request for a new payment
	if (Post::get('paiement') == -1 && $money_defaut >= 0) {
		require_once ('validations.inc.php');
		$p = new PayReq(Session::get('uid'), Post::get('intitule')." - ".$globals->asso('nom'), Post::get('site'), $money_defaut, Post::get('confirmation'),0, 999, $globals->asso('id'), $eid);
		$p->submit();
	}
	
	// events with no sub-event
	if ($nb_moments == 0)
		$globals->xdb->execute("INSERT INTO groupex.evenements_items VALUES ({?}, {?}, '', '', 0)", $eid, 1);
}

if (may_update() && Env::has('sup') && $eid) {
	// deletes the event
	$globals->xdb->execute("DELETE FROM groupex.evenements WHERE eid = {?} AND asso_id = {?}", $eid, $globals->asso('id'));
	// deletes the event items
	$globals->xdb->execute("DELETE FROM groupex.evenements_items WHERE eid = {?}", $eid);
	// deletes the event participants
	$globals->xdb->execute("DELETE FROM groupex.evenements_participants WHERE eid = {?}", $eid);
	// deletes the event mailing aliases
	if ($infos['short_name'])
		$globals->xdb->execute("DELETE FROM virtual WHERE type = 'evt' AND alias LIKE {?}", $infos['short_name']."-%");
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
		"SELECT	eid, intitule, descriptif, debut, fin, membres_only, advertise, show_participants, paiement_id, short_name
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
	"SELECT  e.eid, e.intitule, e.descriptif, e.debut, e.fin, e.show_participants, u.nom, u.prenom, u.promo, a.alias, MAX(ep.nb)>=1 AS inscrit,
		 e.short_name
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
