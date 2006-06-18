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
new_skinned_page('profil.tpl',AUTH_MDP);
$page->addCssLink('css/profil.css');
$page->assign('xorg_title','Polytechnique.org - Mon Profil');

require_once('tabs.inc.php');
require_once('profil.func.inc.php');

if (Post::has('register_from_ax_question')) {
    $globals->xdb->query('UPDATE auth_user_quick SET profile_from_ax = 1 WHERE user_id = {?}', Session::getInt('uid'));
}

require_once('synchro_ax.inc.php');
if (is_ax_key_missing())
    $page->assign('no_private_key', true);

if (Env::get('synchro_ax') == 'confirm' && !is_ax_key_missing()) {
    ax_synchronize(Session::get('bestalias'), Session::getInt('uid'));
    $page->trig('Ton profil a été synchronisé avec celui du site polytechniciens.com');
}

//on met a jour $opened_tab et $new_tab qui sont le tab du POST et le tab demande
// Tout d'abord, quel est le tab actuel ?
// si on vient d'un POST, old_tab etait le tab courant
if (Env::has('old_tab') && isset($tabname_array[Env::get('old_tab')])) {
    // on verifie que la valeur postee existe bien
    $opened_tab = Env::get('old_tab');
}
$new_tab = Env::has('suivant') ? get_next_tab($opened_tab) : $opened_tab;

// pour tous les tabs, la date de naissance pour verifier
// quelle est bien rentree et la date.
$res = $globals->xdb->query(
        "SELECT  naissance, DATE_FORMAT(date,'%d.%m.%Y')
           FROM  auth_user_md5
          WHERE  user_id={?}", Session::getInt('uid'));
list($naissance, $date_modif_profil) = $res->fetchOneRow();

// lorsqu'on n'a pas la date de naissance en base de données
if (!$naissance)  {
    // la date de naissance n'existait pas et vient d'être soumise dans la variable
    if (Env::has('birth')) {
	//en cas d'erreur :
	if (!ereg('[0-3][0-9][0-1][0-9][1][9]([0-9]{2})', Env::get('birth'))) {
	    $page->assign('etat_naissance', 'query');
            $page->trig_run('Date de naissance incorrecte ou incohérente.');
	}
      
	//sinon
        $birth = sprintf("%s-%s-%s", substr(Env::get('birth'),4,4), substr(Env::get('birth'),2,2), substr(Env::get('birth'),0,2));
	$globals->xdb->execute("UPDATE auth_user_md5 SET naissance={?} WHERE user_id={?}", $birth, Session::getInt('uid'));
	$page->assign('etat_naissance','ok');
	$page->run();
    } else {
	$page->assign('etat_naissance','query');
    }
    $page->run();//on affiche le formulaire pour naissance
}

//doit-on faire un update ?
if (Env::has('modifier') || Env::has('suivant')) {
    require_once("profil/get_{$opened_tab}.inc.php");
    require_once("profil/verif_{$opened_tab}.inc.php");

    if($page->nb_errs()) {
	require_once("profil/assign_{$opened_tab}.inc.php");
	$page->assign('onglet',$opened_tab);
	$page->assign('onglet_last',get_last_tab());
	$page->assign('onglet_tpl',"profil/$opened_tab.tpl");
	$page->run();
    }

    $date=date("Y-m-j");//nouvelle date de mise a jour

    //On sauvegarde l'uid pour l'AX
    /* on sauvegarde les changements dans user_changes :
    * on a juste besoin d'insérer le user_id de la personne dans la table
    */
    $globals->xdb->execute('REPLACE INTO user_changes SET user_id={?}', Session::getInt('uid'));

    if (!Session::has('suid')) {
	require_once('notifs.inc.php');
	register_watch_op(Session::getInt('uid'), WATCH_FICHE);
    }

    // mise a jour des champs relatifs au tab ouvert
    require_once("profil/update_{$opened_tab}.inc.php");
    
    $log =& Session::getMixed('log');
    $log->log('profil', $opened_tab);
    $page->assign('etat_update', 'ok');
}

require_once("profil/get_{$new_tab}.inc.php");
require_once("profil/verif_{$new_tab}.inc.php");
require_once("profil/assign_{$new_tab}.inc.php");

$page->assign('onglet',$new_tab);
$page->assign('onglet_last',get_last_tab());
$page->assign('onglet_tpl',"profil/$new_tab.tpl");
$page->run();

// vim:set et sws=4 sw=4 sts=4:
?>
