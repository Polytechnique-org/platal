<?php
require 'xnet.inc.php';

new_group_page('xnet/groupe/annuaire.tpl');
$page->setType($globals->asso('cat'));
$page->assign('asso', $globals->asso());
$page->assign('admin', may_update());
$page->useMenu();

$tri = (Env::get('order') == 'alpha' ? 'promo, nom, prenom' : 'nom, prenom, promo');
$res = $globals->xdb->iterRow(
            'SELECT  SUBSTRING(IF(m.origine="X",u.nom,m.nom), 1, 1), COUNT(IF(m.origine="X",u.nom,m.nom))
               FROM  groupex.membres AS m
          LEFT JOIN  auth_user_md5   AS u ON ( u.user_id = m.uid )
              WHERE  asso_id = {?}
           GROUP BY  SUBSTRING(IF(m.origine="X",u.nom,m.nom), 1, 1)', $globals->asso('id'));
$alphabet = array();
$nb_tot = 0;
while (list($char, $nb) = $res->next()) {
    $alphabet[ord($char)] = $char;
    $nb_tot += $nb;
}
$page->assign('alphabet', $alphabet);
$page->assign('nb_tot',   $nb_tot);

$ini = Env::has('initiale') ? 'AND IF(m.origine="X",u.nom,m.nom) LIKE "'.addslashes(Env::get('initiale')).'%"' : '';

$ann = $globals->xdb->iterator(
          "SELECT  IF(m.origine='X',IF(u.nom_usage, u.nom_usage, u.nom) ,m.nom) AS nom,
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
         ORDER BY  $tri", $globals->asso('id'));
$page->assign('ann', $ann);

$page->run();

?>
