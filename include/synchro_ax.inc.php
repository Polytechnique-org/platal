<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require_once('user.func.inc.php');

function is_ax_key_missing() {
    global $globals;
    return !isset($globals->webservice->private_key_ax) || !is_file($globals->webservice->private_key_ax);
}

function get_user_ax($matricule_ax, $raw=false)
{
    require_once('webservices/ax/client.inc');

    $ancien = recupere_infos_ancien($matricule_ax);

    $userax = Array();
    $userax['matricule_ax'] = $matricule_ax;

    $userax['nom'] = strtoupper($ancien->Nom_patr());
    $userax['nom_usage'] = strtoupper($ancien->Nom_usuel());
    if ($userax['nom_usage'] == $userax['nom']) $userax['nom_usage'] = '';
    $userax['prenom'] = $ancien->Prenom();
    $userax['sexe'] = ($ancien->Civilite() != 'M')?1:0;
    $userax['promo'] = $ancien->Promo();
/*    $userax['nationalite'] = $ancien->Nationalite();
    if ($userax['nationalite'] == 'F') $userax['nationalite'] = 'Français'; */
    //$userax['date'] = substr($ancien[12], 0, 10);
    $userax['mobile'] = trim($ancien->Mobile(0));
/*    if ($ancien->Corps() == 'D' || $ancien->Corps() == 'Z') {
        $userax['corps'] = false;
    } else {
        $userax['corps'] = $ancien->Corps();
        $userax['corps_grade'] = $ancien->Grade();
    } */
    $userax['adr_pro'] = array();

    for ($i = 0; $i < $ancien->Num_Activite(); $i++) {
        $jobax = array();
        $jobax['entreprise'] = $ancien->Entreprise($i);
        if (!$jobax['entreprise'])
            $jobax['entreprise'] = $ancien->Adresse_act_adresse1($i);
        $jobax['poste'] = $ancien->Fonction($i);
        $jobax['adr1'] = $ancien->Adresse_act_adresse1($i);
        $jobax['adr2'] = $ancien->Adresse_act_adresse2($i);
        $jobax['adr3'] = $ancien->Adresse_act_adresse3($i);
        $jobax['postcode']   = $ancien->Adresse_act_code_pst($i);
        $jobax['city'] = $ancien->Adresse_act_ville($i);
        $jobax['region'] = $ancien->Adresse_act_etat_region($i);
        $jobax['countrytxt'] = ucwords(strtolower($ancien->Adresse_act_pays($i)));
        $jobax['tel']  = $ancien->Adresse_act_tel($i);
        $jobax['fax']  = $ancien->Adresse_act_fax($i);
        $jobax['mobile'] = $ancien->Adresse_act_mobile($i);
        $jobax['pub'] = 'ax';
        $jobax['tel_pub'] = 'ax';
        $jobax['adr_pub'] = 'ax';
        $jobax['email_pub'] = 'ax';
        $userax['adr_pro'][] = $jobax;
    }

    $userax['adr'] = array();
    for($i=$ancien->Num_adresse() - 1; $i >= 0; $i--) {
        $adrax = array();
        $adrax['adr1'] = $ancien->Adresse1($i);
        $adrax['adr2'] = $ancien->Adresse2($i);
        $adrax['adr3'] = $ancien->Adresse3($i);
        $adrax['postcode'] = $ancien->Code_pst($i);
        $adrax['city'] = $ancien->Ville($i);
        $adrax['region'] = $ancien->Etat_region($i);
        $adrax['countrytxt'] = ucwords(strtolower($ancien->Pays($i)));
        $adrax['pub'] = 'ax';
        if ($ancien->Tel($i) || $ancien->Fax($i)) {
            $adrax['tels'] = array();
            if ($tel = $ancien->Tel($i))
                $adrax['tels'][] = array('tel' => $tel, 'tel_type' => 'Tél.', 'tel_pub' => 'ax');
            if ($tel = $ancien->Fax($i))
                $adrax['tels'][] = array('tel' => $tel, 'tel_type' => 'Fax', 'tel_pub' => 'ax');
        }
        if ($ancien->Mobile($i)) $userax['mobile'] = $ancien->Mobile($i);
        $userax['adr'][$i] = $adrax;
    }

/*    $userax['formation'] = array();
    for($i=$ancien->Num_formation() - 1; $i >= 0; $i--)
        $userax['formation'][$i] = $ancien->Formation($i);*/

    return $userax;
}

function ax_synchronize($login, $uid) {
    require_once('user.func.inc.php');
    require_once 'profil.func.inc.php';
    // get details from user, but looking only info that can be seen by ax
    $user  = get_user_details($login, $uid, 'ax');
    $userax= get_user_ax($user['matricule_ax']);
    $diff = diff_user_details($userax, $user, 'ax');
    set_user_details($user['user_id'], $diff);
}
// vim:set et sw=4 sts=4 sws=4 enc=utf-8:
?>
