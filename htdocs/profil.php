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
        $Id: profil.php,v 1.6 2004-08-31 14:48:56 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('profil.tpl',AUTH_MDP, true, 'profil.head.tpl');

//on charge les fonctions
require_once('profil.inc.php');

//on met a jour $opened_tab et $new_tab qui sont le tab du POST et le tab demande
// Tout d'abord, quel est le tab actuel ?
// si on vient d'un POST, old_tab etait le tab courant
if(isset($_REQUEST['old_tab']) && isset($tabname_array[$_REQUEST['old_tab']])) // on verifie que la valeur postee existe bien
    $opened_tab = $_REQUEST['old_tab'];
$new_tab = isset($_REQUEST['suivant']) ? get_next_tab($opened_tab) : $opened_tab;

// pour tous les tabs, on recupere les bits car on a besoin de tous les bits pour en mettre a jour un, la date d naissance pour verifier
// quelle est bien rentree et la date.
$sql = "SELECT  FIND_IN_SET('mobile_public', bits), FIND_IN_SET('mobile_ax', bits),
	        FIND_IN_SET('web_public', bits), FIND_IN_SET('libre_public', bits),
		naissance, DATE_FORMAT(date,'%d.%m.%Y')
	  FROM  auth_user_md5
         WHERE  user_id=".$_SESSION['uid'];
$result = $globals->db->query($sql);
list($mobile_public, $mobile_ax,$web_public, $libre_public, $naissance, $date_modif_profil) = mysql_fetch_row($result);

// lorsqu'on n'a pas la date de naissance en base de données
if (!$naissance)  {
    // la date de naissance n'existait pas et vient d'être soumise dans la variable
    // $_REQUEST['birth']
    if (isset($_REQUEST['birth'])) {
	//en cas d'erreur :
	if (!ereg("[0-3][0-9][0-1][0-9][1][9]([0-9]{2})", $_REQUEST['birth'])) {
	    $page->assign('etat_naissance','erreur');
	    $page->run();//on reaffiche le formulaire
	}
      
	//sinon
	$globals->db->query("UPDATE auth_user_md5 SET naissance='{$_REQUEST['birth']}' WHERE user_id=".$_SESSION['uid']);
	$page->assign('etat_naissance','ok');
	$page->run();
    } else {
	$page->assign('etat_naissance','query');
    }
    $page->run();//on affiche le formulaire pour naissance
}

//doit-on faire un update ?
if (isset($_REQUEST['modifier']) || isset($_REQUEST['suivant'])) {
    require_once("profil/profil_{$opened_tab}.inc.php");
    require_once("profil/verif_{$opened_tab}.inc.php");

    $date=date("Y-m-j");//nouvelle date de mise a jour

    //On sauvegarde l'uid pour l'AX
    /* on sauvegarde les changements dans user_changes :
    * on a juste besoin d'insérer le user_id de la personne dans la table
    */
    $globals->db->query("replace into user_changes  set user_id='{$_SESSION['uid']}'");

    //Mise a jour des bits
    // bits : set('mobile_public','mobile_ax','web_public','libre_public')
    $bits_reply = "";
    if ($mobile_public) $bits_reply .= 'mobile_public,';
    if ($mobile_ax) $bits_reply .= 'mobile_ax,';
    if ($web_public) $bits_reply .= 'web_public,';
    if ($libre_public) $bits_reply .= 'libre_public,';
    if (!empty($bits_reply)) $bits_reply = substr($bits_reply, 0, -1);
    $sql = "UPDATE auth_user_md5 set bits = '$bits_reply'";
    // si on est en suid on ne met pas à jour la date
    if(isset($_SESSION['suid'])) {
        $sql = $sql." WHERE user_id={$_SESSION['uid']}";
    } else {
        $sql = $sql.",date='$date' WHERE user_id={$_SESSION['uid']}";
    }
    $globals->db->query($sql);

    // mise a jour des champs relatifs au tab ouvert
    require_once("profil/update_{$opened_tab}.inc.php");

    $_SESSION['log']->log("profil");
    $page->assign('etat_update','ok');
}

require_once("profil/profil_{$new_tab}.inc.php");
require_once("profil/verif_{$new_tab}.inc.php");

$page->assign('onglet',$new_tab);
$page->assign('onglet_last',get_last_tab());
$page->assign('onglet_tpl',"profil/$new_tab.tpl");
$page->run();

?>
