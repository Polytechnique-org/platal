<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

require_once('xorg.inc.php');
require_once("trezo.inc.php");
new_admin_page('trezo/gere_operations.tpl');

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

$action = clean_request('action');
$annee_sel = clean_request('annee');
$mois_sel = clean_request('mois');
$operation_id = clean_request('operation_id');
$operation_date = clean_request('operation_date');
$operation_label = clean_request('operation_label');
$operation_credit = clean_request('operation_credit');
$operation_debit = clean_request('operation_debit');

//selection mois et date
if (!$annee_sel) $annee_sel = date("Y");
    
if ($mois_sel) {
    $from_date = $annee_sel."-".$mois_sel."-01";
    $to_date = $annee_sel."-".$mois_sel."-31";
    $mon_sel = $mois_fr[$mois_sel]." ".$annee_sel;
} else {
    $from_date = date("Y-m")."-01";
    $to_date = date("Y-m")."-31";
    $mon_sel = $mois_fr[date("m")]." ".date("Y");
}

switch($action) {
    case "edit":
        if ($operation_id) {
            $res=$globals->db->query("select date,label,credit,debit from trezo.operations where id='$operation_id'");
            list($operation_date,$operation_label,$operation_credit,$operation_debit)=mysql_fetch_row($res);
        }
    break;
    case "update":
        $operation_date = clean_request('operation_date');
        $operation_label = clean_request('operation_label');
        $operation_credit = clean_request('operation_credit');
        $operation_debit = clean_request('operation_debit');
        $sql_add = "";

        if ($operation_credit)
            $sql_add = ',credit='.$operation_credit;
        if ($operation_debit)
            $sql_add = $sql_add.",debit=".$operation_debit;

        if (isDate($operation_date)){
            $mydatefr = explode("/",$operation_date);
            $mydate = $mydatefr[2]."-".$mydatefr[1]."-".$mydatefr[0];
        }else{
            $mydate = date("Y-m-d");
        }

        if ($operation_id) {
            $sql = "update trezo.operations set date='".$mydate."',label='".$operation_label."'".$sql_add." where id='".$operation_id."'";
        } else { 
            $sql = "insert into trezo.operations set date='".$mydate."',label='".$operation_label."'".$sql_add;
        }
    break;
    case "del":
        if ($operation_id) {
            $globals->db->query("delete from trezo.operations where id='".$operation_id."'");
        }
    break;

} // $action

$page->assign('operation_id', $operation_id);
$page->assign('annee_sel', $annee_sel);
$page->assign('mois_sel', $mois_sel);
$page->assign('operation_date', $operation_date);
$page->assign('operation_label', $operation_label);
$page->assign('operation_debit', $operation_debit);
$page->assign('operation_credit', $operation_credit);
$page->assign('mon_sel', $mon_sel);

$page->assign('from_solde', solde_until($from_date));
$page->assign('to_solde', solde_until($to_date));
$page->assign_by_ref('month_arr', $mois_fr);

$sql = "SELECT id,date,label,credit,debit 
        FROM trezo.operations
        WHERE date >= '$from_date' AND date <= '$to_date' 
        ORDER BY date";
$page->mysql_assign($sql, 'ops');

$page->run();
?>
