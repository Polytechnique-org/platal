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

// {{{ liste les pays ou les régions d'un pays
/** donne la liste déroulante des pays
 * @param $current pays actuellement selectionné
 */
function geoloc_country($current) {
    $res  = XDB::iterRow('SELECT a2,pays FROM geoloc_pays ORDER BY pays');
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
    $res  = XDB::iterRow('SELECT region,name FROM geoloc_region where a2={?} ORDER BY name', $country);
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

function geoloc_is_utf8($text)
{
    return (iconv('utf-8', 'utf-8', $text) == $text);
}

function geoloc_utf8_decode($text)
{
    return geoloc_is_utf8($text)  ? utf8_decode($text) : $text;
}

// {{{ get_address_infos($txt)
/** retrieve the infos on a text address
 * store on the fly the info of the city concerned
 * @param $txt the raw text of an address
 */
function get_address_infos($txt) {
    global $globals;
    $url = $globals->geoloc->webservice_url."address.php?precise=1&txt=".urlencode(utf8_encode($txt));
    if (!($f = @fopen($url, 'r'))) return false;
    $keys = explode('|',fgets($f));
    $vals = explode('|',fgets($f));
    $infos = array();
    foreach ($keys as $i=>$key) {
        if($vals[$i]) {
            if ($key == 'sql') {
                $infos[$key] = $vals[$i];
            } else {
                $val = strtr($vals[$i], array(chr(197).chr(147) => "&oelig;"));
                $infos[$key] = geoloc_utf8_decode($val);
            }
        }
    }
    if (isset($infos['sql']) && $infos['sql'])
       XDB::execute("REPLACE INTO geoloc_city VALUES ".$infos['sql']);
    if (isset($infos['display']) && $infos['display'])
        XDB::execute("UPDATE geoloc_pays SET display = {?} WHERE a2 = {?}", $infos['display'], $infos['country']);
    if (isset($infos['cityid']))
    	fix_cities_not_on_map(1, $infos['cityid']);
    return $infos;
}
// }}}

// {{{ get_cities_maps($array)
/* get all the maps id of the cities contained in an array */
function get_cities_maps($array)
{
    global $globals;
    implode("\n",$array);
    $url = $globals->geoloc->webservice_url."findMaps.php?datatext=".urlencode(utf8_encode(implode("\n", $array)));
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

// {{{ get_address_text($adr)
/** make the text of an address that can be read by a mailman
 * @param $adr an array with all the usual fields
 */
function get_address_text($adr) {
    $t = "";
    if (isset($adr['adr1']) && $adr['adr1']) $t.= $adr['adr1'];
    if (isset($adr['adr2']) && $adr['adr2']) $t.= "\n".$adr['adr2'];
    if (isset($adr['adr3']) && $adr['adr3']) $t.= "\n".$adr['adr3'];
    $l = "";
    if (isset($adr['display']) && $adr['display']) {
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
            if (isset($adr['postcode']) && $adr['postcode']) $l .= $adr['postcode']." ";
            if (isset($adr['city']) && $adr['city']) $l .= $adr['city'];
        }
    }
    if ($l) $t .= "\n".trim($l);
    if ($adr['country'] != '00' && (!$adr['countrytxt'] || $adr['countrytxt'] == strtoupper($adr['countrytxt']))) {
        $res = XDB::query("SELECT pays FROM geoloc_pays WHERE a2 = {?}", $adr['country']);
        $adr['countrytxt'] = $res->fetchOneCell();
    }
    if (isset($adr['countrytxt']) && $adr['countrytxt']) $t .= "\n".$adr['countrytxt'];
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
    $res = XDB::iterator("SELECT * FROM adresses WHERE uid = {?} and (cityid IS NULL OR cityid = 0)", $uid);
    $erreur = Array();

    while ($a = $res->next()) {
        $new = get_address_infos($ta = get_address_text($a));
        if (compare_addresses_text($ta, get_address_text($new))) {
            XDB::execute("UPDATE adresses SET
                adr1 = {?}, adr2 = {?}, adr3 = {?},
                cityid = {?}, city = {?}, postcode = {?},
                region = {?}, regiontxt = {?}, country = {?},
                glat = {?}, glng = {?}
                WHERE uid = {?} AND adrid = {?}",
                $new['adr1'], $new['adr2'], $new['adr3'],
                $new['cityid'], $new['city'], $new['postcode'],
                $new['region'], $new['regiontxt'], $new['country'],
                $new['precise_lat'], $new['precise_lon'],
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
    global $globals;
    $url = $globals->geoloc->webservice_url."cityFinder.php?method=id&id=".$id."&out=sql";
    if (!($f = @fopen($url, 'r'))) return false;
    $s = fgets($f);
    if ($s)
        return XDB::execute("REPLACE INTO geoloc_city VALUES ".$s) > 0;
 }
 // }}}

// {{{ function fix_cities_not_on_map($limit)
function fix_cities_not_on_map($limit=false, $cityid=false)
{
    $missing = XDB::query("SELECT c.id FROM geoloc_city AS c LEFT JOIN geoloc_city_in_maps AS m ON(c.id = m.city_id) WHERE m.city_id IS NULL".($cityid?(" AND c.id = '".$cityid."'"):"").($limit?" LIMIT $limit":""));
    $maps = get_cities_maps($missing->fetchColumn());
    if ($maps)
    {
        $values = "";
        foreach ($maps as $cityid => $maps_c)
            foreach ($maps_c as $map_id)
                $values .= ",($cityid, $map_id, '')";
        XDB::execute("REPLACE INTO geoloc_city_in_maps VALUES ".substr($values, 1));
    }
    else
        return false;
    return true;
}

function set_smallest_levels() {
    $maxlengths = XDB::iterRow("SELECT MAX(LENGTH(gm.path)), gcim.city_id
        FROM geoloc_city_in_maps AS gcim
        INNER JOIN geoloc_maps AS gm
        USING ( map_id )
        GROUP BY gcim.city_id
        ");
    while (list($length, $id) = $maxlengths->next()) {
        XDB::execute("UPDATE geoloc_city_in_maps AS gcim
            INNER JOIN geoloc_maps AS gm USING(map_id)
            SET gcim.infos = IF(LENGTH(gm.path) = {?}, 'smallest', '')
            WHERE gcim.city_id = {?}", $length, $id);
    }
    return true;
}
// }}}


function geoloc_to_x($lon, $lat) { return deg2rad(1) * $lon *100; }

function geoloc_to_y($lon, $lat) {
	if ($lat < -75) return latToY(-75);
	if ($lat > 75) return latToY(75);
  return -100 * log(tan(pi()/4 + deg2rad(1)/2*$lat));	
}

function size_of_city($nb) { $s = round(log($nb + 1)*2,2); if ($s < 1) return 1; return $s; }
function size_of_territory($nb) { return size_of_city($nb); }

function geoloc_getData_subcities($mapid, $SFields, &$cities, $direct=true) {
    for ($i_mapfield=0; $i_mapfield < count($SFields) ; $i_mapfield++) if ($SFields[$i_mapfield]->fieldFormName == 'mapid') break;
    $SFields[$i_mapfield] = new MapSField('mapid', array('gcim.map_id'), array('adresses','geoloc_city_in_maps'), array('am','gcim'), array(getadr_join('am'), 'am.cityid = gcim.city_id'), $mapid);
    
    $fields = new SFieldGroup(true, $SFields);
    $where = $fields->get_where_statement();
    if ($where) $where = " AND ".$where;

    $cityres = XDB::iterator("
        SELECT  gc.id,
                gc.lon / 100000 AS x, gc.lat/100000 AS y,
                gc.name,
                COUNT(u.user_id) AS pop,
                SUM(u.promo % 2) AS yellow
          FROM auth_user_md5 AS u
    INNER JOIN auth_user_quick AS q ON(u.user_id = q.user_id)
            ".$fields->get_select_statement()."
				 LEFT JOIN geoloc_city AS gc ON(gcim.city_id = gc.id)
         WHERE ".($direct?"gcim.infos = 'smallest'":"1")."
         $where
      GROUP BY gc.id,gc.alias ORDER BY pop DESC");
    while ($c = $cityres->next())
        if ($c['pop'] > 0)
        {
            $city = $c;
            //      $city['name'] = geoloc_utf8_decode($city['name']);
            if (!geoloc_is_utf8($city['name'])) {
                $city['name'] = utf8_encode($city['name']);
            }
            $city['x'] = geoloc_to_x($c['x'], $c['y']);
            $city['y'] = geoloc_to_y($c['x'], $c['y']);
            $city['size'] = size_of_city($c['pop']);
            $cities[$c['id']] = $city;
        }
}

function geoloc_getData_subcountries($mapid, $SFields, $minentities) {
    $countries = array();
    $cities = array();
    
    if ($mapid === false)
    	$wheremapid = "WHERE gm.parent IS NULL";
    else
    	$wheremapid = "WHERE   gm.parent = {?}";
    $submapres = XDB::iterator(
        "SELECT  gm.map_id AS id, gm.name, gm.x, gm.y, gm.xclip, gm.yclip, 
                gm.width, gm.height, gm.scale, 1 AS rat
        FROM    geoloc_maps AS gm
        ".$wheremapid, Env::v('mapid',''));

    global $globals;

    while ($c = $submapres->next())
    {
        $country = $c;
        $country['name'] = utf8_decode($country['name']);
        $country['color'] = 0xFFFFFF;
        $country['swf'] = $globals->geoloc->webservice_url."maps/mercator/map_".$c['id'].".swf";
        $countries[$c['id']] = $country;
    }
    
    if ($mapid === false) return array($countries, $cities);

	geoloc_getData_subcities(Env::i('mapid'), $SFields, $cities);
	$nbcities = count($cities);
	$nocity = $nbcities == 0;

    for ($i_mapfield=0; $i_mapfield < count($SFields) ; $i_mapfield++) if ($SFields[$i_mapfield]->fieldFormName == 'mapid') break;
	$SFields[$i_mapfield] = new MapSField('mapid', array('map.parent'), array('adresses','geoloc_city_in_maps','geoloc_maps'), array('am','gcim','map'), array(getadr_join('am'), 'am.cityid = gcim.city_id', 'map.map_id = gcim.map_id'));

	$fields = new SFieldGroup(true, $SFields);
	$where = $fields->get_where_statement();
	if ($where) $where = " WHERE ".$where;
		
	$countryres = XDB::iterator("
	    SELECT  map.map_id AS id,
	            COUNT(u.user_id) AS nbPop,
	            SUM(u.promo % 2) AS yellow,
	            COUNT(DISTINCT gcim.city_id) AS nbCities,
	            SUM(IF(u.user_id IS NULL,0,am.glng)) AS lonPop,
	            SUM(IF(u.user_id IS NULL, 0,am.glat)) AS latPop
	      FROM  auth_user_md5 AS u
	INNER JOIN  auth_user_quick AS q ON(u.user_id = q.user_id)
	            ".$fields->get_select_statement()."
	     $where
	  GROUP BY  map.map_id ORDER BY NULL", $hierarchy);
	
	$maxpop = 0;
	$nbentities = $nbcities + $countryres->total();
	while ($c = $countryres->next())
	{
	    $c['latPop'] /= $c['nbPop'];
	    $c['lonPop'] /= $c['nbPop'];
	    $c['rad'] = size_of_territory($c['nbPop']);
	    if ($maxpop < $c['nbPop']) $maxpop = $c['nbPop'];
	    $c['xPop'] = geoloc_to_x($c['lonPop'], $c['latPop']);
	    $c['yPop'] = geoloc_to_y($c['lonPop'], $c['latPop']);
	    $countries[$c['id']] = array_merge($countries[$c['id']], $c);
	
	    $nbcities += $c['nbCities'];
	}
	
	if ($nocity && $nbcities < $minentities)
	{
	    foreach($countries as $i => $c)
	    {
	        $countries[$i]['nbPop'] = 0;
	        if ($c['nbCities'] > 0)
	            geoloc_getData_subcities($c['id'], $SFields, $cities, false);
	    }   
	}
	
	foreach ($countries as $i => $c) if ($c['nbPop'] > 0)
	{
	    $lambda = pow($c['nbPop'] / $maxpop,0.3);
	    $countries[$i]['color'] = 0x0000FF + round((1-$lambda) * 0xFF)*0x010100;
	}
	
    return array($countries, $cities);   
} 
// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
