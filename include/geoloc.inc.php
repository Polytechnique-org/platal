<?php
/***************************************************************************
 *  Copyright (C) 2003-2009 Polytechnique.org                              *
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

// {{{ geoloc_country($current, $avail_only = false)
/** donne la liste déroulante des pays
 * @param $current pays actuellement selectionné
 */
function geoloc_country($current, $avail_only = false)
{
    if ($avail_only) {
        $res = XDB::iterRow('SELECT  g.a2, g.pays
                               FROM  geoloc_pays AS g
                         INNER JOIN  adresses    AS a ON(a.country = g.a2)
                           GROUP BY  g.a2
                           ORDER BY  g.pays');
    } else {
        $res = XDB::iterRow('SELECT a2,pays FROM geoloc_pays ORDER BY pays');
    }
    $html = "";
    while (list($my_id, $my_pays) = $res->next()) {
        $html .= sprintf("<option value=\"%s\" %s>%s</option>\n",
                         $my_id, ($current==$my_id?"selected='selected'":""), $my_pays);
    }
    return $html;
}

// }}}
// {{{ geoloc_region($country, $current, $avail_only = false)
/** donne la liste deroulante des regions pour un pays
 * @param $pays le pays dont on veut afficher les regions
 * @param $current la region actuellement selectionnee
 */
function geoloc_region($country, $current, $avail_only = false)
{
    if ($avail_only) {
        $res = XDB::iterRow('SELECT  r.region, r.name
                               FROM  geoloc_region AS r
                         INNER JOIN  adresses      AS a ON (a.country = r.a2 AND a.region = r.region)
                              WHERE  r.a2 = {?}
                           GROUP BY  r.region
                           ORDER BY  r.name', $country);
    } else {
        $res = XDB::iterRow('SELECT  region,name
                               FROM  geoloc_region
                              WHERE  a2 = {?}
                           ORDER BY  name', $country);
    }
    $html = "<option value=\"\"></option>";
    while (list($regid, $regname) = $res->next()) {
        $html .= sprintf("<option value=\"%s\" %s>%s</option>\n",
                 $regid, ($current==$regid?"selected='selected'":""), $regname);
    }
    return $html;
}
// }}}
// {{{ get_cities_maps($array)
/* get all the maps id of the cities contained in an array */
function get_cities_maps($array)
{
    global $globals;
    implode("\n",$array);
    $url = $globals->geoloc->webservice_url."findMaps.php?datatext=".urlencode(implode("\n", $array));
    if (!($f = @fopen($url, 'r'))) return false;
    $maps = array();
    while (!feof($f))
    {
        $l = trim(fgets($f));
        $tab = explode(';', $l);
        $i = $tab[0];
        unset($tab[0]);
        $maps[$i] = $tab;
    }
    return $maps;
}
// }}}
// {{{ get_new_maps($url)
/** set new maps from url **/
function get_new_maps($url)
{
    if (!($f = @fopen($url, 'r'))) {
        return false;
    }
    XDB::query('TRUNCATE TABLE geoloc_maps');
    $s = '';
    while (!feof($f)) {
        $l = fgetcsv($f, 1024, ';', '"');
        foreach ($l as $i => $val) {
            if ($val != 'NULL') {
                $l[$i] = '\''.addslashes($val).'\'';
            }
        }
        $s .= ',('.implode(',',$l).')';
    }
    XDB::execute('INSERT INTO geoloc_maps VALUES '.substr($s, 1));
    return true;
}
// }}}
// {{{ geolocGoogle (array $address)
// retrieve the infos on a text address

function geolocGoogle (array &$address)
{
    /* keys
     * www.polytechnique.org:
     * ABQIAAAAIlFNe1A494mwR9Zf4R3t0xRsw9kzQBeaENRP66lRw7Ru3uVJcRR73lY1tmAdYGqw-pyHTdynmicz0w
     * www.polytechnique.net and dev.polytechnique.net:
     * ABQIAAAAIlFNe1A494mwR9Zf4R3t0xT8SmDPc83znji5QwIVTgAvxgX5zRRMagHx_rmGeQF5SnCzmyqiSeSAxA
     * dev.m4x.org:
     * ABQIAAAAIlFNe1A494mwR9Zf4R3t0xQ31muaRX97DHXrOFfMwMMCxEnhaxQIPDe9Ct3D6ZvWuGiWllkGAP3IqA
     *
     * Documentation:
     * http://code.google.com/intl/fr/apis/maps/documentation/geocoding/
     * http://code.google.com/apis/maps/documentation/reference.html#GGeoAddressAccuracy */

    $success    = true;
    $key        = 'ABQIAAAAIlFNe1A494mwR9Zf4R3t0xQ31muaRX97DHXrOFfMwMMCxEnhaxQIPDe9Ct3D6ZvWuGiWllkGAP3IqA';
    $webservice = "http://maps.google.com/maps/geo?";
    $baseurl    = $webservice . "&key=$key" . "&sensor=false&output=json&oe=utf8&gl=fr&hl=fr&q=";

    $url = $baseurl . urlencode($address['text']);
    if (!geolocalizeAddress($url, $gAddress)) {
        $addressLines = explode("\n", $address['text']);
        $nbLines      = count($addressLines);
        $currentState = array();
        $success      = false;
        for ($i = 1; !$success && ($i < $nbLines); $i++) {
            for ($j = 0; $j < $i; $j++) {
                $currentState[$j] = 0;
            }
            while($j < $nbLines) {
                $currentState[$j] = 1;
                $j++;
            }
            do {
                $partialAddress = "";
                for ($j = 0; $j < $nbLines; $j++) {
                    if ($currentState[$j] == 1) {
                        $partialAddress .= $addressLines[$j] . " ";
                    }
                }
                $url     = $baseurl . urlencode(trim($partialAddress));
                $success = geolocalizeAddress($url, $gAddress);
            } while (!$success && nextCurrentState($currentState, $nbLines));
        }
        if ($success) {
            $extras = "";
            for ($i = 0; $i < $nbLines; $i++) {
                if ($currentState[$i] == 0) {
                    $extras .= $addressLines[$i] . ", ";
                }
            }
            trim($extras, ", ");
            $address['extras'] = $extras;
        }
    }
    if ($success) {
        fillAddress($address, $gAddress);
        formatAddress($address);
    }
    return $success;
}

// }}}
// {{{ nextCurrentState(&$currentState, $nbLines)

function nextCurrentState(&$currentState, $nbLines)
{
    $lastOne = 0;
    $nbZeros = 2;
    for ($i = 0; $i < $nbLines; $i++) {
        if ($currentState[$i] == 1) {
            $lastOne = $i;
            $nbZeros = 2;
        } else {
            $nbZeros++;
        }
    }
    if ($lastOne == 0) {
        return false;
    } elseif ($currentState[$lastOne - 1] == 0) {
        $currentState[$lastOne - 1] = 1;
        $currentState[$lastOne]     = 0;
        return true;
    } else {
        $lastZero = -1;
        for ($j = 0; $j < $lastOne; $j++) {
            if ($currentState[$j] == 0) {
                $lastZero = $j;
            }
        }
        if ($lastZero == -1) {
            return false;
        } else {
            $currentState[$lastZero] = 1;
            for ($k = $lastZero + 1; $k < $lastZero + $nbZeros; $k++) {
                $currentState[$k] = 0;
            }
            for ($k = $lastZero + $nbZeros; $k < $nbLines; $k++) {
                $currentState[$k] = 1;
            }
            return true;
        }
    }
}

// }}}
// {{{ geolocalizeAddress ($url, &$result)

function geolocalizeAddress ($url, &$result = array())
{
    global $globals;

    if ($globals->debug & DEBUG_BT) {
        if (!isset(PlBacktrace::$bt['Geoloc'])) {
            new PlBacktrace('Geoloc');
        }
        PlBacktrace::$bt['Geoloc']->start($url);
    }

    if ($f = file_get_contents($url, 'r')) {
        $data = json_decode($f, true);
        if ($globals->debug & DEBUG_BT) {
            PlBacktrace::$bt['Geoloc']->stop(count($data), null, $data);
        }
        if ($data['Status']['code'] != 200) {
            return false;
        }
        $nbResults = count($data['Placemark']);
        $idAccuracy   = 0;
        if ($nbResults > 1) {
            $bestAccuracy = $data['Placemark'][0]['AddressDetails']['Accuracy'];
            for ($i = 1; $i < $nbResults; $i++) {
                if ($data['Placemark'][$i]['AddressDetails']['Accuracy'] > $bestAccuracy) {
                    unset($data['Placemark'][$idAccuracy]);
                    $bestAccuracy = $data['Placemark'][$i]['AddressDetails']['Accuracy'];
                    $idAccuracy   = $i;
                } else {
                    unset($data['Placemark'][$i]);
                }
            }
        }
        $result = $data['Placemark'][$idAccuracy];
        return true;
    }
    if ($globals->debug & DEBUG_BT) {
        PlBacktrace::$bt['Geoloc']->stop(0, "Can't fetch result.");
    }
    return false;
}

// }}}
// {{{ fillAddress(array &$address, $gAddress)

function fillAddress(array &$address, array $gAddress)
{
    // An address is Country -> AdministrativeArea -> SubAdministrativeArea -> Locality -> Thoroughfare
    // with all the shortcuts possible

    // postalText
    $address['geoloc'] = str_replace(", ", "\n", $gAddress['address']);
    if (isset($gAddress['AddressDetails']['Accuracy'])) {
        $address['accuracy'] = $gAddress['AddressDetails']['Accuracy'];
    }
    $currentPosition = $gAddress['AddressDetails'];
    if (isset($currentPosition['Country'])) {
        $currentPosition      = $currentPosition['Country'];
        $address['countryId'] = $currentPosition['CountryNameCode'];
        $address['country']   = $currentPosition['CountryName'];
    }
    if (isset($currentPosition['AdministrativeArea'])) {
        $currentPosition                   = $currentPosition['AdministrativeArea'];
        $address['administrativeAreaName'] = $currentPosition['AdministrativeAreaName'];
    }
    if (isset($currentPosition['SubAdministrativeArea'])) {
        $currentPosition                      = $currentPosition['SubAdministrativeArea'];
        $address['subAdministrativeAreaName'] = $currentPosition['SubAdministrativeAreaName'];
    }
    if (isset($currentPosition['Locality'])) {
        $currentPosition          = $currentPosition['Locality'];
        $address['localityName']  = $currentPosition['LocalityName'];
    }
    if (isset($currentPosition['Thoroughfare'])) {
        $address['thoroughfareName'] = $currentPosition['Thoroughfare']['ThoroughfareName'];
    }
    if (isset($currentPosition['PostalCode'])) {
        $address['postalCode'] = $currentPosition['PostalCode']['PostalCodeNumber'];
    }

    // Coordinates
    if (isset($gAddress['Point']['coordinates'][0])) {
        $address['latitude'] = $gAddress['Point']['coordinates'][0];
    }
    if (isset($gAddress['Point']['coordinates'][1])) {
        $address['longitude'] = $gAddress['Point']['coordinates'][1];
    }
    if (isset($gAddress['ExtendedData']['LatLonBox']['north'])) {
        $address['north'] = $gAddress['ExtendedData']['LatLonBox']['north'];
    }
    if (isset($gAddress['ExtendedData']['LatLonBox']['south'])) {
        $address['south'] = $gAddress['ExtendedData']['LatLonBox']['south'];
    }
    if (isset($gAddress['ExtendedData']['LatLonBox']['east'])) {
        $address['east'] = $gAddress['ExtendedData']['LatLonBox']['east'];
    }
    if (isset($gAddress['ExtendedData']['LatLonBox']['west'])) {
        $address['west'] = $gAddress['ExtendedData']['LatLonBox']['west'];
    }
}

// }}}
// {{{ formatAddress(array &$address)

function formatAddress(array &$address)
{
    $same = true;
    $text   = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                      array("", "\n"), $address['text']));
    $geoloc = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                      array("", "\n"), $address['geoloc']));
    if (isset($address['extras'])) {
        $extras = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"),
                                          array("", "\n"), $address['extras']));
        $geoloc = $extras . $geoloc;
        unset($address['extras']);
    }

    $arrayText   = explode("\n", $text);
    $arrayGeoloc = explode("\n", $geoloc);
    $nbText   = count($arrayText);
    $nbGeoloc = count($arrayGeoloc);

    if ((($nbText > $nbGeoloc) || ($nbText < $nbGeoloc - 1))
        || (($nbText == $nbGeoloc - 1) && ($arrayText[$nbText - 1] == strtoupper($address['country'])))) {
        $same = false;
    } else {
        foreach ($arrayText as $i => $lignText) {
            if (levenshtein($lignText, trim($arrayGeoloc[$i])) > 3) {
                $same = false;
            }
        }
    }
    if ($same) {
        $address['text'] = $address['geoloc'];
        unset($address['geoloc']);
    }
}

