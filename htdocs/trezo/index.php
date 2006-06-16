<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

require_once("xorg.inc.php");
require_once("money/trezo.inc.php");
new_skinned_page('trezo/index.tpl', AUTH_COOKIE);

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

$annee_sel = isset($_REQUEST['annee']) ? $_REQUEST['annee'] : date("Y");
$mois_sel  = isset($_REQUEST['mois']) ? $_REQUEST['mois'] : sprintf("%02u", date('m') - (intval(date('m')-1)%3));

$mois_sel_fin = sprintf("%02u",$mois_sel + 2);
$from_date    = "$annee_sel-$mois_sel-01";
$to_date      = "$annee_sel-$mois_sel_fin-31";
$mon_sel      = $trim_fr[$mois_sel]." $annee_sel";

$page->assign('from_solde', solde_until($from_date));
$page->assign('to_solde', solde_until($to_date));
$page->assign('annee_sel', $annee_sel);
$page->assign('mois_sel', $mois_sel);
$page->assign('mon_sel', $mon_sel);
$page->assign_by_ref('month_arr', $trim_fr);

$page->assign('ops', $globals->xdb->iterator(
            "SELECT date,label,credit,debit FROM money_trezo  WHERE date >= {?} and date <= {?} ORDER BY date",
            $from_date, $to_date));

$page->run();
?>
