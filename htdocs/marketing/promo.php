<?php

require("auto.prepend.inc.php");
new_admin_page('marketing/promo.tpl');

$promo = (integer) (isset($_REQUEST["promo"]) ? $_REQUEST["promo"] : $_SESSION["promo"]);
$page->assign('promo', $promo);
$page->assign('promob10', $promo-10);
$page->assign('promob1', $promo-1);
$page->assign('promoa1', $promo+1);
$page->assign('promoa10', $promo+10);

$sql = "SELECT  i.nom, i.prenom, i.last_known_email, i.matricule, i.matricule_ax, MAX(e.date_envoi) AS dern_rel, c.email
          FROM  identification AS i
     LEFT JOIN  auth_user_md5  AS a ON (i.matricule = a.matricule)
     LEFT JOIN  envoidirect    AS e ON (i.matricule = e.matricule)
     LEFT JOIN  en_cours       AS c ON (i.matricule = c.matricule)
         WHERE  a.nom is NULL AND i.promo = $promo AND i.deces = 0
      GROUP BY  i.matricule
      ORDER BY  nom,prenom";

$page->mysql_assign($sql, 'nonins', 'nbnonins');

$page->run();

?>
