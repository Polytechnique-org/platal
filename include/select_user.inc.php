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

if(empty($_REQUEST["xmat"]) || empty($_REQUEST["submit"])) {
    if (empty($_REQUEST["xmat"]) && (empty($_REQUEST["prenomR"]) || empty($_REQUEST["nomR"]))) {
        new_admin_page('marketing/utilisateurs_recherche.tpl');
        $page->run();
    }

    if (!empty($_REQUEST["xmat"])) {
	// on a un matricule, on affiche juste l'entrée correspondante
	$where = "matricule={$_REQUEST['xmat']}";
    } else {
	// on n'a pas le matricule, essayer de le trouver moi-meme, de le proposer
	// et de reafficher le formulaire avec les propositions de matricules

	// suppression accents et passage en majuscules des champs entrés
	$nomUS=replace_accent($_REQUEST["nomR"]);
	$nomup=strtoupper($nomUS);
	$nomup=str_replace("\'","'",$nomup);
	$prenomUS=replace_accent($_REQUEST["prenomR"]);
	$prenomup=strtoupper($prenomUS);
	$prenomup=str_replace("\'","'",$prenomup);

	// calcul de la plus longue chaine servant à l'identification
	$chaine1=strtok($nomup," -'");
	$chaine2=strtok(" -'");
	if ( strlen($chaine2) > strlen($chaine1) ) {
	    $chaine = $chaine2;
	}  else  {
	    $chaine = $chaine1;
	}

	if(strlen($_REQUEST["promoR"])==4) {
	    $rq="AND promo=".$_REQUEST["promoR"];
	} else {
	    $rq="";
	}

	$where = "prenom LIKE '%{$_REQUEST['prenomR']}%' AND nom LIKE '%$chaine%' $rq ORDER BY promo,nom";
    } // a-t-on xmat

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
