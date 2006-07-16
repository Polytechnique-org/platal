<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
new_admin_page('admin/geoloc_dynamap.tpl');

if (Env::get('fix') == 'cities_not_on_map') {
    require_once('geoloc.inc.php');
    if (!fix_cities_not_on_map(20))
        $page->trig("Impossible d'accéder au webservice");
    else
        $refresh = true;
}

if (Env::get('fix') == 'smallest_maps') {
    require_once('geoloc.inc.php');
    set_smallest_levels();
}

if (Env::get('fix') == 'precise_coordinates') {
    XDB::execute("UPDATE adresses AS a INNER JOIN geoloc_city AS c ON(a.cityid = c.id) SET a.glat = c.lat / 100000, a.glng = c.lon / 100000");
}

if (Env::has('new_maps')) {
    require_once('geoloc.inc.php');
    if (!get_new_maps(Env::get('url')))
        $page->trig("Impossible d'accéder aux nouvelles cartes");
}

$countMissing = XDB::query("SELECT COUNT(*) FROM geoloc_city AS c LEFT JOIN geoloc_city_in_maps AS m ON(c.id = m.city_id) WHERE m.city_id IS NULL");
$missing = $countMissing->fetchOneCell();

$countNoSmallest = XDB::query("SELECT SUM(IF(infos = 'smallest',1,0)) AS n FROM geoloc_city_in_maps GROUP BY city_id ORDER BY n");
$noSmallest = $countNoSmallest->fetchOneCell() == 0;

$countNoCoordinates = XDB::query("SELECT COUNT(*) FROM adresses WHERE cityid IS NOT NULL AND glat = 0 AND glng = 0");
$noCoordinates = $countNoCoordinates->fetchOneCell();

if (isset($refresh) && $missing) {
    $page->assign("xorg_extra_header", "<meta http-equiv='Refresh' content='3'/>");
}
$page->assign("nb_cities_not_on_map", $missing);
$page->assign("no_smallest", $noSmallest);
$page->assign("no_coordinates", $noCoordinates);

$page->run();

// vim:set et sws=4 sts=4 sw=4:
?>

