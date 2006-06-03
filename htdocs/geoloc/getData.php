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


require_once('xorg.inc.php');

// to debug sql use the next line
if (Env::has('debug'))
	new_simple_page('geoloc/getData.tpl', AUTH_COOKIE);
else
{
	header("Content-type: text/xml");
	new_nonhtml_page('geoloc/getData.tpl', AUTH_COOKIE);
}

require_once('search.inc.php');
require_once('geoloc.inc.php');

$usual_fields = advancedSearchFromInput();
foreach ($usual_fields as $i_mapfield => $field) if ($field->fieldFormName == 'mapid') break;

$querystring = "";
foreach ($_GET as $v => $a)
	if ($v != 'mapid')
		$querystring .= urlencode($v).'='.urlencode($a).'&amp;';
$page->assign('searchvars', $querystring);

$maxentities = 100;
$minentities = 5;

function get_cities_in_territory($t, $usual_fields, $direct=true)
{
  global $cities, $globals, $i_mapfield;
  $usual_fields[$i_mapfield] = new MapSField('mapid', array('gcim.map_id'), array('adresses','geoloc_city_in_maps'), array('am','gcim'), array(getadr_join('am'), 'am.cityid = gcim.city_id'), $t);
		$fields = new SFieldGroup(true, $usual_fields);
		$where = $fields->get_where_statement();
		if ($where) $where = " AND ".$where;
    $cityres = $globals->xdb->iterator("
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
            $city['x'] = geoloc_to_x($c['x'], $c['y']);
            $city['y'] = geoloc_to_y($c['x'], $c['y']);
            $city['size'] = size_of_city($c['pop']);
            $cities[$c['id']] = $city;
        }
}

if (Env::has('mapid'))
	$wheremapid = "WHERE   gm.parent = {?}";
else
	$wheremapid = "WHERE gm.parent IS NULL";

$submapres = $globals->xdb->iterator(
    "SELECT  gm.map_id AS id, gm.name, gm.x, gm.y, gm.xclip, gm.yclip, 
            gm.width, gm.height, gm.scale, 1 AS rat
    FROM    geoloc_maps AS gm
    ".$wheremapid, Env::get('mapid',''));

$countries = array();
while ($c = $submapres->next())
{
    $country = $c;
    $country['name'] = utf8_decode($country['name']);
    $country['color'] = 0xFFFFFF;
    $country['swf'] = $globals->geoloc->webservice_url."maps/mercator/map_".$c['id'].".swf";
    $countries[$c['id']] = $country;
}

$cities = array();
if (Env::has('mapid'))
{
	get_cities_in_territory(Env::getInt('mapid'), $usual_fields);
	$nbcities = count($cities);
	$nocity = $nbcities == 0;

	$usual_fields[$i_mapfield] = new MapSField('mapid', array('map.parent'), array('adresses','geoloc_city_in_maps','geoloc_maps'), array('am','gcim','map'), array(getadr_join('am'), 'am.cityid = gcim.city_id', 'map.map_id = gcim.map_id'));
	$fields = new SFieldGroup(true, $usual_fields);
	$where = $fields->get_where_statement();
	if ($where) $where = " WHERE ".$where;
		
	$countryres = $globals->xdb->iterator("
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
	
	if ($nocity && $nbcities < 10)
	{
	    foreach($countries as $i => $c)
	    {
	        $countries[$i]['nbPop'] = 0;
	        if ($c['nbCities'] > 0)
	            get_cities_in_territory($c['id'], $usual_fields, false);
	    }   
	}
	
	foreach ($countries as $i => $c) if ($c['nbPop'] > 0)
	{
	    $lambda = pow($c['nbPop'] / $maxpop,0.3);
	    $countries[$i]['color'] = 0x0000FF + round((1-$lambda) * 0xFF)*0x010100;
	}
}

$page->assign('countries', $countries);
$page->assign('cities', $cities);

$page->run();
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
