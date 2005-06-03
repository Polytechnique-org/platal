<?php
require 'xnet.inc.php';

define('NB_PER_PAGE', 25);

$res = $globals->xdb->query(
	"SELECT	SUM(nb) AS nb_tot, e.intitule, ei.titre, e.show_participants
	   FROM	groupex.evenements AS e
     INNER JOIN	groupex.evenements_items AS ei ON (e.eid = ei.eid)
      LEFT JOIN	groupex.evenements_participants AS ep ON(e.eid = ep.eid AND ei.item_id = ep.item_id)
          WHERE	e.eid = {?} AND ei.item_id = {?} AND asso_id = {?}
       GROUP BY e.eid",
       Env::get('eid'), Env::getInt('item_id', 1), $globals->asso('id'));

$evt = $res->fetchOneAssoc();
if (!$evt['intitule'])
	header("Location: evenements.php");

if ($evt['show_participants'])
	new_group_page('xnet/groupe/evt-admin.tpl');
else
	new_groupadmin_page('xnet/groupe/evt-admin.tpl');

$page->assign('evt', $evt);
$page->assign('url_page', Env::get('PHP_SELF')."?eid=".Env::get('eid')."&item_id=".Env::getInt('item_id', 1));
 
$res = $globals->xdb->iterator(
	"SELECT eid, item_id, titre
	   FROM groupex.evenements_items
	  WHERE eid = {?}",
	Env::get('eid'));
if ($res->total() > 1) $page->assign('moments', $res);

$tri = (Env::get('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');
$res = $globals->xdb->iterRow(
            'SELECT  UPPER(SUBSTRING(IF(m.origine="X",IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom), 1, 1)), COUNT(IF(m.origine="X",u.nom,m.nom))
               FROM  groupex.evenements_participants AS ep
	 INNER JOIN  groupex.evenements AS e ON (ep.eid = e.eid)
	 INNER JOIN  groupex.membres AS m ON ( ep.uid = m.uid AND e.asso_id = m.asso_id)
          LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
              WHERE  e.asso_id = {?} AND ep.eid = {?} AND ep.item_id = {?}
           GROUP BY  UPPER(SUBSTRING(IF(m.origine="X",u.nom,m.nom), 1, 1))', $globals->asso('id'), Env::get('eid'), Env::getInt('moment', 1));

$alphabet = array();
$nb_tot = 0;
while (list($char, $nb) = $res->next()) {
    $alphabet[ord($char)] = $char;
    $nb_tot += $nb;
    if (Env::has('initiale') && $char == strtoupper(Env::get('initiale'))) {
        $tot = $nb;
    }
}
$page->assign('alphabet', $alphabet);

$ofs   = Env::getInt('offset');
$tot   = Env::get('initiale') ? $tot-1 : $nb_tot-1;
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

$ini = Env::has('initiale') ? 'AND IF(m.origine="X",IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom) LIKE "'.addslashes(Env::get('initiale')).'%"' : '';
$ann = $globals->xdb->iterator(
          "SELECT  IF(m.origine='X',IF(u.nom_usage<>'', u.nom_usage, u.nom) ,m.nom) AS nom,
                   IF(m.origine='X',u.prenom,m.prenom) AS prenom,
                   IF(m.origine='X',u.promo,'extérieur') AS promo,
                   IF(m.origine='X',a.alias,m.email) AS email,
                   IF(m.origine='X',FIND_IN_SET('femme', u.flags),0) AS femme,
                   m.perms='admin' AS admin,
                   m.origine='X' AS x,
		   ep.nb
               FROM  groupex.evenements_participants AS ep
	 INNER JOIN  groupex.evenements AS e ON (ep.eid = e.eid)
	 INNER JOIN  groupex.membres AS m ON ( ep.uid = m.uid AND e.asso_id = m.asso_id)
          LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
          LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type='a_vie' )
              WHERE  e.asso_id = {?} AND ep.eid = {?} AND ep.item_id = {?} $ini
	   ORDER BY  $tri
	      LIMIT {?}, {?}",
	   $globals->asso('id'), Env::get('eid'), Env::getInt('item_id', 1),
           $ofs*NB_PER_PAGE, NB_PER_PAGE);


$page->assign('ann', $ann);

$page->run();

?>
