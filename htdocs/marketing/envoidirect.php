<?php
require("auto.prepend.inc.php");
new_admin_page('marketing/envoidirect.tpl');

// effacement des inscrits il y a plus de 8 jours
$globals->db->query("DELETE FROM envoidirect WHERE DATE_ADD(date_succes, INTERVAL 8 DAY) < CURRENT_DATE AND date_succes <> '0000-00-00'");
$sql = "SELECT  a.date_ins,e.date_envoi,e.promo,e.nom,e.prenom,e.email,b.nom as sender
          FROM  envoidirect   AS e
    INNER JOIN  auth_user_md5 AS a ON e.matricule = a.matricule
    LEFT  JOIN  auth_user_md5 AS b ON e.sender    = b.user_id
      ORDER BY  e.date_envoi DESC";

$page->mysql_assign($sql, 'recents', 'nbrecents');

$sql = "SELECT  DISTINCT e.date_envoi, e.promo, e.nom, e.prenom, e.email, b.nom as sender
          FROM  envoidirect   AS e
    LEFT  JOIN  auth_user_md5 AS a ON e.matricule = a.matricule
    INNER JOIN  auth_user_md5 AS b ON e.sender    = b.user_id
         WHERE  a.nom is null
      ORDER BY  e.date_envoi DESC";
$page->mysql_assign($sql, 'notsub', 'nbnotsub');

$page->run();

?>
