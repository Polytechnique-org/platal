<?php
require 'xnet.inc.php';

define('NB_PER_PAGE', 25);

require_once('xnet/evenements.php');

$evt = get_event_detail(Env::get('eid'), Env::get('item_id'));

// the event doesn't exist or doesn't belong to this assoif (!$evt)
if (!$evt)
	header("Location: evenements.php");

if ($evt['show_participants'])
	new_group_page('xnet/groupe/evt-admin.tpl');
else
	new_groupadmin_page('xnet/groupe/evt-admin.tpl');

$admin = may_update();

// select a member from his mail
if ($admin && Env::get('adm') && Env::get('mail')) {
	if (strpos(Env::get('mail'), '@') === false)
	$res = $globals->xdb->query(
		"SELECT m.uid
		   FROM groupex.membres AS m
	     INNER JOIN aliases AS a ON (a.id = m.uid)
		  WHERE a.alias = {?}",
		Env::get('mail'));
	else
	$res = $globals->xdb->query(
		"SELECT m.uid
		   FROM groupex.membres AS m
		  WHERE m.email = {?} AND m.asso_id = {?}",
		Env::get('mail'), $globals->asso('id'));
	$member = $res->fetchOneCell();
	if (!$member) $page->trig("Membre introuvable");
}

// change the price paid by a participant
if ($admin && Env::get('adm') == 'prix' && $member) {
	$globals->xdb->execute("UPDATE groupex.evenements_participants SET paid = IF(paid + {?} > 0, paid + {?}, 0) WHERE uid = {?} AND eid = {?}",
		strtr(Env::get('montant'), ',', '.'),
		strtr(Env::get('montant'), ',', '.'),
		$member, Env::get('eid'));
}

// change the number of personns coming with a participant
if ($admin && Env::get('adm') == 'nbs' && $member) {
	$res = $globals->xdb->query("SELECT paid FROM groupex.evenements_participants WHERE uid = {?} AND eid = {?}", $member, Env::get('eid'));
	$paid = $res->fetchOneCell();
	$participate = false;
	foreach ($evt['moments'] as $m) if (Env::has('nb'.$m['item_id'])) {
		$nb = Env::getInt('nb'.$m['item_id'], 0);
		if ($nb < 0) $nb = 0;
		if ($nb) {
			$participate = true;
			if (!$paid) $paid = 0;
			$globals->xdb->execute("REPLACE INTO groupex.evenements_participants VALUES ({?}, {?}, {?}, {?}, {?})",
			Env::get('eid'), $member, $m['item_id'], $nb, $paid);
		}
		else
		$globals->xdb->execute("DELETE FROM groupex.evenements_participants WHERE uid = {?} AND eid = {?} AND item_id = {?}", $member, Env::get('eid'), $m['item_id']);
	}
	if ($participate) 
		subscribe_lists_event(true, $member, $evt['participant_list'], $evt['absent_list']);
	else {
		$res = $globals->xdb->query("SELECT uid FROM groupex.evenements_participants WHERE uid = {?} AND eid = {?}", $member, Env::get('eid'));
		$u = $res->fetchOneCell();
		subscribe_lists_event($u, $member, $evt['participant_list'], $evt['absent_list']);
	}
	$evt = get_event_detail(Env::get('eid'), Env::get('item_id'));
}

$page->assign('admin', $admin);
$page->assign('evt', $evt);
$page->assign('url_page', Env::get('PHP_SELF')."?eid=".Env::get('eid').(Env::has('item_id')?("&item_id=".Env::getInt('item_id')):''));
$page->assign('tout', !Env::has('item_id'));
 
if (count($evt['moments']) > 1) $page->assign('moments', $evt['moments']);
$page->assign('money', $evt['money']);

$tri = (Env::get('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');
$whereitemid = Env::has('item_id')?('AND ep.item_id = '.Env::getInt('item_id', 1)):'';
$res = $globals->xdb->iterRow(
            'SELECT  UPPER(SUBSTRING(IF(u.nom IS NULL,m.nom,IF(u.nom_usage<>"", u.nom_usage, u.nom)), 1, 1)), COUNT(DISTINCT ep.uid)
               FROM  groupex.evenements_participants AS ep
	 INNER JOIN  groupex.evenements AS e ON (ep.eid = e.eid)
	  LEFT JOIN  groupex.membres AS m ON ( ep.uid = m.uid AND e.asso_id = m.asso_id)
          LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = ep.uid )
              WHERE  ep.eid = {?} '.$whereitemid.'
           GROUP BY  UPPER(SUBSTRING(IF(u.nom IS NULL,m.nom,u.nom), 1, 1))', Env::get('eid'));

$alphabet = array();
$nb_tot = 0;
while (list($char, $nb) = $res->next()) {
    $alphabet[ord($char)] = $char;
    $nb_tot += $nb;
    if (Env::has('initiale') && $char == strtoupper(Env::get('initiale'))) {
        $tot = $nb;
    }
}
ksort($alphabet);
$page->assign('alphabet', $alphabet);

$ofs   = Env::getInt('offset');
$tot   = Env::get('initiale') ? $tot : $nb_tot;
$nbp   = intval(($tot-1)/NB_PER_PAGE);
$links = array();
if ($ofs) {
    $links['précédent'] = $ofs-1;
}
for ($i = 0; $i <= $nbp; $i++) {
    $links[(string)($i+1)] = $i;
}
if ($ofs < $nbp) {
    $links['suivant'] = $ofs+1;
}
if (count($links)>1) {
    $page->assign('links', $links);
}

$ini = Env::has('initiale') ? 'AND IF(u.nom IS NULL,m.nom,IF(u.nom_usage<>"", u.nom_usage, u.nom)) LIKE "'.addslashes(Env::get('initiale')).'%"' : '';

$participants = get_event_participants(Env::get('eid'), Env::get('item_id'), $ini, $tri, "LIMIT ".($ofs*NB_PER_PAGE).", ".NB_PER_PAGE, $evt['money'] && $admin, $evt['paiement_id']);

if ($evt['paiement_id']) {
	$res = $globals->xdb->iterator(
        "SELECT IF(u.nom_usage<>'', u.nom_usage, u.nom) AS nom, u.prenom,
		u.promo, a.alias AS email, t.montant
	   FROM {$globals->money->mpay_tprefix}transactions AS t
	 INNER JOIN auth_user_md5 AS u ON(t.uid = u.user_id)
         INNER JOIN aliases AS a ON (a.id = t.uid AND a.type='a_vie' )
	  LEFT JOIN groupex.evenements_participants AS ep ON(ep.uid = t.uid AND ep.eid = {?})
	  WHERE t.ref = {?} AND ep.uid IS NULL",
	  $evt['eid'], $evt['paiement_id']);
	$page->assign('oublis', $res->total());
	$page->assign('oubliinscription', $res);
}

	
$page->assign('participants', $participants);

$page->run();

?>
