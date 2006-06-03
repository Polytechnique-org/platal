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

if (Env::get('fix') == 'cities_not_on_map')
{
    require_once('geoloc.inc.php');
    if (!fix_cities_not_on_map(100))
        $page->trig("Impossible d'accéder au webservice");
}

if (Env::has('new_maps'))
{
	require_once('geoloc.inc.php');
	if (!get_new_maps(Env::get('url')))
		$page->trig("Impossible d'accéder aux nouvelles cartes");
}

$countMissing = $globals->xdb->query("SELECT COUNT(*) FROM geoloc_city AS c LEFT JOIN geoloc_city_in_maps AS m ON(c.id = m.city_id) WHERE m.city_id IS NULL");
$page->assign("nb_cities_not_on_map", $countMissing->fetchOneCell());

$page->run();

// vim:set et sws=4 sts=4 sw=4:
?>