// }}}
// {{{ cleanText(&$text)

function cleanText(&$text)
{
    $lines = explode("\n", $text);
    $n =  count($lines);
    $text = "";
    for ($i = 0; $i < $n; $i++) {
        if (trim($lines[$i])) {
            $text .= trim($lines[$i]) . "\n";
        }
    }
    $text = trim($text);
}

// }}}
// {{{ getAreaId(array &$address, $area)

function getAreaId(array &$address, $area)
{
    if (isset($address[$area . 'Name'])) {
        $res = XDB::query("SELECT  id
                             FROM  geoloc_" . $area . "
                            WHERE  name = {?}",
                          $address[$area . 'Name']);
        if ($res->numRows() == 0) {
            $address[$area . 'Id'] = XDB::execute("INSERT INTO  geoloc_" . $area . " (name, country)
                                                        VALUES  ({?}, {?})",
                                                  $address[$area . 'Name'], $address['countryId']);
        } else {
            $address[$area . 'Id'] = $res->fetchOneCell();
        }
    }
}

// }}}
// {{{ get_address_text($adr)
/** make the text of an address that can be read by a mailman
 * @param $adr an array with all the usual fields
 */
function get_address_text($adr)
{
    $t = "";
    if (isset($adr['adr1']) && $adr['adr1']) $t.= $adr['adr1'];
    if (isset($adr['adr2']) && $adr['adr2']) $t.= "\n".$adr['adr2'];
    if (isset($adr['adr3']) && $adr['adr3']) $t.= "\n".$adr['adr3'];
    $l = "";
    if (isset($adr['display']) && $adr['display']) {
        $keys = explode(' ', $adr['display']);
        foreach ($keys as $key) {
            if (isset($adr[$key])) {
                $l .= " ".$adr[$key];
            } else {
                $l .= " ".$key;
            }
        }
        if ($l) substr($l, 1);
    } elseif ($adr['country'] == 'US' || $adr['country'] == 'CA' || $adr['country'] == 'GB') {
        if ($adr['city']) $l .= $adr['city'].",\n";
        if ($adr['region']) $l .= $adr['region']." ";
        if ($adr['postcode']) $l .= $adr['postcode'];
    } else {
        if (isset($adr['postcode']) && $adr['postcode']) $l .= $adr['postcode']." ";
        if (isset($adr['city']) && $adr['city']) $l .= $adr['city'];
    }
    if ($l) $t .= "\n".trim($l);
    if ($adr['country'] != '00' && (!$adr['countrytxt'] || $adr['countrytxt'] == strtoupper($adr['countrytxt']))) {
        $res = XDB::query("SELECT pays FROM geoloc_pays WHERE a2 = {?}", $adr['country']);
        $adr['countrytxt'] = $res->fetchOneCell();
    }
    if (isset($adr['countrytxt']) && $adr['countrytxt']) {
        $t .= "\n".$adr['countrytxt'];
    }
    return trim($t);
}
// }}}
// {{{ compare_addresses_text($a, $b)
/** compares if two address matches
 * @param $a the raw text of an address
 * @param $b the raw text of a complete valid address
 */
