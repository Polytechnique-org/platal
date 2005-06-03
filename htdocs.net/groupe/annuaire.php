<?php
require 'xnet.inc.php';

define('NB_PER_PAGE', 25);

if ($globals->asso('pub') == 'public')
	new_group_page('xnet/groupe/annuaire.tpl');
else
	new_groupadmin_page('xnet/groupe/annuaire.tpl');

$page->assign('admin', may_update());

$tri = (Env::get('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');
$res = $globals->xdb->iterRow(
            'SELECT  UPPER(SUBSTRING(IF(m.origine="X",IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom), 1, 1)), COUNT(IF(m.origine="X",u.nom,m.nom))
               FROM  groupex.membres AS m
          LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
              WHERE  asso_id = {?}
           GROUP BY  UPPER(SUBSTRING(IF(m.origine="X",u.nom,m.nom), 1, 1))', $globals->asso('id'));
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
$page->assign('nb_tot',   $nb_tot);

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
                   m.origine='X' AS x
             FROM  groupex.membres AS m
        LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
        LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type='a_vie' )
            WHERE  m.asso_id = {?} $ini
         ORDER BY  $tri
            LIMIT  {?},{?}", $globals->asso('id'), $ofs*NB_PER_PAGE, NB_PER_PAGE);


$page->assign('ann', $ann);

$page->run();

?>
