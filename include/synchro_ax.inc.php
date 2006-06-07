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


require_once("xorg.inc.php");

require_once('user.func.inc.php');

function get_user_ax($uid, $raw=false)
{
    require_once('webservices/ax/client.inc');

    global $globals;

    $res = $globals->xdb->query(
        "SELECT matricule_ax
           FROM auth_user_md5 AS u
          WHERE u.user_id = {?}", $uid);
    $matricule_ax = $res->fetchOneCell();

    $ancien = recupere_infos_ancien($matricule_ax);

    $userax = Array();
    $userax['matricule_ax'] = $matricule_ax;
    $userax['uid'] = $uid;
    
    $userax['nom'] = $ancien->Nom_patr();
    $userax['nom_usage'] = $ancien->Nom_usuel();
    if ($userax['nom_usage'] == $userax['nom']) $userax['nom_usage'] = '';
    $userax['prenom'] = $ancien->Prenom();
    $userax['sexe'] = ($ancien->Civilite() != 'M')?1:0;
    $userax['promo'] = $ancien->Promo();
    $userax['nationalite'] = $ancien->Nationalite();
    if ($userax['nationalite'] == 'F') $userax['nationalite'] = 'Français';
    //$userax['date'] = substr($ancien[12], 0, 10);
    $userax['mobile'] = $ancien->Mobile(0);
    if ($ancien->Corps() == 'D' || $ancien->Corps() == 'Z') {
        $userax['applis_join'] = "pas un corps";
    } else {
        $userax['applis_join'] = "Corps ".$ancien->Corps()." - ".$ancien->Grade();
    }
    $userax['adr_pro'] = array();
    
    for ($i = 0; $i < $ancien->Num_Activite(); $i++) {
        $jobax = array();
        $jobax['entreprise'] = $ancien->Entreprise($i);
        $jobax['fonction'] = $ancien->Fonction($i);
        $jobax['adr1'] = $ancien->Adresse_act_adresse1($i);
        $jobax['adr2'] = $ancien->Adresse_act_adresse2($i);
        $jobax['adr3'] = $ancien->Adresse_act_adresse3($i);
        $jobax['postcode']   = $ancien->Adresse_act_code_pst($i);
        $jobax['city'] = $ancien->Adresse_act_ville($i);
        $jobax['region'] = $ancien->Adresse_act_etat_region($i);
        $jobax['country'] = $ancien->Adresse_act_pays($i);
        $jobax['tel']  = $ancien->Adresse_act_tel($i);
        $jobax['fax']  = $ancien->Adresse_act_fax($i);
        $jobax['mobile'] = $ancien->Adresse_act_mobile($i);
        $userax['adr_pro'][] = $jobax;
    }

    $userax['adr'] = array();
    foreach ($array['dump']['adresse'] as $adr) {
        $adrax = array();
        $adrax['adr1'] = $ancien->Adresse1($i);
        $adrax['adr2'] = $ancien->Adresse2($i);
        $adrax['adr3'] = $ancien->Adresse3($i);
        $adrax['postcode'] = $ancien->Code_pst($i);
        $adrax['city'] = $ancien->Ville($i);
        $adrax['region'] = $ancien->Etat_region($i);
        $adrax['country'] = $ancien->Pays($i);
        $adrax['tel'] = $ancien->Tel($i);
        $adrax['fax'] = $ancien->Fax($i);
        $userax['adr'][] = $adrax;
    }
    if ($raw) {
        $userax['raw'] = $ancien;
    }

    return $userax;
}

function import_from_ax($userax, $nom_usage=false, $mobile=false, $del_address=null, $add_address=null, $del_pro=null, $add_pro=null, $nationalite=false)
{ 
    global $globals;

    if ($nom_usage) {
        $globals->xdb->execute("UPDATE auth_user_md5 SET nom_usage = {?} WHERE user_id = {?}", strtoupper($userax['nom_usage']), $userax['uid']);
    }
    
    if ($mobile) {
        $globals->xdb->execute("UPDATE auth_user_quick SET profile_mobile = {?} WHERE user_id = {?}", $userax['mobile'], $userax['uid']);
    }

    if ($nationalite) {
        if ($userax['nationalite'] == 'Français') {
            $userax['nationalite'] = 'FR';
        }
        $globals->xdb->execute("UPDATE auth_user_md5 SET nationalite = {?} WHERE user_id = {?}",  $userax['nationalite'], $userax['uid']);
    }
    if (is_array($del_address)) foreach($del_address as $adrid) {
        $globals->xdb->execute("DELETE FROM adresses WHERE uid = {?} AND adrid = {?}", $userax['uid'], $adrid);
        $globals->xdb->execute("DELETE FROM tels WHERE uid = {?} AND adrid = {?}", $userax['uid'], $adrid);
    }

    if (is_array($del_pro)) foreach($del_pro as $entrid) {
        $globals->xdb->execute("DELETE FROM entreprises WHERE uid = {?} AND entrid = {?}", $userax['uid'], $entrid);
    }

    if (is_array($add_address)) {

        $res = $globals->xdb->query(
            "SELECT adrid 
               FROM adresses 
              WHERE uid = {?} AND adrid >= 1
           ORDER BY adrid",
            $userax['uid']);
        $adrids = $res->fetchColumn();
        $i_adrid = 0;
        $new_adrid = 1;
    
        foreach($add_address as $adrid) {

            $adr = $userax['adr'][$adrid];

            // find the next adrid not used
            while ($adrids[$i_adrid] == $new_adrid) {
                $i_adrid++;
                $new_adrid++;
            }
            
            if ($adr['city']) {
            
                $res = $globals->xdb->query(
                "SELECT a2 FROM geoloc_pays
                 WHERE pays LIKE {?} OR country LIKE {?}",
                 $adr['country'], $adr['country']);
                 
                $a2 = $res->fetchOneCell();
            }
            if (!$a2) { $a2 = '00'; }
            
            $globals->xdb->execute(
                "INSERT INTO adresses
                         SET uid = {?}, adrid = {?},
                             adr1 = {?}, adr2 = {?}, adr3 = {?},
                             postcode = {?}, city = {?},
                         country = {?},
                         datemaj = NOW(),
                         pub = 'ax'",
                $userax['uid'], $new_adrid,
                $adr['adr1'], $adr['adr2'], $adr['adr3'],
                $adr['postcode'], $adr['city'],
                $a2);
            // import tels
            if ($adr['tel'])
                $globals->xdb->execute(
                    "INSERT INTO adresses
                        SET uid = {?}, adrid = {?}, tel = {?},
                        telid = 0, tel_type = 'Tel.', tel_pub = 'ax'",
                    $userax["uid"], $new_adrid, $adr['tel']);
            if ($adr['fax'])
                $globals->xdb->execute(
                    "INSERT INTO adresses
                        SET uid = {?}, adrid = {?}, tel = {?},
                        telid = 1, tel_type = 'Fax', tel_pub = 'ax'",
                    $userax["uid"], $new_adrid, $adr['fax']);
        }
    }
    
    if (is_array($add_pro)) {

        $res = $globals->xdb->query(
            "SELECT entrid FROM entreprises 
              WHERE uid = {?} AND entrid >= 1 ORDER BY entrid",
            $userax['uid']);
        $entrids = $res->fetchColumn();
        $i_entrid = 0;
        $new_entrid = 1;
       
        $nb_entr = count($entrids);

        foreach($add_pro as $entrid) if ($nb_entr < 2) {

            $nb_entr++;
        
            $pro = $userax['adr_pro'][$entrid];

            // find the next adrid not used
            while ($entrids[$i_entrid] == $new_entrid) {
                $i_entrid++;
                $new_entrid++;
            }
            
            if ($pro['country']) {
                $res = $globals->xdb->query(
                    "SELECT a2 FROM geoloc_pays
                      WHERE pays LIKE {?} OR country LIKE {?}",
                    $pro['country'], $pro['country']);
                $a2 = $res->fetchOneCell();
            }
            if (!$a2) { $a2 = '00'; }
            
            $globals->xdb->execute(
                "INSERT INTO entreprises
                         SET uid = {?}, entrid = {?},
                         entreprise = {?}, poste = {?},
                         adr1 = {?}, adr2 = {?}, adr3 = {?},
                         postcode = {?}, city = {?},
                         country = {?},
                         tel = {?}, fax = {?},
                         pub = 'ax', adr_pub = 'ax', tel_pub = 'ax'",
                $userax['uid'], $new_entrid,
                $pro['entreprise'], $pro['fonction'],
                $pro['adr1'], $pro['adr2'], $pro['adr3'],
                $pro['postcode'], $pro['city'],
                $a2,
                $pro['tel'], $pro['fax']);
        }
    }
}

function copy_from_ax($uid)
{
     $uax = get_user_ax($uid);
     import_from_ax($uax, false, true, null, array_keys($uax['adr']), null, array_keys($uax['adr_pro']), true); 
}

// vim:set et sw=4 sts=4 sws=4:
?>
