<?php
require 'xnet.inc.php';

define('NB_PER_PAGE', 25);

if ($globals->asso('pub') == 'public')
	new_group_page('xnet/groupe/annuaire.tpl');
else
	new_groupadmin_page('xnet/groupe/annuaire.tpl');

$page->assign('admin', may_update());

switch (Env::get('order')) {
    case 'promo'        : $group = 'promo'; $tri = 'promo_o DESC, nom, prenom'; break;
    case 'promo_inv'    : $group = 'promo'; $tri = 'promo_o, nom, prenom'; break;
    case 'alpha_inv'    : $group = 'initiale'; $tri = 'nom DESC, prenom DESC, promo'; break;
    default             : $group = 'initiale'; $tri = 'nom, prenom, promo';
}
if ($group == 'initiale')
    $res = $globals->xdb->iterRow(
                'SELECT  UPPER(SUBSTRING(IF(m.origine="X",IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom), 1, 1)) as letter, COUNT(*)
                   FROM  groupex.membres AS m
              LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                  WHERE  asso_id = {?}
               GROUP BY  letter
               ORDER BY  letter', $globals->asso('id'));
else
    $res = $globals->xdb->iterRow(
                'SELECT  IF(m.origine="X",u.promo,"extérieur") as promo, COUNT(*), IF(m.origine="X",u.promo,"") as promo_o
                   FROM  groupex.membres AS m
              LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
                  WHERE  asso_id = {?}
               GROUP BY  promo
               ORDER BY  promo_o DESC', $globals->asso('id'));

$alphabet = array();
$nb_tot = 0;
while (list($char, $nb) = $res->next()) {
    $alphabet[] = $char;
    $nb_tot += $nb;
    if (Env::has($group) && $char == strtoupper(Env::get($group))) {
        $tot = $nb;
    }
}
$page->assign('group', $group);
$page->assign('request_group', Env::get($group));
$page->assign('alphabet', $alphabet);
$page->assign('nb_tot',   $nb_tot);

$ofs   = Env::getInt('offset');
$tot   = Env::get($group) ? $tot : $nb_tot;
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

$ini = '';
if (Env::has('initiale'))
    $ini = 'AND IF(m.origine="X",IF(u.nom_usage<>"", u.nom_usage, u.nom),m.nom) LIKE "'.addslashes(Env::get('initiale')).'%"';
elseif (Env::has('promo'))
    $ini = 'AND IF(m.origine="X",u.promo,"extérieur") = "'.addslashes(Env::get('promo')).'"';
$ann = $globals->xdb->iterator(
          "SELECT  IF(m.origine='X',IF(u.nom_usage<>'', u.nom_usage, u.nom) ,m.nom) AS nom,
                   IF(m.origine='X',u.prenom,m.prenom) AS prenom,
                   IF(m.origine='X',u.promo,'extérieur') AS promo,
                   IF(m.origine='X',u.promo,'') AS promo_o,
                   IF(m.origine='X',a.alias,m.email) AS email,
                   IF(m.origine='X',FIND_IN_SET('femme', u.flags),0) AS femme,
                   m.perms='admin' AS admin,
                   m.origine='X' AS x,
                   m.uid
             FROM  groupex.membres AS m
        LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
        LEFT JOIN  aliases         AS a ON ( a.id = m.uid AND a.type='a_vie' )
            WHERE  m.asso_id = {?} $ini
         ORDER BY  $tri
            LIMIT  {?},{?}", $globals->asso('id'), $ofs*NB_PER_PAGE, NB_PER_PAGE);


$page->assign('ann', $ann);

$page->run();

?>
