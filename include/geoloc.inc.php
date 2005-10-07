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


// {{{ liste les pays ou les régions d'un pays
/** donne la liste déroulante des pays
 * @param $current pays actuellement selectionné
 */
function geoloc_country($current) {
    global $globals;
    $res  = $globals->xdb->iterRow('SELECT a2,pays FROM geoloc_pays ORDER BY pays');
    $html = "";
    while (list($my_id, $my_pays) = $res->next()) {
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n",
                $my_id, ($current==$my_id?"selected='selected'":""), $my_pays);
    }
    return $html;
}

function _geoloc_country_smarty($params){
  if(!isset($params['country']))
    return;
  return geoloc_country($params['country']);
}
$GLOBALS['page']->register_function('geoloc_country', '_geoloc_country_smarty');

/** donne la liste deroulante des regions pour un pays
 * @param $pays le pays dont on veut afficher les regions
 * @param $current la region actuellement selectionnee
 */
function geoloc_region($country,$current) {
    global $globals;
    $res  = $globals->xdb->iterRow('SELECT region,name FROM geoloc_region where a2={?} ORDER BY name', $country);
    $html = "<option value=\"\"></option>";
    while (list($regid, $regname) = $res->next()) {
	$html .= sprintf("<option value=\"%s\" %s>%s</option>\n",
                $regid, ($current==$regid?"selected='selected'":""), $regname);
    }
    return $html;

}
function _geoloc_region_smarty($params){
  if(!isset($params['country']))
    return;
  if(!isset($params['region']))
    return;
  return geoloc_region($params['country'], $params['region']);
}
$GLOBALS['page']->register_function('geoloc_region', '_geoloc_region_smarty');
// }}}

// {{{ get_address_infos($txt)
/** retrieve the infos on a text address
 * store on the fly the info of the city concerned
 * @param $txt the raw text of an address
 */
function get_address_infos($txt) {
    $url ="http://www.geoloc.org/adressparser/address.php?txt=".urlencode(utf8_encode($txt));
    if (!($f = @fopen($url, 'r'))) return false;
    $keys = explode('|',fgets($f));
    $vals = explode('|',fgets($f));
    $infos = array();
    foreach ($keys as $i=>$key) if($vals[$i]) $infos[$key] = ($key == 'sql')?$vals[$i]:utf8_decode(strtr($vals[$i], array(chr(197).chr(147) => "&oelig;")));
    global $globals;
    if ($infos['sql'])
       $globals->xdb->execute("REPLACE INTO geoloc_city VALUES ".$infos['sql']);
    if ($infos['display'])
       $globals->xdb->execute("UPDATE geoloc_pays SET display = {?} WHERE a2 = {?}", $infos['display'], $infos['country']);
    return $infos;
}
// }}}

// {{{ get_address_text($adr)
/** make the text of an address that can be read by a mailman
 * @param $adr an array with all the usual fields
 */
function get_address_text($adr) {
    $t = "";
    if ($adr['adr1']) $t.= $adr['adr1'];
    if ($adr['adr2']) $t.= "\n".$adr['adr2'];
    if ($adr['adr3']) $t.= "\n".$adr['adr3'];
    $l = "";
    if ($adr['display']) {
        $keys = explode(' ', $adr['display']);
        foreach ($keys as $key) {
            if (isset($adr[$key]))
                $l .= " ".$adr[$key];
            else
                $l .= " ".$key;
        }
        if ($l) $l = substr($l, 1);
    }
    else
    {
        if ($adr['country'] == 'US' || $adr['country'] == 'CA' || $adr['country'] == 'GB') {
            if ($adr['city']) $l .= $adr['city'].",\n";
            if ($adr['region']) $l .= $adr['region']." ";
            if ($adr['postcode']) $l .= $adr['postcode'];
        } else {
            if ($adr['postcode']) $l .= $adr['postcode']." ";
            if ($adr['city']) $l .= $adr['city'];
        }
    }
    if ($l) $t .= "\n".trim($l);
    if ($adr['country'] != '00' && (!$adr['countrytxt'] || $adr['countrytxt'] == strtoupper($adr['countrytxt']))) {
        global $globals;
        $res = $globals->xdb->query("SELECT pays FROM geoloc_pays WHERE a2 = {?}", $adr['country']);
        $adr['countrytxt'] = $res->fetchOneCell();
    }
    if ($adr['countrytxt']) $t .= "\n".$adr['countrytxt'];
    return trim($t);
}
// }}}

// {{{ compare_addresses_text($a, $b)
/** compares if two address matches
 * @param $a the raw text of an address
 * @param $b the raw text of a complete valid address
 */
function compare_addresses_text($a, $b) {
    $ta = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"), array("", "\n"), $a));
    $tb = strtoupper(preg_replace(array("/[0-9,\"'#~:;_\- ]/", "/\r\n/"), array("", "\n"), $b));
   
    $la = explode("\n", $ta);
    $lb = explode("\n", $tb);

    if (count($lb) > count($la) + 1) return false;
    foreach ($la as $i=>$l) if (levenshtein($l, $lb[$i]) > 3) return false;
    return true;
}

// }}}

function empty_address() {
    return Array(
        "adr1" => "",
        "adr2" => "",
        "adr3" => "",
        "cityid" => NULL,
        "city" => "",
        "postcode" => "",
        "region" => "",
        "regiontxt" => "",
        "country" => "00",
        "countrytxt" => "");
}

// create a simple address from a text without geoloc
function cut_address($txt) {
    $txt = str_replace("\r\n", "\n", $txt);
    ereg("^([^\n]*)(\n([^\n]*)(\n(.*))?)?$", trim($txt), $a);
    return array("adr1" => trim($a[1]), "adr2" => trim($a[3]), "adr3" => trim(str_replace("\n", " ", $a[5])));
}

// {{{ localize_addresses($uid)
/* localize all the address of a user and modify the database
 * if the new address match with the old one
 * @param $uid the id of the user
 */
function localize_addresses($uid) {
    global $globals;
    $res = $globals->xdb->iterator("SELECT * FROM adresses WHERE uid = {?} and (cityid IS NULL OR cityid = 0)", $uid);
    $erreur = Array();

    while ($a = $res->next()) {
        $new = get_address_infos($ta = get_address_text($a));
        if (compare_addresses_text($ta, get_address_text($new))) {
            $globals->xdb->execute("UPDATE adresses SET
                adr1 = {?}, adr2 = {?}, adr3 = {?},
                cityid = {?}, city = {?}, postcode = {?},
                region = {?}, regiontxt = {?}, country = {?}
                WHERE uid = {?} AND adrid = {?}",
                $new['adr1'], $new['adr2'], $new['adr3'],
                $new['cityid'], $new['city'], $new['postcode'],
                $new['region'], $new['regiontxt'], $new['country'],
                $uid, $a['adrid']);
                $new['store'] = true;
                if (!$new['cityid']) $erreur[$a['adrid']] = $new;
        } else {
            $new['store'] = false;
            $erreur[$a['adrid']] = $new;
        }
    }
    return $erreur;
}
// }}}

// {{{ synchro_city($id)
/** synchronise the local geoloc_city base to geoloc.org
 * @param $id the id of the city to synchronize
 */
 function synchro_city($id) {
    $url ="http://www.geoloc.org/adressparser/cityFinder.php?method=id&id=".$id."&out=sql";
    if (!($f = @fopen($url, 'r'))) return false;
    $s = fgets($f);
    global $globals;
    if ($s)
        return $globals->xdb->execute("REPLACE INTO geoloc_city VALUES ".$s) > 0;
 }
 // }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
