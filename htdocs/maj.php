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
        $Id: maj.php,v 1.2 2004-08-31 10:03:28 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");

$erreur = Array();

if (isset($_REQUEST['n'])) {
    $sql = "SELECT * FROM envoidirect WHERE uid='".$_REQUEST["n"]."'";
    $result = $globals->db->query($sql);
    $ligne = mysql_fetch_array($result); 
}

if (isset($ligne) && $ligne) {
    // il faut remettre le matricule dans son format de saisie
    $_REQUEST['matricule'] = strlen($ligne['matricule']>6) ?
	substr($ligne['matricule'],2) : $ligne['matricule'];
    $_REQUEST['promo'] = $ligne['promo'];
    $_REQUEST['nom'] = $ligne['nom'];
    $_REQUEST['prenom'] = $ligne['prenom'];
    $_REQUEST['email'] = $ligne['email'];

    // on veut basculer sur inscrire.php

    require("identification.inc.php");

    new_skinned_page('inscrire.form_data.tpl', AUTH_PUBLIC, true);
    $page->assign('gotourl', $gotourl);
    $gotourl = "inscrire.php";
    $page->run();
}

new_skinned_page('maj.tpl', AUTH_PUBLIC);
$page->run();
?>
