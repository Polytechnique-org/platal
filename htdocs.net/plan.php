<?php
    require 'xnet.inc.php';

    new_skinned_page('xnet/plan.tpl', AUTH_PUBLIC);
    $page->setType('plan');

    $res = $globals->xdb->iterator(
            'SELECT  dom.id, dom.nom as domnom, asso.diminutif, asso.nom
               FROM  groupex.dom
         INNER JOIN  groupex.asso ON dom.id = asso.dom
              WHERE  FIND_IN_SET("GroupesX", dom.cat) AND FIND_IN_SET("GroupesX", asso.cat)
           ORDER BY  dom.nom, asso.nom');
    $groupesx = array();
    while ($tmp = $res->next()) { $groupesx[$tmp['id']][] = $tmp; }
    $page->assign('groupesx', $groupesx);

    $res = $globals->xdb->iterator(
            'SELECT  dom.id, dom.nom as domnom, asso.diminutif, asso.nom
               FROM  groupex.dom
         INNER JOIN  groupex.asso ON dom.id = asso.dom
              WHERE  FIND_IN_SET("Binets", dom.cat) AND FIND_IN_SET("Binets", asso.cat)
           ORDER BY  dom.nom, asso.nom');
    $binets = array();
    while ($tmp = $res->next()) { $binets[$tmp['id']][] = $tmp; }
    $page->assign('binets', $binets);

    $res = $globals->xdb->iterator(
            'SELECT  asso.diminutif, asso.nom
               FROM  groupex.asso
              WHERE  cat LIKE "%Promotions%"
           ORDER BY  diminutif');
    $page->assign('promos', $res);

    $res = $globals->xdb->iterator(
            'SELECT  asso.diminutif, asso.nom
               FROM  groupex.asso
              WHERE  FIND_IN_SET("Institutions", cat)
           ORDER BY  diminutif');
    $page->assign('inst', $res);

    $page->run();
?>
