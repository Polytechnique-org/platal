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
 ***************************************************************************
        $Id: deces_promo.php,v 1.4 2004-08-31 10:03:29 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_admin_page('admin/deces_promo.tpl');

$promo = ( isset($_REQUEST["promo"]) ? $_REQUEST["promo"] : 0 );
if(isset($_REQUEST['sub10']))   $promo -= 10;
if(isset($_REQUEST['sub01']))   $promo --;
if(isset($_REQUEST['add01']))   $promo ++;
if(isset($_REQUEST['add10']))   $promo += 10;

$page->assign('promo',$promo);

if (isset($_REQUEST["valider"]) && $_REQUEST["valider"] == "Valider") {
    $res = $globals->db->query("SELECT matricule FROM identification WHERE promo = $promo");
    while (list($mat) = mysql_fetch_row($res)) {
        $globals->db->query("UPDATE identification SET deces='".$_REQUEST[$mat]."' WHERE matricule = '".$mat."'");
    }
}

$sql = "SELECT matricule, nom, prenom, deces FROM identification WHERE promo = $promo ORDER BY nom,prenom";
$page->mysql_assign($sql, 'decedes');

$page->run();
?>