function compare_addresses_text($a, $b)
{
    $ta = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"), array("", "\n"), $a));
    $tb = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"), array("", "\n"), $b));

    $la = explode("\n", $ta);
    $lb = explode("\n", $tb);

    if (count($lb) > count($la) + 1) {
        return false;
    }
    foreach ($la as $i => $l) {
        if (levenshtein(trim($l), trim($lb[$i])) > 3) {
            return false;
        }
    }
    return true;
}

// }}}
// {{{ fixNumber($oldtext, &$new)

function fixNumber($oldtext, &$new)
{
    $ThoroughfareName = $new['AddressDetails']['Country']['AdministrativeArea']['SubAdministrativeArea']['Locality']['Thoroughfare']['ThoroughfareName'];
    $ThoroughfareName = trim(strtoupper(preg_replace(array("/[,\"'#~:;_\-]/", "/\r\n/"),
                                                     array("", "\n"), $ThoroughfareName)));
    $oldarray = explode("\n", trim(strtoupper(preg_replace(array("/[,\"'#~:;_\-]/", "/\r\n/"),
                                                           array("", "\n"), $oldtext))));
    $mindist = strlen($ThoroughfareName);
    $minpos  = 0;
    foreach ($oldarray as $i => $oldline) {
        if (($l = levenshtein(trim($oldline), $ThoroughfareName)) < $mindist) {
            $mindist = $l;
            $minpos  = $i;
        }
    }
    $nb = explode(" ", $oldarray[$minpos]);
    $new['text'] = $nb[0] . " " . $new['text'];
}

