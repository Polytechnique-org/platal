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
        $Id: referent.php,v 1.11 2004-11-22 20:04:36 x2000habouzit Exp $
 ***************************************************************************/


require_once("xorg.inc.php");
new_skinned_page('referent.tpl',AUTH_COOKIE);

$nb_max_resultats_total = 100;
$nb_max_resultats_par_page = 10;
$show_formulaire = true;
$page->assign_by_ref('show_formulaire', $show_formulaire);
$page->assign('show_resultat', false);


$secteur_selectionne = '';
if(isset($_POST['secteur'])){
    $secteur_selectionne = "{$_POST['secteur']}";
}

$ss_secteur_selectionne = '';
if(isset($_POST['ss_secteur'])){
    $ss_secteur_selectionne = $_POST['ss_secteur'];
}

$pays_selectionne = '00';
if(isset($_POST['pays'])){
    $pays_selectionne = $_POST['pays'];
}

$expertise_champ = '';
if(isset($_POST['expertise'])){
    $expertise_champ = stripslashes($_POST['expertise']);
}
      
$page->assign('pays_selectionne',$pays_selectionne);
$page->assign('expertise_champ',$expertise_champ);
$page->assign('secteur_selectionne',$secteur_selectionne);
$page->assign('ss_secteur_selectionne',$ss_secteur_selectionne);

//recuperation des noms de secteurs
$res = $globals->db->query("SELECT id, label FROM emploi_secteur");
$secteurs[''] = '';
while(list($tmp_id, $tmp_label) = mysql_fetch_row($res)) {
    $secteurs[$tmp_id] = $tmp_label;
}
mysql_free_result($res);
$page->assign_by_ref('secteurs', $secteurs);

//on recupere les sous-secteurs si necessaire
$ss_secteurs[''] = '';
if(!empty($secteur_selectionne))
{
    $res = $globals->db->query("SELECT id, label FROM emploi_ss_secteur
				 WHERE secteur = '$secteur_selectionne'");
    while(list($tmp_id, $tmp_label) = mysql_fetch_row($res)) {
	$ss_secteurs[$tmp_id] = $tmp_label;
    }
    mysql_free_result($res);
}
$page->assign_by_ref('ss_secteurs', $ss_secteurs);

//recuperation des noms de pays
$res = $globals->db->query("SELECT a2, pays FROM geoloc_pays WHERE pays <> '' ORDER BY pays");
$pays['00'] = '';
while(list($tmp_id, $tmp_label) = mysql_fetch_row($res)) {
    $pays[$tmp_id] = $tmp_label;
}
mysql_free_result($res);
$page->assign_by_ref('pays', $pays);

//On vient d'un formulaire
if(isset($_REQUEST['Chercher'])){

    $champ_select = 'm.uid, a.prenom, a.nom, a.promo, l.alias, m.expertise';
    $champ_select = $champ_select.', mp.pid';
    $champ_select = $champ_select.', ms.secteur, ms.ss_secteur';

    $clause_from = ' FROM mentor as m LEFT JOIN auth_user_md5 AS a ON(m.uid = a.user_id)'
                 . ' INNER JOIN aliases AS l ON (a.user_id=l.id AND FIND_IN_SET(\'bestalias\',l.flags)'
                 . ' LEFT JOIN mentor_pays AS mp ON(m.uid = mp.uid)'
                 . ' LEFT JOIN mentor_secteurs AS ms ON(m.uid = ms.uid)';

    $clause_where = 'WHERE';

    if ($pays_selectionne != '00') {
	$clause_where = $clause_where." mp.pid = '$pays_selectionne' AND";
    }
    if ($secteur_selectionne != '')
    {
	$clause_where = $clause_where." ms.secteur = '$secteur_selectionne' AND";
	if($ss_secteur_selectionne)
	    $clause_where = $clause_where." ms.ss_secteur = '$ss_secteur_selectionne' AND";
    }

    if($expertise_champ != '')
    {
	$clause_where = $clause_where." MATCH(m.expertise) AGAINST('".addslashes($expertise_champ)."') AND";
    }

    if($clause_where != 'WHERE'){

	$show_formulaire = false;
	$clause_where = substr($clause_where, 0, -3); //on vire le dernier AND

	$sql = "SELECT $champ_select $clause_from $clause_where
	    GROUP BY uid ORDER BY RAND({$_SESSION['uid']})";

	$res = $globals->db->query($sql);

	if (mysql_num_rows($res) == 0) {
	    $page->assign('recherche_trop_large',true);
	    mysql_free_result($res);
	}
	else{
	    if (isset($_REQUEST['page_courante'])) {
		$page_courante = (int) $_REQUEST['page_courante'];
	    }
	    else{
		$page_courante = 1;
	    }

	    $current_uid = 0;
	    $nb_resultats = 0;
	    $page->assign('resultats',true);
	    $personnes = Array();
	    $page->assign_by_ref('personnes',$personnes);
	    while((list($uid, $prenom, $nom, $promo, $bestalias,
			    $expertise_bd, $pays_id, $secteur_id, $ss_secteur_id) = mysql_fetch_row($res))
		    || ($nb_resultats >= $nb_max_resultats_total)){
		if($current_uid != $uid){
		    $current_uid = $uid;
		    $page_correspondante = (int)($nb_resultats / $nb_max_resultats_par_page) +1;
		    $nb_resultats++;
		    if( $page_correspondante == $page_courante){
			$pers_trouve['nom'] = $nom;
			$pers_trouve['prenom'] = $prenom;
			$pers_trouve['promo'] = $promo;
			$pers_trouve['bestalias'] = $bestalias;
			$pers_trouve['expertise'] = $expertise_bd;
			$personnes[] = $pers_trouve;
		    }
		}
	    }
	    $nb_pages = (int) ($nb_resultats/$nb_max_resultats_par_page) + 1;
	    $page->assign('nb_pages_total', $nb_pages);
	    $page->assign('page_courante', $page_courante);
	    mysql_free_result($res);
	}//fin else
    }//fin if clause_where non vide
}//fin if Chercher
//sinon on affiche le formulaire

if ($show_formulaire) {
    $res = $globals->db->query("SELECT count(*) FROM mentor");
    list($nb) = mysql_fetch_row($res);
    mysql_free_result($res);

    $page->assign('mentors_number',$nb);
}

$page->run();

?>
