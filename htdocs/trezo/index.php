<?php
require("auto.prepend.inc.php");
require("trezo.inc.php");
new_admin_page('trezo/index.tpl');

//Table operations :
//+--------+---------------+------+-----+------------+----------------+
//| Field  | Type          | Null | Key | Default    | Extra          |
//+--------+---------------+------+-----+------------+----------------+
//| id     | int(11)       |      | PRI | NULL       | auto_increment |
//| date   | date          |      |     | 0000-00-00 |                |
//| label  | varchar(80)   |      |     |            |                |
//| credit | decimal(10,2) |      |     | 0.00       |                |
//| debit  | decimal(10,2) |      |     | 0.00       |                |
//+--------+---------------+------+-----+------------+----------------+

$annee_sel = clean_request('annee');
$mois_sel = clean_request('mois');

if(isset($_REQUEST['action']) && $_REQUEST['action']=='lister') {
    if (!$annee_sel)
        $annee_sel = date("Y");
    $mois_sel_fin = sprintf("%02u",$mois_sel + 2);

    $from_date = $annee_sel."-".$mois_sel."-01";
    $to_date = $annee_sel."-".$mois_sel_fin."-31";
    $mon_sel = $trim_fr[$mois_sel]." ".$annee_sel;
} else {
    $mois_sel = sprintf("%02u", date("m") - (date("m")%3) + 1);
    $mois_sel_fin = sprintf("%02u",$mois_sel + 2);

    $from_date = date("Y")."-".$mois_sel."-01";
    $to_date = date("Y")."-".$mois_sel_fin."-31";
    $mon_sel = $trim_fr[$mois_sel]." ".date("Y");
}

$page->assign('from_solde', solde_until($from_date));
$page->assign('to_solde', solde_until($to_date));
$page->assign('mon_sel', $mon_sel);
$page->assign_by_ref('month_arr', $trim_fr);

$sql = "SELECT date,label,credit,debit FROM trezo.operations 
        WHERE date >= '$from_date' and date <= '$to_date'
        ORDER BY date";
$page->mysql_assign($sql,'ops');

$page->display();
?>
