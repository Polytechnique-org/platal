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
    require_once('webservices/manageurs.inc.php');
    require_once('webservices/manageurs.client.inc.php');

    global $globals;

    $res = $globals->xdb->query(
        "SELECT matricule_ax
           FROM auth_user_md5 AS u
          WHERE u.user_id = {?}", $uid);
    $matricule_ax = $res->fetchOneCell();

    $array = get_annuaire_infos(2, $matricule_ax, 0);

    $userax = Array();
    $userax['matricule_ax'] = $matricule_ax;
    $userax['uid'] = $uid;
    
    $ancien = $array['dump']['ancien'];
    $userax['nom'] = $ancien[0];
    // ancien1 = ?
    $userax['epouse'] = ($ancien[2] != $ancien[0])?$ancien[2]:"";
    // ancien3 = ?
    $userax['prenom'] = $ancien[4];
    $userax['sexe'] = ($ancien[5] != 'M')?1:0;
    $userax['promo'] = $ancien[6];
    // ancien7 = Type de membre à l'AX
    // ancien8 = dernière année de cotisation
    $userax['nationalite'] = $ancien[9];
    if ($userax['nationalite'] == 'F') $userax['nationalite'] = 'Français';
    // ancien10 = ?
    // ancine11 = ?
    $userax['date'] = substr($ancien[12], 0, 10);
    $userax['mobile'] = $array['cell'];
    if ($ancien[13] == 'D' || $ancien[13] == 'Z') {
        $userax['applis_join'] = "pas un corps";
    } else {
        $userax['applis_join'] = "Corps ".$ancien[13]." - ".$ancien[14];
    }
    // ancien15 = login AX
    // ancien16 = ?
    // ancien17 = prenom.nom

    $userax['adr_pro'] = array();
    if (is_array($array['dump']['pro'])) {
        foreach ($array['dump']['pro'] as $job) {
            // job0 = code identifiant de l'entreprise
            $jobax['entreprise'] = $job[1];
            // job2 = comme job1 (peut etre plus court pour les noms longs)
            // job4 = sigle de l'entreprise
            // job5 = maj de l'adresse pro
            $jobax['fonction'] = $job[6];
            // job7 = ? peut être id de l'adresse dans la base AX
            // job8 = matricule AX
            // job9 = type d'adresse PRO = professionelle
            $jobax['adr1'] = $job[10];
            $jobax['adr2'] = $job[11];
            $jobax['adr3'] = $job[12];
            $jobax['cp']   = $job[13];
            $jobax['ville'] = $job[14];
            // job15 = ?
            // job16 = ?
            $jobax['pays'] = $job[17];
            // job18 = ?
            $jobax['tel']  = $job[19];
            $jobax['fax']  = $job[20];
            // job21 = f ?
            // job22 = ?
            // job23 = date de mise à jour de l'adresse
            // job24 = ?
            $userax['adr_pro'][] = $jobax;
        }
    }

    $userax['adr'] = array();
    if (is_array($array['dump']['adresse'])) {
        foreach ($array['dump']['adresse'] as $adr) {
            // adr0 : ?
            // adr1 = matricule ax
            // adr2 = type d'adresse P = personnelle
            $adrax['adr1'] = $adr[3];
            $adrax['adr2'] = $adr[4];
            $adrax['adr3'] = $adr[5];
            $adrax['cp'] = $adr[6];
            $adrax['ville'] = $adr[7];
            // adr8 = ?
            // adr9 = ?
            $adrax['pays'] = $adr[10];
            // adr 11 = ?
            $adrax['tel'] = $adr[12];
            $adrax['fax'] = $adr[13];
            // adr 14 = t ?
            // adr15 = ?
            // adr 16 = date de mise a jour
            $userax['adr'][] = $adrax;
        }
    }
    if ($raw) {
        $userax['raw'] = $array;
    }

    return $userax;
}

function import_from_ax($userax, $epouse=false, $mobile=false, $del_address=null, $add_address=null, $del_pro=null, $add_pro=null, $nationalite=false)
{ 
    global $globals;

    if ($epouse) {
        $globals->xdb->execute("UPDATE auth_user_md5 SET epouse = {?} WHERE user_id = {?}", strtoupper($userax['epouse']), $userax['uid']);
    }
    
    if ($mobile) {
        $globals->xdb->execute("UPDATE auth_user_md5 SET mobile = {?} WHERE user_id = {?}", $userax['mobile'], $userax['uid']);
    }

    if ($nationalite) {
        if ($userax['nationalite'] == 'Français') {
            $userax['nationalite'] = 'FR';
        }
        $globals->xdb->execute("UPDATE auth_user_md5 SET nationalite = {?} WHERE user_id = {?}",  $userax['nationalite'], $userax['uid']);
    }
    if (is_array($del_address)) foreach($del_address as $adrid) {
        $globals->xdb->execute("DELETE FROM adresses WHERE uid = {?} AND adrid = {?}", $userax['uid'], $adrid);
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
            
            if ($adr['pays']) {
            
                $res = $globals->xdb->query(
                "SELECT a2 FROM geoloc_pays
                 WHERE pays LIKE {?} OR country LIKE {?}",
                 $adr['pays'], $adr['pays']);
                 
                $a2 = $res->fetchOneCell();
            }
            if (!$a2) { $a2 = '00'; }
            
            $globals->xdb->execute(
                "INSERT INTO adresses
                         SET uid = {?}, adrid = {?},
                             adr1 = {?}, adr2 = {?}, adr3 = {?},
                             cp = {?}, ville = {?},
                         pays = {?},
                         tel = {?}, fax = {?},
                         datemaj = NOW(),
                         pub = 'ax',
                         tel_pub = 'ax'",
                $userax['uid'], $new_adrid,
                $adr['adr1'], $adr['adr2'], $adr['adr3'],
                $adr['cp'], $adr['ville'],
                $a2,
                $adr['tel'], $adr['fax']);
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
            
            if ($pro['pays']) {
                $res = $globals->xdb->query(
                    "SELECT a2 FROM geoloc_pays
                      WHERE pays LIKE {?} OR country LIKE {?}",
                    $pro['pays'], $pro['pays']);
                $a2 = $res->fetchOneCell();
            }
            if (!$a2) { $a2 = '00'; }
            
            $globals->xdb->execute(
                "INSERT INTO entreprises
                         SET uid = {?}, entrid = {?},
                         entreprise = {?}, poste = {?},
                         adr1 = {?}, adr2 = {?}, adr3 = {?},
                         cp = {?}, ville = {?},
                         pays = {?},
                         tel = {?}, fax = {?},
                         visibilite = 'entreprise_ax,adr_ax,tel_ax'",
                $userax['uid'], $new_entrid,
                $pro['entreprise'], $pro['fonction'],
                $pro['adr1'], $pro['adr2'], $pro['adr3'],
                $pro['cp'], $pro['ville'],
                $a2,
                $pro['tel'], $pro['fax']);
        }
    }
}

function copy_from_ax($uid)
{
     $uax = get_user_ax($uid);
     import_from_ax($uax, true, true, null, array_keys($uax['adr']), null, array_keys($uax['adr_pro']), true); 
}

// vim:set et sw=4 sts=4 sws=4:
?>
