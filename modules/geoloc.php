<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

class GeolocModule extends PLModule
{
    function handlers()
    {
        return array(
            'map'      => $this->make_hook('map',      AUTH_COOKIE),
            'map/ajax' => $this->make_hook('map_ajax', AUTH_COOKIE)
        );
    }

    function handler_map($page)
    {
        global $globals;
        $page->changeTpl('geoloc/index.tpl');
        $map_url = $globals->maps->dynamic_map . '?&sensor=false&v=' . $globals->maps->api_version . '&language=' . $globals->maps->language;
        $page->addJsLink($map_url, false);
        $page->addJsLink('maps.js');
        $page->addJsLink('markerclusterer_packed.js');
        $page->addJsLink('markerwithlabel_packed.js');
        $page->assign('pl_extra_header', '<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />');

        $page->assign('latitude', 0);
        $page->assign('longitude', 0);
    }

    function handler_map_ajax($page)
    {
        $data = XDB::rawFetchAllAssoc('SELECT  pa.latitude, pa.longitude, GROUP_CONCAT(DISTINCT p.hrpid SEPARATOR \',\') AS hrpid,
                                               GROUP_CONCAT(pd.promo SEPARATOR \',\') AS promo,
                                               GROUP_CONCAT(DISTINCT CONCAT(pd.private_name, \' (\', pd.promo, \')\') SEPARATOR \',\') AS name
                                         FROM  profile_addresses AS pa
                                   INNER JOIN  profiles          AS p  ON (pa.pid = p.pid)
                                   INNER JOIN  profile_display   AS pd ON (pd.pid = pa.pid)
                                        WHERE  pa.type = \'home\' AND pa.latitude IS NOT NULL AND pa.longitude IS NOT NULL
                                     GROUP BY  pa.latitude, pa.longitude');
        $page->jsonAssign('data', $data);

        return PL_JSON;
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
