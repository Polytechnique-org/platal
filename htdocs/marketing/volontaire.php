<?php
require("auto.prepend.inc.php");
new_admin_page('marketing/volontaire.tpl');

$errors = Array();

// traitement des paramètres éventuels
if (!empty($_GET["del"])) {
    mysql_query("DELETE FROM marketing WHERE id ='{$_GET['del']}'");
    $errors[] = "Entrée effacée";
}
if (!empty($_GET["done"])) {
    mysql_query("UPDATE marketing SET flags = CONCAT(flags,',envoye') WHERE id ='{$_GET['done']}'");
    $errors[] = "Entrée mise à jour";
}

$page->assign_by_ref('errors', $errors);

$sql = "SELECT  m.id, m.expe, m.dest, m.email, 
		i.promo, i.nom, i.prenom, i.last_known_email, 
		sa.promo AS spromo, sa.nom AS snom, sa.prenom AS sprenom, sa.username AS susername,
                FIND_IN_SET('mail_perso', m.flags) AS mailperso
          FROM  marketing      AS m
    INNER JOIN  identification AS i  ON i.matricule = m.dest
    INNER JOIN  auth_user_md5  AS sa ON sa.user_id = m.expe
         WHERE  NOT FIND_IN_SET('envoye', m.flags)";

$page->mysql_assign($sql, 'neuves');


$sql = "SELECT  i.promo, i.nom, i.prenom,
                m.email, a.nom AS inscrit,
                sa.promo AS sprormo, sa.nom AS snom, sa.prenom AS sprenom
          FROM  marketing      AS m
    INNER JOIN  identification AS i  ON i.matricule = m.dest
    INNER JOIN  auth_user_md5  AS sa ON sa.user_id = m.expe
    LEFT  JOIN  auth_user_md5  AS a  ON a.matricule = m.dest
         WHERE  FIND_IN_SET('envoye', m.flags)";

$page->mysql_assign($sql, 'used', 'nbused');

$sql = "SELECT  COUNT(a.nom) AS j,
                COUNT(i.matricule) AS i,
                100 * COUNT(a.nom) / COUNT(i.matricule) as rate
          FROM  marketing      AS m
    INNER JOIN  identification AS i  ON i.matricule = m.dest
    INNER JOIN  auth_user_md5  AS sa ON sa.user_id = m.expe
    LEFT  JOIN  auth_user_md5  AS a  ON a.matricule = m.dest
         WHERE  FIND_IN_SET('envoye', m.flags)";
$res = $globals->db->query($sql);

$page->assign('rate', mysql_fetch_assoc($res));
mysql_free_result($res);

$page->run();
?>
