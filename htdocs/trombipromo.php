<?php
require("auto.prepend.inc.php");
new_skinned_page('trombipromo.tpl', AUTH_COOKIE, true);

$limit = 30;

$page->assign('limit', $limit);


if (!ereg("(19|20)[0-9]{2}",$_REQUEST['xpromo']) && ($_REQUEST['xpromo']!="all" && $_SESSION['perms']!="admin")) {
    $page->assign('erreur', "La promotion doit être saisie au format YYYY. Recommence.");
}

if(!isset($_REQUEST['xpromo'])) $page->run();

$offset = (empty($_REQUEST['offset']) ? 0 : $_REQUEST['offset']);

$where = ( $_REQUEST['xpromo']!="all" ? "WHERE promo='{$_REQUEST['xpromo']}'" : "" );

$res = $globals->db->query("SELECT  COUNT(*)
                              FROM  auth_user_md5 AS u
                        RIGHT JOIN  photo         AS p ON u.user_id=p.uid
                        $where");
list($pnb) = mysql_fetch_row($res);
$page->assign('pnb', $pnb);

$sql = "SELECT  promo,user_id,username,nom,prenom
          FROM  auth_user_md5 AS u
    RIGHT JOIN  photo         AS p ON u.user_id=p.uid
        $where
      ORDER BY  promo,nom,prenom LIMIT $offset,$limit";

$links = Array();
if($offset>0) { $links[] = Array($offset-$limit, 'précédent'); }
for($i = 0; $i < $pnb / $limit ; $i++) $links[] = Array($i*$limit, $i+1);
if($offset+$limit < $pnb) { $links[] = Array ($offset+$limit, 'suivant'); }
$page->assign('links',$links);

$page->mysql_assign($sql,'photos');
$page->run();

?>
