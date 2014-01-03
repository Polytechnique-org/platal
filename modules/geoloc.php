<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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
            'map'     => $this->make_hook('map',     AUTH_COOKIE, 'user'),
            'map_url' => $this->make_hook('map_url', AUTH_COOKIE, 'user')
        );
    }

    static public function prepare_map(PlPage $page)
    {
        global $globals;
        $page->changeTpl('geoloc/index.tpl');
        $map_url = $globals->maps->dynamic_map . '?&sensor=false&v=' . $globals->maps->api_version . '&language=' . $globals->maps->language;
        $page->addJsLink($map_url, false);
        $page->addJsLink('maps.js');
        $page->addJsLink('markerclusterer.js');
        $page->assign('pl_extra_header', '<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />');
    }

    static public function assign_json_to_map(PlPage $page, $pids = null)
    {
        if (!is_null($pids)) {
            $where = XDB::format(' AND pa.pid IN {?}', $pids);
        } else {
            $where = '';
        }

        if (!S::logged() || !S::user()->checkPerms('directory_ax')) {
            $where .= " AND pa.pub = 'public'";
            $name_publicity = 'public';
        } else if (!S::user()->checkPerms('directory_private')) {
            $where .= " AND pa.pub = 'ax'";
            $name_publicity = 'public';
        } else {
            $name_publicity = 'private';
        }

        $data = XDB::rawFetchAllAssoc('SELECT  pa.latitude, pa.longitude, GROUP_CONCAT(DISTINCT p.hrpid SEPARATOR \',\') AS hrpid,
                                               GROUP_CONCAT(pd.promo SEPARATOR \',\') AS promo,
                                               GROUP_CONCAT(DISTINCT pd.' . $name_publicity . '_name, \' (\', pd.promo, \')\' SEPARATOR \', \') AS name,
                                               GROUP_CONCAT(DISTINCT pa.pid SEPARATOR \',\') AS pid
                                         FROM  profile_addresses AS pa
                                   INNER JOIN  profiles          AS p  ON (pa.pid = p.pid)
                                   INNER JOIN  profile_display   AS pd ON (pd.pid = pa.pid)
                                        WHERE  pa.type = \'home\' AND p.deathdate IS NULL AND pa.latitude IS NOT NULL AND pa.longitude IS NOT NULL' . $where . '
                                     GROUP BY  pa.latitude, pa.longitude');
        $page->jsonAssign('data', $data);
    }

    function handler_map($page)
    {
        if (Get::b('ajax')) {
            self::assign_json_to_map($page);
            return PL_JSON;
        } else {
            self::prepare_map($page);
        }
    }

    function handler_map_url($page)
    {
        pl_content_headers('text/plain');

        if (Post::has('text')) {
            $address = new Address(array('text' => Post::t('text')));
            $gmapsGeocoder = new GMapsGeocoder();
            $gmapsGeocoder->getGeocodedAddress($address);
            echo GMapsGeocoder::buildStaticMapURL($address->latitude, $address->longitude, Post::t('color'));
        }

        exit();
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
