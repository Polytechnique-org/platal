<?php
/***************************************************************************
 *  Copyright (C) 2003-2015 Polytechnique.org                              *
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

require_once('webservices/manageurs.inc.php');

$error_mat = "You didn't provide me with a valid matricule number...";
$error_key = "You didn't provide me with a valid cipher key...";

// The first parameter has to be the identification number (matricule).
// The second parameter facultative has to be the number of the desired address:
// -1 => we do not want any address;
// 0  => we want all the addresses;
// n  => we want the address number n.
//
// DO NOT CHANGE THE NAMES OF THE ADDRESSES' FIELDS.
// IF YOU HAVE TO MAKE SOME MODIFICATION, FIRST CONTACT:
// admin@manageurs.com
function get_annuaire_infos($method, $params) {
    global $error_mat, $error_key, $globals;

    // Password verification.
    if(!isset($params[0]) || ($params[0] != $globals->manageurs->manageurs_pass)) {
        return false;
    }
    // If address == -1, we do not retrieve any address.
    if(isset($params[2]) && ($params[2] == -1)) {
        unset($params[2]);
    }

    // We check we actually have an identification number.
    if(!empty($params[1])) {
        // We only retrieve addresses when required.
        if(!isset($params[2])) {
            $res = XDB::iterRow("SELECT  pp.display_tel AS cell, p.birthdate AS age
                                   FROM  profiles       AS p
                              LEFT JOIN  profile_phones AS pp ON (pp.pid = p.pid AND pp.link_type = 'user'
                                                                  AND pp.tel_type = 'mobile')
                                  WHERE  p.xorg_id = {?} LIMIT 1",
                                $params[1]);
            $array = $res->next();
        } else {
            $res = XDB::iterRow("SELECT  p.birthdate, pa.text, GROUP_CONCAT(pace3.short_name), GROUP_CONCAT(pace2.short_name),
                                         GROUP_CONCAT(pace1.short_name), p.pid, pa.id
                                   FROM  profiles                          AS p
                              LEFT JOIN  profile_addresses                 AS pa    ON (pa.pid = p.pid)
                              LEFT JOIN  profile_addresses_components      AS pc    ON (pa.pid = pc.pid AND pa.jobid = pc.jobid AND pa.groupid = pc.groupid
                                                                                        AND pa.type = pc.type AND pa.id = pc.id)
                              LEFT JOIN  profile_addresses_components_enum AS pace1 ON (FIND_IN_SET(\'country\', pace1.types) AND pace1.id = pc.component_id)
                              LEFT JOIN  profile_addresses_components_enum AS pace2 ON (FIND_IN_SET(\'locality\', pace2.types) AND pace2.id = pc.component_id)
                              LEFT JOIN  profile_addresses_components_enum AS pace3 ON (FIND_IN_SET(\'postal_code\', pace3.types) AND pace3.id = pc.component_id)
                                  WHERE  p.xorg_id = {?} AND NOT FIND_IN_SET('job', pa.flags)
                               ORDER BY  NOT FIND_IN_SET('current', pa.flags),
                                         FIND_IN_SET('secondary', pa.flags),
                                         NOT FIND_IN_SET('mail', pa.flags)
                               GROUP BY  pa.pid, pa.jobid, pa.groupid, pa.id, pa.type",
                                $params[1]);
            // Process the addresses we got.
            if(list($age, $text, $adr['cp'], $adr['ville'],
                    $adr['pays'], $pid, $adr['adrid']) = $res->next()) {
                list($adr['adr1'], $adr['adr2'], $adr['adr3']) =
                    explode("\n", Geocoder::getFirstLines($text, $adr['cp'], 3));
                $sql = XDB::query("SELECT  display_tel
                                     FROM  profile_phones
                                    WHERE  pid = {?} AND link_type = 'user' AND tel_type = 'mobile'
                                    LIMIT  1", $pid);
                if ($sql->numRows() > 0) {
                    $array['cell'] = $sql->fetchOneCell();
                } else {
                    $array['cell'] ='';
                }
                $array['age']       = $age;
                $array['adresse'][] = $adr;


                // We limit the address number by the number of available addresses.
                $adresse = min((int) $params[2], $res->total());

                if ($adresse != 1) { // We don't want the first address.
                    $i = 2;
                    while(list($age, $text, $adr['cp'], $adr['ville'],
                                $adr['pays'], , $adr['adrid']) = $res->next()) {
                        list($adr['adr1'], $adr['adr2'], $adr['adr3']) =
                            explode("\n", Geocoder::getFirstLines($text, $adr['cp'], 3));
                        if ($adresse == $i) {
                            // If we want this particular address.
                            $array['adresse'][0] = $adr;
                            //$res->free();
                            break;
                        } elseif ($adresse == 0) {
                            // If we want every address.
                            $array['adresse'][] = $adr;
                        }
                        $i++;
                    }
                }

                // We add the phone numbers.
                $adrid_index = array();
                foreach ($array['adresse'] as $i => $a) {
                    $adrid_index[$a['adrid']] = $i;
                }
                $restel = XDB::iterator("SELECT  pp.display_tel AS tel, pp..tel_type, pp.link_id as adrid
                                           FROM  profile_phones    AS pp
                                     INNER JOIN  profile_addresses AS pa ON (pp.link_id = pa.id AND pp.pid = pa.pid)
                                          WHERE  pp.pid = {?} AND pp.link_type = 'address'
                                                 AND NOT FIND_IN_SET('pro', pa.statut)",
                                        $pid);
                while ($tel = $restel->next()) {
                    $array['adresse'][$adrid_index[$tel['adrid']]]['tels'][] = $tel;
                }
                foreach ($array['adresse'] as $i => $adr) {
                    unset($lasttel);
                    foreach ($adr['tels'] as $j => $t) {
                        if (!isset($array['adresse'][$i]['tel']) && (strpos($t['tel_type'], 'Tél') === 0)) {
                            $array['adresse'][$i]['tel'] = $t['tel'];
                        } elseif (!isset($array['adresse'][$i]['fax'])
                                  && (strpos($t['tel_type'], 'Fax') === 0)) {
                            $array['adresse'][$i]['fax'] = $t['tel'];
                        } else {
                            $lasttel = $t['tel'];
                        }
                        if (isset($array['adresse'][$i]['tel']) && isset($array['adresse'][$i]['fax'])) {
                            break;
                        }
                    }
                    if (!isset($array['adresse'][$i]['tel']) && isset($lasttel)) {
                        $array['adresse'][$i]['tel'] = $lasttel;
                    } elseif (!isset($array['adresse'][$i]['fax']) && isset($lasttel)) {
                        $array['adresse'][$i]['fax'] = $lasttel;
                    }
                    unset($array['adresse'][$i]['adrid']);
                    unset($array['adresse'][$i]['tels']);
                }
            } else {
                $array = false;
            }
        }

        if ($array) { // We did get a result: the identification number was rigth.

            // We only send the age to manageurs.com; the format is YYYY-MM-DD 0123-56-89.
            $year  = (int) substr($array['age'], 0, 4);
            $month = (int) substr($array['age'], 5, 2);
            $day   = (int) substr($array['age'], 8, 2);
            $age   = (int) date('Y') - $year - 1;
            if(( $month < (int)date('m')) ||
                    (($month == (int)date('m')) && ($day >= (int)date('d')))) {
                $age += 1;
            }
            $array['age'] = $age;

            // We start the encryption of the data.
            if (manageurs_encrypt_init($params[1]) == 1) {
                // We did not find the key to encryptthe data.
                $args  = array("erreur" => 3, "erreurstring" => $error_key);
                $reply = xmlrpc_encode_request(NULL,$args);
            } else {
                $reply = manageurs_encrypt_array($array);
                manageurs_encrypt_close();
            }
        } else {
            // The identification number was not valid.
            $args  = array("erreur" => 2, "erreurstring" => $erreur_mat);
            $reply = xmlrpc_encode_request(NULL,$args);
        }
    } else {
        // The identification number was not in argument.
        $args  = array("erreur" => 1, "erreurstring" => $error_mat);
        $reply = xmlrpc_encode_request(NULL, $args);
    }

    return $reply;
}

function get_nouveau_infos($method, $params) {
    global $error_mat, $error_key, $globals;
    // Password verification.
    if(!isset($params[0]) || ($params[0] != $globals->manageurs->manageurs_pass)) {
        return false;
    }
    // We check we actually have an identification number.
    if(!empty($params[1])) {
        $res = XDB::query("SELECT  ppn.lastname_initial AS nom, ppn.lastname_ordinary AS nom_usage, ppn.firstname_initial AS prenom,
                                   p.sex = 'female' AS femme, p.deathdate IS NOT NULL AS decede,
                                   p.birthdate, pd.promo, CONCAT(e.email, '@', d.name) AS mail
                             FROM  profiles         AS p
                       INNER JOIN  account_profiles AS ap ON (p.pid = ap.pid AND FIND_IN_SET('owner', perms)
                       INNER JOIN  email_source_account AS s ON (s.uid = ap.uid AND FIND_IN_SET('bestalias', s.flags))
                       INNER JOIN  email_virtual_domains AS d ON (s.domain = s.id)
                       INNER JOIN  profile_display  AS pd PN (p.pid = pd.pid)
                       INNER JOIN  profile_public_names AS ppn ON (ppn.pid = p.pid)
                            WHERE  a.flags = 'bestalias' AND p.xorg_id = {?}",
                          $params[1]);
        // $data['mail'] .= '@polytechnique.org';


        // We start the encryption of the data.
        if (manageurs_encrypt_init($params[1]) == 1) {
            // We did not find the key to encryptthe data.
            $args  = array("erreur" => 3, "erreurstring" => $error_key);
            $reply = xmlrpc_encode_request(NULL, $args);
        } else {
            $reply = manageurs_encrypt_array($data);
            manageurs_encrypt_close();
        }

    } else {
        $reply = false;
    }
    return $reply;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
