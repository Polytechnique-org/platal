<?php
require("auto.prepend.inc.php");
new_admin_page('admin/deces_promo.tpl');

$promo = ( isset($_REQUEST["promo"]) ? $_REQUEST["promo"] : 0 );
if(isset($_REQUEST['sub10']))   $promo -= 10;
if(isset($_REQUEST['sub01']))   $promo --;
if(isset($_REQUEST['add01']))   $promo ++;
if(isset($_REQUEST['add10']))   $promo += 10;

$page->assign('promo',$promo);

if (isset($_REQUEST["valider"]) && $_REQUEST["valider"] == "Valider") {
    $res = mysql_query("SELECT matricule FROM identification WHERE promo = $promo");
    while (list($mat) = mysql_fetch_row($res)) {
        mysql_query("UPDATE identification SET deces='".$_REQUEST[$mat]."' WHERE matricule = '".$mat."'");
    }
}

$sql = "SELECT matricule, nom, prenom, deces FROM identification WHERE promo = $promo ORDER BY nom,prenom";
$page->mysql_assign($sql, 'decedes');

$page->display();
?>
