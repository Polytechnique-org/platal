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
        $Id: profil.php,v 1.4 2004-08-31 10:03:28 x2000habouzit Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");
new_skinned_page('profil.tpl',AUTH_COOKIE, true, 'profil.head.tpl');

if ($no_update_bd) {
    $_REQUEST = array();
    $_POST = array();
    $_GET = array();
    $str_error = "La base de donnée est actuellement en consultation seulement, tu peux voir ton profil mais pas y faire de modifications.";
}
else
  $str_error = '';//chaine contenant toutes les erreurs

$page->assign_by_ref('profil_error',$str_error);

//on charge les fonctions
require_once('profil.inc.php');

//on met a jour $opened_tab et $new_tab qui sont le tab du POST et le tab demande
// Tout d'abord, quel est le tab actuel ?
// si on vient d'un POST, old_tab etait le tab courant
if(isset($_REQUEST['old_tab']))
  if(isset($tabname_array[$_REQUEST['old_tab']])) // on verifie que la valeur postee existe bien
    $opened_tab = $_REQUEST['old_tab'];


//en cas de bouton valider + passer au suivant, on definit ces deux variables
if(isset($_REQUEST['modifier+suivant'])){
  $_REQUEST['modifier'] = 'modifier';
  $_REQUEST['go_to_next'] = 'go_to_next';
}

$new_tab = $opened_tab;

if(isset($_REQUEST['new_tab'])){
        if(isset($tabname_array[$_REQUEST['new_tab']]))
                $new_tab = $_REQUEST['new_tab'];
	else
		$new_tab = $opened_tab;
}
else
        $new_tab = $opened_tab;

//echo "opening profil_{$opened_tab}.inc.php<br>";
require_once("profil/profil_{$opened_tab}.inc.php");

// pour tous les tabs, on recupere les bits car on a besoin de tous les bits pour en mettre a jour un, la date d naissance pour verifier
// quelle est bien rentree et la date.
$sql = "SELECT ".
"FIND_IN_SET('mobile_public', bits), FIND_IN_SET('mobile_ax', bits)".
", FIND_IN_SET('web_public', bits)".
", FIND_IN_SET('libre_public', bits)".
", naissance, DATE_FORMAT(date,'%d.%m.%Y')".
" FROM auth_user_md5".
" WHERE user_id=".$_SESSION['uid'];

$result = mysql_query($sql);
list($mobile_public, $mobile_ax,
$web_public, $libre_public,
$naissance, $date_modif_profil) = mysql_fetch_row($result);

if(mysql_errno($conn) !=0) echo mysql_errno($conn).": ".mysql_error($conn);

//en cas de modifications
if(isset($_REQUEST['modifier']) && ($opened_tab == 'general')){
  $mobile_public = (isset($_REQUEST['mobile_public']));
  $mobile_ax = (isset($_REQUEST['mobile_ax']));
  $libre_public = (isset($_REQUEST['libre_public']));
  $web_public = (isset($_REQUEST['web_public']));
}


// lorsqu'on n'a pas la date de naissance en base de données
if (!$naissance && !$no_update_bd)  {

  # la date de naissance n'existait pas et vient d'être soumise dans la variable
  # $_REQUEST['birth']
  if (isset($_REQUEST['birth']) && !$no_update_bd) {

    //en cas d'erreur :
    if (!ereg("[0-3][0-9][0-1][0-9][1][9]([0-9]{2})", $_REQUEST['birth'])) {
      $page->assign('etat_naissance','erreur');
      $page->run();//on reaffiche le formulaire
    }
  
    //sinon
    mysql_query("UPDATE auth_user_md5 SET naissance='{$_REQUEST['birth']}' WHERE user_id=".$_SESSION['uid']);
    $page->assign('etat_naissance','ok');
    $page->run();
}
else
  $page->assign('etat_naissance','query');
  $page->run();//on affiche le formulaire pour naissance
}


// inclure tous les tests sur les champs du formulaire
require_once("profil/verif_{$opened_tab}.inc.php");
    
if($str_error!=""){
  $new_tab = $opened_tab;
}
else{
  if(isset($_REQUEST['go_to_next']))
    $new_tab = get_next_tab($opened_tab);

  //doit-on faire un update ?
  if (!empty($_REQUEST['modifier'])  && !$no_update_bd) {
    
    $date=date("Y-m-j");//nouvelle date de mise a jour

    
    //On sauvegarde l'uid pour l'AX
    /* on sauvegarde les changements dans user_changes :
    * on a juste besoin d'insérer le user_id de la personne dans la table
    */
    $sql="insert into user_changes ('{$_SESSION['uid']}')";
    /* l'insertion ne se fait que s'il n'existe pas un enregistrement avec le même
     * user_id car user_id est la clé primaire.
     */
    mysql_query($sql);
    
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
    mysql_query($sql);

    // mise a jour des champs relatifs au tab ouvert
    require_once("profil/update_{$opened_tab}.inc.php");

    //Warning : ca ne marche que si update_<tab>.inc.php contient bien une requete mysql qui mettra errno a 0
    if (mysql_errno() == 0  /*|| mysql_affected_rows() == 1*/ ) {

      $_SESSION['log']->log("profil");
      
      $page->assign('etat_update','ok');

      //echo "opening profil_$new_tab.inc.php<br>";
      require_once("profil/profil_{$new_tab}.inc.php");
      require_once("profil/verif_{$new_tab}.inc.php");
    }
    else{
      $page->assign('etat_update','error');
      if($site_dev){
        echo mysql_error();
	echo '(message d\'erreur seulement présent en dev)<br />';
      }
      
    }

  }//fin if(update)
  else { 
    /* le profil n'a pas encore été soumis, on se contente de l'afficher pour
     * permettre une modification
     * si les boutons "plus..." ont été cliqués, on indique à la variable qui
     * contrôle l'affichage des listes de choix d'afficher tous les choix pour le
     * bouton cliquer (ex: toutes les applis)
     */

      require_once("profil/profil_{$new_tab}.inc.php");
      require_once("profil/verif_{$new_tab}.inc.php");

     
  }
}//fin else(!erreur)

$page->assign('onglet',$new_tab);
$page->assign('onglet_last', get_last_tab());
$page->assign('onglet_tpl',"profil/$new_tab.tpl");
$page->run();

?>
