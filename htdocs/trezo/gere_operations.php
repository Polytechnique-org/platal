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
require_once("money/trezo.inc.php");
new_admin_page('trezo/gere_operations.tpl');

$action    = clean_request('action');
$op_id     = clean_request('op_id');
$op_date   = clean_request('op_date');
$op_label  = clean_request('op_label');
$op_credit = clean_request('op_credit');
$op_debit  = clean_request('op_debit');

$annee_sel = isset($_REQUEST['annee']) ? $_REQUEST['annee'] : date("Y");
$mois_sel  = isset($_REQUEST['mois']) ? $_REQUEST['mois'] : sprintf("%02u", date('m'));

$from_date = "$annee_sel-$mois_sel-01";
$to_date   = "$annee_sel-$mois_sel-31";
$mon_sel   = $trim_fr[$mois_sel]." $annee_sel";

switch($action) {
    case "edit":
        if ($op_id) {
            $res = $globals->xdb->query("SELECT date,label,credit,debit FROM money_trezo WHERE id={?}", $op_id);
            list($op_date,$op_label,$op_credit,$op_debit) = $res->fetchOneRow();
        }
        break;

    case "update":
        if (isDate($op_date)){
            $mydatefr = explode("/",$op_date);
            $mydate   = $mydatefr[2]."-".$mydatefr[1]."-".$mydatefr[0];
        } else {
            $mydate   = date("Y-m-d");
        }

        $sql = "replace into money_trezo set date='$mydate',label='".addslashes($op_label)."'";

        if ($op_credit) { $sql .= ',credit='.$op_credit; }
        if ($op_debit)  { $sql .= ",debit=".$op_debit;   }
        if ($op_id)     { $sql .= ",id='$op_id'";        }
        
        $globals->xdb->execute($sql);
        break;

    case "del":
        if ($op_id) {
            $globals->xdb->execute("DELETE FROM money_trezo WHERE id={?}", $op_id);
        }
        break;
}

$page->assign('op_id',     $op_id);
$page->assign('annee_sel', $annee_sel);
$page->assign('mois_sel',  $mois_sel);
$page->assign('op_date',   $op_date);
$page->assign('op_label',  $op_label);
$page->assign('op_debit',  $op_debit);
$page->assign('op_credit', $op_credit);
$page->assign('mon_sel',   $mon_sel);

$page->assign('from_solde', solde_until($from_date));
$page->assign('to_solde',   solde_until($to_date));
$page->assign_by_ref('month_arr', $mois_fr);

$sql = "SELECT  id,date,label,credit,debit 
          FROM  money_trezo
         WHERE  date >= '$from_date' AND date <= '$to_date' 
      ORDER BY  date";
$page->mysql_assign($sql, 'ops');

$page->run();
?>