// }}}
// {{{ localize_addresses($uid)
/* localize all the address of a user and modify the database
 * if the new address match with the old one
 * @param $uid the id of the user
 */
function localize_addresses($uid)
{
    $res = XDB::iterator("SELECT  *
                            FROM  adresses
                           WHERE  uid = {?} and (cityid IS NULL OR cityid = 0)", $uid);
    $erreur = Array();

    while ($a = $res->next()) {
        $new = get_address_infos($ta = get_address_text($a));
        if (compare_addresses_text($ta, get_address_text($new))) {
            XDB::execute("UPDATE  adresses
                             SET  adr1 = {?}, adr2 = {?}, adr3 = {?},
                                  cityid = {?}, city = {?}, postcode = {?},
                                  region = {?}, regiontxt = {?}, country = {?},
                                  glat = {?}, glng = {?}
                           WHERE  uid = {?} AND adrid = {?}",
                          $new['adr1'], $new['adr2'], $new['adr3'],
                          $new['cityid'], $new['city'], $new['postcode'],
                          $new['region'], $new['regiontxt'], $new['country'],
                          $new['precise_lat'], $new['precise_lon'],
                          $uid, $a['adrid']);
            $new['store'] = true;
            if (!$new['cityid']) {
                $erreur[$a['adrid']] = $new;
            }
        } else {
            $new['store'] = false;
            $erreur[$a['adrid']] = $new;
        }
    }
    return $erreur;
}
// }}}
// {{{ get_address_infos($txt)
/** retrieve the infos on a text address
 * store on the fly the info of the city concerned
 * @param $txt the raw text of an address
 */

function get_address_infos($txt)
{
    global $globals;

    $url = $globals->geoloc->webservice_url."address.php?precise=1&txt=" . urlencode($txt);
    if ($globals->debug & DEBUG_BT) {
        if (!isset(PlBacktrace::$bt['Geoloc'])) {
            new PlBacktrace('Geoloc');
        }
        PlBacktrace::$bt['Geoloc']->start($url);
    }
    $f = @fopen($url, 'r');
    if ($f === false) {
        if ($globals->debug & DEBUG_BT) {
            PlBacktrace::$bt['Geoloc']->stop(0, 'Can\'t fetch result');
        }
        return false;
    }
    $keys = explode('|',fgets($f));
    $vals = explode('|',fgets($f));
    if ($globals->debug & DEBUG_BT) {
        $data = array();
        for ($i = 0 ; $i < count($keys) ; ++$i) {
            $data[] = array($keys[$i], $vals[$i]);
        }
        PlBacktrace::$bt['Geoloc']->stop(count($keys), null, $data);
    }
    $infos = empty_address();
    foreach ($keys as $i=>$key) {
        if($vals[$i]) {
            if ($key == 'sql') {
                $infos[$key] = $vals[$i];
            } else {
                $val = strtr($vals[$i], array(chr(197).chr(147) => "&oelig;"));
                $infos[$key] = $val;
            }
        }
    }
    if (empty($infos['country'])) {
        $infos['country'] = '00';
    }
    if (isset($infos['sql']) && $infos['sql']) {
        $sql = explode(', ', trim($infos['sql'], '()'));
        if (count($sql) == 16) {
            for ($i = 0 ; $i < 16 ; ++$i) {
                $sql[$i] = stripslashes(trim($sql[$i], ' \''));
            }
            XDB::execute("REPLACE INTO  geoloc_city
                                VALUES  ({?}, {?}, {?}, {?}, {?}, {?},
                                         {?}, {?}, {?}, {?}, {?}, {?},
                                         {?}, {?}, {?}, {?})",
                         $sql[0], $sql[1], $sql[2], $sql[3], $sql[4], $sql[5],
                         $sql[6], $sql[7], $sql[8], $sql[9], $sql[10], $sql[11],
                         $sql[12], $sql[13], $sql[14], $sql[15]);
        }
    }
    if (isset($infos['display']) && $infos['display'])
        XDB::execute("UPDATE  geoloc_pays
                         SET  display = {?}
                       WHERE  a2 = {?}", $infos['display'], $infos['country']);
    if (isset($infos['cityid'])) {
        fix_cities_not_on_map(1, $infos['cityid']);
        if (floatval($infos['precise_lat']) && floatval($infos['precise_lon'])) {
            $res = XDB::query("SELECT  c.lat / 100000, c.lon / 100000
                                 FROM  geoloc_city AS c
                                WHERE  c.id = {?}", $infos['cityid']);
            if ($res->numRows()) {
                list($glat, $glng) = $res->fetchOneRow();
                $infos['precise_lat'] = $glat;
                $infos['precise_lon'] = $glng;
            }
        }
    }
    return $infos;
}

// }}}
// {{{ synchro_city($id)
/** synchronise the local geoloc_city base to geoloc.org
 * @param $id the id of the city to synchronize
 */
function synchro_city($id)
{
    global $globals;
    $url = $globals->geoloc->webservice_url."cityFinder.php?method=id&id=".$id."&out=sql";
    if (!($f = @fopen($url, 'r'))) {
        return false;
    }
    $s = fgets($f);
    if ($s) {
        return XDB::execute("REPLACE INTO geoloc_city VALUES ".$s) > 0;
    }
}
 // }}}
// {{{ function fix_cities_not_on_map($limit)
function fix_cities_not_on_map($limit=false, $cityid=false)
{
    $missing = XDB::query("SELECT  c.id
                             FROM  geoloc_city AS c
                        LEFT JOIN  geoloc_city_in_maps AS m ON(c.id = m.city_id)
                            WHERE  m.city_id IS NULL"
                            . ($cityid ? " AND c.id = '" . $cityid . "'" : "" )
                            . ($limit ?  " LIMIT $limit" : "" ));
    $maps = get_cities_maps($missing->fetchColumn());
    if ($maps) {
        $values = "";
        foreach ($maps as $cityid => $maps_c) {
            foreach ($maps_c as $map_id) {
                $values .= ",($cityid, $map_id, '')";
            }
        }
        if (strlen($values) > 1) {
            XDB::execute("REPLACE INTO  geoloc_city_in_maps
                                VALUES  ".substr($values, 1));
        }
    } else {
        return false;
    }
    return true;
}

function set_smallest_levels()
{
    $maxlengths = XDB::iterRow("SELECT  MAX(LENGTH(gm.path)), gcim.city_id
                                  FROM  geoloc_city_in_maps AS gcim
                            INNER JOIN  geoloc_maps AS gm USING ( map_id )
                              GROUP BY  gcim.city_id");
    while (list($length, $id) = $maxlengths->next()) {
        XDB::execute("UPDATE  geoloc_city_in_maps AS gcim
                  INNER JOIN  geoloc_maps AS gm USING(map_id)
                         SET  gcim.infos = IF(LENGTH(gm.path) = {?}, 'smallest', '')
                       WHERE  gcim.city_id = {?}", $length, $id);
    }
    return true;
}
// }}}

function geoloc_to_x($lon, $lat)
{
    return deg2rad(1) * $lon *100;
}

function geoloc_to_y($lon, $lat)
{
    if ($lat < -75) {
        return latToY(-75);
    }
    if ($lat > 75) {
        return latToY(75);
    }
    return -100 * log(tan(pi()/4 + deg2rad(1)/2*$lat));
}

function size_of_city($nb)
{
    $s = round(log($nb + 1)*2,2);
    if ($s < 1) {
        return 1;
    }
    return $s;
}

function size_of_territory($nb)
{
    return size_of_city($nb);
}

function geoloc_getData_subcities($mapid, $SFields, &$cities, $direct=true)
{
    if ($SFields instanceof UserSet) {
        $set = $SFields;
        $SFields = array();
    } else {
        $set = new UserSet();
    }
    for ($i_mapfield=0; $i_mapfield < count($SFields) ; $i_mapfield++) {
        if ($SFields[$i_mapfield]->fieldFormName == 'mapid') {
            break;
        }
    }
    $SFields[$i_mapfield] = new MapSField('mapid',
                                          array('gcim.map_id'),
                                          array('adresses','geoloc_city_in_maps'),
                                          array('am','gcim'),
                                          array(getadr_join('am'), 'am.cityid = gcim.city_id'),
                                          $mapid);
    $fields = new SFieldGroup(true, $SFields);
    $where = $fields->get_where_statement();
    $joins = $fields->get_select_statement();
    if ($where) {
        $where .= ' AND ';
    }
    $cityres = $set->get('gc.id,
                          gc.lon / 100000 AS x, gc.lat/100000 AS y,
                          gc.name,
                          COUNT(u.user_id) AS pop,
                          SUM(u.promo % 2) AS yellow',
                          "$joins
                          LEFT JOIN  geoloc_city  AS gc ON(gcim.city_id = gc.id)",
                          $where . ($direct ? "gcim.infos = 'smallest'" : '1'),
                          'gc.id, gc.alias',
                          'pop DESC');
    foreach($cityres as $c) {
        if ($c['pop'] > 0) {
            $city = $c;
            $city['x'] = geoloc_to_x($c['x'], $c['y']);
            $city['y'] = geoloc_to_y($c['x'], $c['y']);
            $city['size'] = size_of_city($c['pop']);
            $cities[$c['id']] = $city;
        }
    }
}

function geoloc_getData_subcountries($mapid, $sin, $minentities)
{
    $countries = array();
    $cities = array();

    if ($mapid === false) {
        $wheremapid = "WHERE gm.parent IS NULL";
    } else {
        $wheremapid = "WHERE   gm.parent = {?}";
    }
    $submapres = XDB::iterator(
        "SELECT  gm.map_id AS id, gm.name, gm.x, gm.y, gm.xclip, gm.yclip,
                 gm.width, gm.height, gm.scale, 1 AS rat
           FROM  geoloc_maps AS gm
        ". $wheremapid, Env::v('mapid',''));

    global $globals;

    while ($c = $submapres->next()) {
        $country = $c;
        $country['color'] = 0xFFFFFF;
        $country['swf'] = $globals->geoloc->webservice_url."maps/mercator/map_".$c['id'].".swf";
        $countries[$c['id']] = $country;
    }

    if ($mapid === false) {
        return array($countries, $cities);
    }

    geoloc_getData_subcities(Env::i('mapid'), $sin, $cities);
    $nbcities = count($cities);
    $nocity = $nbcities == 0;
    if ($sin instanceof UserSet) {
        $set = $sin;
        $SFields = array();
    } else {
        $set = new UserSet();
        $SFields = $sin;
    }

    for ($i_mapfield=0; $i_mapfield < count($SFields) ; $i_mapfield++) {
        if ($SFields[$i_mapfield]->fieldFormName == 'mapid') {
            break;
        }
    }
    $SFields[$i_mapfield] = new MapSField('mapid',
                                          array('map.parent'),
                                          array('adresses','geoloc_city_in_maps','geoloc_maps'),
                                          array('am','gcim','map'),
                                          array(getadr_join('am'),
                                          'am.cityid = gcim.city_id',
                                          'map.map_id = gcim.map_id'));
    $fields = new SFieldGroup(true, $SFields);
    $where  = $fields->get_where_statement();
    $joins  = $fields->get_select_statement();
    $countryres = $set->get('map.map_id AS id,
                             COUNT(u.user_id) AS nbPop,
                             SUM(u.promo % 2) AS yellow,
                             COUNT(DISTINCT gcim.city_id) AS nbCities,
                             SUM(IF(u.user_id IS NULL,0,am.glng)) AS lonPop,
                             SUM(IF(u.user_id IS NULL, 0,am.glat)) AS latPop',
                            $joins,
                            $where,
                            'map.map_id',
                            'NULL');

    $maxpop = 0;
    $nbentities = $nbcities + count($countryres);
    foreach ($countryres as $c) {
        $c['latPop'] /= $c['nbPop'];
        $c['lonPop'] /= $c['nbPop'];
        $c['rad'] = size_of_territory($c['nbPop']);
        if ($maxpop < $c['nbPop']) $maxpop = $c['nbPop'];
        $c['xPop'] = geoloc_to_x($c['lonPop'], $c['latPop']);
        $c['yPop'] = geoloc_to_y($c['lonPop'], $c['latPop']);
        @$countries[$c['id']] = array_merge($countries[$c['id']], $c);

        $nbcities += $c['nbCities'];
    }

    if ($nocity && $nbcities < $minentities){
        foreach($countries as $i => $c) {
            $countries[$i]['nbPop'] = 0;
            if (@$c['nbCities'] > 0) {
                geoloc_getData_subcities($c['id'], $sin, $cities, false);
            }
        }
    }

    foreach ($countries as $i => $c) {
        if (@$c['nbPop'] > 0) {
            $lambda = pow($c['nbPop'] / $maxpop,0.3);
            $countries[$i]['color'] = 0x0000FF + round((1-$lambda) * 0xFF)*0x010100;
        }
    }

    return array($countries, $cities);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
