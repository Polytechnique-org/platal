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
        $Id: public.php,v 1.3 2004-08-31 10:03:30 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('marketing/public.php', AUTH_MDP);

if (! isset($_REQUEST["num"])) { exit; }

$mat = (((integer) $_REQUEST["num"]) + 100) / 2;

$res = $globals->db->query("SELECT nom,prenom,promo FROM identification WHERE matricule = '$mat'");
if (list($nom, $prenom, $promo) = mysql_fetch_row($res)) {
    $page->assign('prenom', $prenom);
    $page->assign('nom', $nom);
    $page->assign('promo', $promo);
}

if (isset($_REQUEST["valide"])) {
	$globals->db->query(
	  "INSERT INTO marketing
       SET expe = {$_SESSION['uid']}, dest = '$mat', email = '{$_REQUEST['mail']}', flags = '".(($_REQUEST["origine"]=="perso") ? "mail_perso" : "")."'"
	);
}

$page->run();
?>
