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

if (!Env::has("xmat") || !Env::has("submit")) {
    if ( !Env::has("xmat") && (!Env::has("prenomR") || !Env::has("nomR")) ) {
        new_admin_page('marketing/utilisateurs_recherche.tpl');
        $page->run();
    }

    if (Env::has("xmat")) {
	$where = "matricule=".Env::getInt('xmat');
    } else {
	$nom    = Env::get('nomR');
	$prenom = Env::get('prenomR');

	// calcul de la plus longue chaine servant à l'identification
	$chaine1 = strtok($nom," -'");
	$chaine2 = strtok(" -'");
        $chaine  = ( strlen($chaine2) > strlen($chaine1) ) ? $chaine2 : $chaine1;

        $rq = strlen(Env::get("promoR")==4 ? "AND promo=".Env::getInt("promoR") : "";

	$where = "prenom LIKE '%$prenom%' AND nom LIKE '%$chaine%' $rq ORDER BY promo,nom";
    }

    $sql = "SELECT  matricule,matricule_ax,promo,nom,prenom,comment,appli,flags,last_known_email,deces,user_id
              FROM  auth_user_md5
             WHERE  perms IN ('admin','user') AND deces=0 AND $where";

    new_admin_page('marketing/utilisateurs_select.tpl');
    $page->mysql_assign($sql, 'nonins');
    $page->assign('id_actions', $id_actions);
    $page->run();
}

function exit_error($err) {
    global $page;
    new_admin_page('marketing/utilisateurs_recherche.tpl');
    $page->assign('err', $err);
    $page->run();
}
?>
