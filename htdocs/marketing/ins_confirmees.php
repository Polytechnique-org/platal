<?php
require("auto.prepend.inc.php");
new_admin_page('marketing/ins_confirmees.tpl',true);

if (!isset($_GET["sort"]) || $_GET["sort"] != "promo") $_GET["sort"] = "date_ins";

$sql = "SELECT a.username,a.date_ins,a.promo,a.nom,a.prenom
        FROM       ins_confirmees AS i
        INNER JOIN auth_user_md5  AS a ON i.id=a.user_id
        ORDER BY a.{$_GET['sort']} DESC";
$page->mysql_assign($sql, 'ins', 'nb_ins');

$page->run();
?>
