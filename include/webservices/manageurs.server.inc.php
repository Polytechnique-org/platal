<?php

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
    require "geocoding.inc.php";
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
            $res = XDB::iterRow(
                    "SELECT  ph.display_tel  AS cell, a.naissance AS age
                       FROM  auth_user_md5   AS a
                 INNER JOIN  auth_user_quick AS q USING (user_id)
                  LEFT JOIN  profile_phones  AS ph ON (ph.pid = a.user_id AND link_type='user' AND tel_type = 'mobile')
                      WHERE  a.matricule = {?} LIMIT 1", $params[1]);
            $array = $res->next();
        } else {
            $res = XDB::iterRow(
                    "SELECT  a.naissance, addr.text, addr.postalCode,
                             l.name, addr.countryId, addr.pid, addr.id
                       FROM  auth_user_md5     AS a
                 INNER JOIN  auth_user_quick   AS q    USING (user_id)
                  LEFT JOIN  profile_addresses AS addr ON(adr.pid = a.user_id)
                  LEFT JOIN  geoloc_localities AS l    ON (l.id = addr.localityId)
                      WHERE  a.matricule = {?} AND
                             NOT FIND_IN_SET('pro', adr.statut)
                   ORDER BY  NOT FIND_IN_SET('active', adr.statut),
                             FIND_IN_SET('res-secondaire', adr.statut),
                             NOT FIND_IN_SET('courrier', adr.statut)", $params[1]);
            // Process the addresses we got.
            if(list($age, $text, $adr['cp'], $adr['ville'],
                    $adr['pays'], $uid, $adr['adrid']) = $res->next()) {
                list($adr['adr1'], $adr['adr2'], $adr['adr3']) =
                    explode("\n", Geocoder::getFirstLines($text, $adr['cp'], 3));
                $sql = XDB::query("SELECT  display_tel
                                     FROM  profile_phones
                                    WHERE  pid = {?} AND link_type = 'user' AND tel_type = 'mobile'
                                    LIMIT  1", $uid);
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
                $restel = XDB::iterator(
                           "SELECT  t.display_tel AS tel, t.tel_type, t.link_id as adrid
                              FROM  profile_phones    AS t
                        INNER JOIN  profile_addresses AS a ON (t.link_id = a.id AND t.uid = a.pid)
                             WHERE  t.pid = {?} AND t.link_type = 'address'
                                    AND NOT FIND_IN_SET('pro', a.statut)", $uid);
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
        $res = XDB::query(
                "SELECT  a.nom, a.nom_usage,a.prenom, FIND_IN_SET('femme', a.flags) as femme,
                         a.deces!= 0 as decede, a.naissance, a.promo, concat(al.alias, '@m4x.org') as mail
                   FROM  auth_user_md5 AS a
             INNER JOIN  aliases       AS al ON (a.user_id = al.uid)
                  WHERE  al.flags = 'bestalias' AND a.matricule = {?}",$params[1]);
        $data=$res->fetchOneAssoc();
        //$data['mail'].='@polytechnique.org';


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

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
