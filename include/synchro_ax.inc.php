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

function get_user_ax($uid, $raw=false) {
    require_once('webservices/manageurs.inc.php');

    require_once('xml-rpc-client.inc.php');

    require_once('manageurs.client.inc.php');

    global $globals;

    $res = $globals->xdb->query(
            "SELECT matricule_ax
              FROM auth_user_md5 AS u
             WHERE u.user_id = {?}", $uid);
    $matricule_ax = $res->fetchOneCell();

    $array = get_annuaire_infos(2, $matricule_ax, 0);
    
    $ancien = $array['dump']['ancien'];

    $userax = Array();

    $userax['uid'] = $uid;
    $userax['nom'] = $ancien[0];
    $userax['prenom'] = $ancien[4];
    $userax['epouse'] = ($ancien[2] != $ancien[0])?$ancien[2]:"";
    $userax['promo'] = $ancien[6];
    $userax['sexe'] = ($ancien[5] != 'M')?1:0;
    $userax['nationalite'] = $ancien[9];
    $userax['date'] = substr($ancien[12], 0, 10);
    $userax['mobile'] = $array['cell'];
    if ($ancien[13] == 'D' || $ancien[13] == 'Z')
        $userax['applis_join'] = "pas un corps";
    else
        $userax['applis_join'] = "Corps ".$ancien[13]." - ".$ancien[14];
    $userax['adr_pro'] = array();
    if (is_array($array['dump']['pro']))
    foreach ($array['dump']['pro'] as $job) {
        $jobax['entreprise'] = $job[1];
        $jobax['fonction'] = $job[6];
        $jobax['adr1'] = $job[10];
        $jobax['adr2'] = $job[11];
        $jobax['adr3'] = $job[12];
        $jobax['cp']   = $job[13];
        $jobax['ville'] = $job[14];
        $jobax['pays'] = $job[17];
        $jobax['tel']  = $job[19];
        $userax['adr_pro'][] = $jobax;
    }
    $userax['adr'] = array();
    if (is_array($array['dump']['adresse']))
    foreach ($array['dump']['adresse'] as $adr) {
        $adrax['adr1'] = $adr[3];
        $adrax['adr2'] = $adr[4];
        $adrax['adr3'] = $adr[5];
        $adrax['cp'] = $adr[6];
        $adrax['ville'] = $adr[7];
        $adrax['pays'] = $adr[10];
        $adrax['tel'] = $adr[12];
        $adrax['fax'] = $adr[13];
        $userax['adr'][] = $adrax;
    }
    if ($raw) $userax['raw'] = $array;

    return $userax;
}

function import_from_ax($userax, $epouse=false, $mobile=false, $del_address=null, $add_address=null, $del_pro=null, $add_pro=null) { 
    global $globals;

    if ($epouse) {
        $globals->xdb->execute("UPDATE auth_user_md5 SET epouse = {?} WHERE user_id = {?}", $userax['epouse'], $userax['uid']);
    }
    
    if ($mobile) {
        $globals->xdb->execute("UPDATE auth_user_md5 SET mobile = {?} WHERE user_id = {?}", $userax['mobile'], $userax['uid']);
    }

    if (is_array($del_address)) foreach($del_address as $adrid) {
        $globals->xdb->execute("DELETE FROM adresses WHERE uid = {?} AND adrid = {?}", $userax['uid'], $adrid);
    }

    if (is_array($add_address)) {

    $res = $globals->xdb->query("SELECT adrid FROM adresses WHERE uid = {?} ORDER BY adrid", $userax['uid']);
    $adrids = $res->fetchOneColumn();
    $i_adrid = 0;
    $new_adrid = 1;
    
    foreach($add_address as $adrid) {
    
        $adr = $userax['adr'][$adrid];

        // find the next adrid not used
        while ($adrids[$i_adrid] == $new_adrid) { $i_adrid++; $new_adrid++; }
        
        if ($adr['pays']) {
            $res = $globals->xdb->query("SELECT a2 FROM geoloc_pays WHERE pays LIKE {?} OR country LIKE {?}", $adr['pays'], $adr['pays']);
            $a2 = $res->fetchOneCell();
        }
        if (!$a2) $a2 = '00';
        
        $globals->xdb->execute(
            "INSERT INTO adresses
                     SET uid = {?}, adrid = {?},
                         adr1 = {?}, adr2 = {?}, adr3 = {?},
                         cp = {?}, ville = {?},
                         pays = {?},
                         datemaj = NOW(),
                         visibilite = 'adr_ax,tel_ax'",
                $userax['uid'], $new_adrid,
                $adr['adr1'], $adr['adr2'], $adr['adr3'],
                $adr['cp'], $adr['ville'],
                $a2);
    }}
}

// vim:set et sw=4 sts=4 sws=4:
?>
