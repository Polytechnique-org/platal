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

class GeolocModule extends PLModule
{
    function handlers()
    {
        return array(
            'geoloc'             => $this->make_hook('default', AUTH_COOKIE),
            'geoloc/icon.swf'    => $this->make_hook('icon',    AUTH_COOKIE),
            'geoloc/dynamap.swf' => $this->make_hook('dynamap', AUTH_COOKIE),
            'geoloc/init'        => $this->make_hook('init',    AUTH_COOKIE),
            'geoloc/city'        => $this->make_hook('city',    AUTH_COOKIE),
            'geoloc/country'     => $this->make_hook('country', AUTH_COOKIE),
            '%grp/geoloc'            => $this->make_hook('default',AUTH_COOKIE),
            '%grp/geoloc/icon.swf'   => $this->make_hook('icon',   AUTH_COOKIE),
            '%grp/geoloc/dynamap.swf'=> $this->make_hook('dynamap',AUTH_COOKIE),
            '%grp/geoloc/init'       => $this->make_hook('init',   AUTH_COOKIE),
            '%grp/geoloc/city'       => $this->make_hook('city',   AUTH_COOKIE),
            '%grp/geoloc/country'    => $this->make_hook('country',AUTH_COOKIE),
            'admin/geoloc'           => $this->make_hook('admin', AUTH_MDP, 'admin'),
            'admin/geoloc/dynamap'   => $this->make_hook('admin_dynamap', AUTH_MDP, 'admin'),
        );
    }

    function _make_qs()
    {
        $querystring = "";

        foreach ($_GET as $v => $a) {
            if ($v != 'initfile' && $v != 'n' && $v != 'mapid') {
                $querystring .= urlencode($v).'='.urlencode($a).'&amp;';
            }
        }

        return $querystring;
    }

    function use_map()
    {
        return is_file(dirname(__FILE__).'/geoloc/dynamap.swf') &&
                is_file(dirname(__FILE__).'/geoloc/icon.swf');
    }

    function handler_default(&$page)
    {
        global $globals;

        if (!$this->use_map())
            $page->assign('request_geodesix', 1);

        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            $page->assign('no_annu', 1);
            new_annu_page('geoloc/index.tpl');
        } else {
            $page->changeTpl('geoloc/index.tpl');
        }

        require_once dirname(__FILE__).'/search/search.inc.php';

        $fields = new SFieldGroup(true, advancedSearchFromInput());
        $search = str_replace('&amp;','&',$fields->get_url());
        if ((!Env::has('only_current') && !Env::has('rechercher')) || Env::v('only_current') == 'on')
            $search .= '&only_current=on';
        elseif (Env::i('only_current') != 'on')
            $search .= '&only_current=';

        $search = preg_replace('/(^|&)mapid=([0-9]+)(&)/','\1\3', $search);
        if ($search)
            $search = '?'.$search;
        $page->assign('search_nourlencode',$search);
        $page->assign('search',urlencode($search));

        $page->assign('protocole', substr($globals->baseurl,0,strpos($globals->baseurl,':')));

        if (!$search) {
            $res = XDB::query('SELECT COUNT(DISTINCT uid)
                                   FROM adresses WHERE cityid IS NOT NULL');
            $page->assign('localises', $res->fetchOneCell());
        }
    }

    function handler_icon(&$page)
    {
        global $globals;

        header("Content-type: application/x-shockwave-flash");
        header("Pragma:");

        readfile(dirname(__FILE__).'/geoloc/icon.swf');
        exit;

        return PL_NOT_FOUND;
    }

    function handler_dynamap(&$page)
    {
        global $globals;

        header("Content-type: application/x-shockwave-flash");

        header("Pragma:");
        readfile(dirname(__FILE__).'/geoloc/dynamap.swf');
        exit;

        return PL_NOT_FOUND;
    }

    function handler_init(&$page)
    {
        global $globals;

        $page->changeTpl('geoloc/init.tpl', NO_SKIN);

        header('Content-type: text/xml');
        header('Pragma:');
        if(!empty($GLOBALS['IS_XNET_SITE']))
            $page->assign('background', 0xF2E9D0);               
        $page->assign('querystring', $this->_make_qs());
    }

    function handler_city(&$page)
    {
        global $globals;

        header("Content-type: text/xml");
        header("Pragma:");

        $page->changeTpl('geoloc/city.tpl', NO_SKIN);

        require_once dirname(__FILE__).'/search/search.inc.php';
        require_once('geoloc.inc.php');

        if (empty($GLOBALS['IS_XNET_SITE'])) {
            $usual_fields = advancedSearchFromInput();
            $fields = new SFieldGroup(true, $usual_fields);
        } else {
            $_REQUEST['asso_id'] = $globals->asso('id');
            $_REQUEST['only_current'] = 'on';
            $fields   = new SFieldGroup(true, array(
                new RefSField('asso_id',array('gxm.asso_id'),'groupex.membres','gxm','u.user_id=gxm.uid'),
                new RefSField('cityid',array('av.cityid'),'adresses','av',getadr_join('av'))));
        }
        $where = $fields->get_where_statement();
        if ($where) $where = "WHERE ".$where;

        $users = XDB::iterator("
            SELECT u.user_id AS id, u.prenom, u.nom, u.promo, alias
              FROM adresses AS a 
        INNER JOIN auth_user_md5 AS u ON(u.user_id = a.uid)
        INNER JOIN auth_user_quick AS q ON(q.user_id = a.uid)
         LEFT JOIN aliases ON(u.user_id = aliases.id AND FIND_IN_SET(aliases.flags,'bestalias'))
                ".$fields->get_select_statement()."
                ".$where."
             GROUP BY u.user_id LIMIT 11", $id);

        $page->assign('users', $users);
    }

    function handler_country(&$page)
    {
        global $globals;

        // to debug sql use the next line
        if (Env::has('debug')) {
            $page->changeTpl('geoloc/country.tpl', SIMPLE);
        } else {
            header("Content-type: text/xml");
            header("Pragma:");
            $page->changeTpl('geoloc/country.tpl', NO_SKIN);
        }

        require_once dirname(__FILE__).'/search/search.inc.php';
        require_once 'geoloc.inc.php';

        $querystring = $this->_make_qs();
        $page->assign('searchvars', $querystring);

        $mapid = Env::has('mapid') ? Env::i('mapid', -2) : false;
        if (empty($GLOBALS['IS_XNET_SITE'])) {
            $fields = advancedSearchFromInput();
        } else {
            $_REQUEST['asso_id'] = $globals->asso('id');
            $_REQUEST['only_current'] = 'on';
            $fields   = array(new RefSField('asso_id',array('gxm.asso_id'),'groupex.membres','gxm','u.user_id=gxm.uid'));
        }

        list($countries, $cities) = geoloc_getData_subcountries($mapid, $fields, 10);

        $page->assign('countries', $countries);
        $page->assign('cities', $cities);
    }

    function handler_admin(&$page, $action = false) {
        $page->changeTpl('geoloc/admin.tpl');
        require_once("geoloc.inc.php");
        $page->assign('xorg_title','Polytechnique.org - Administration - Geolocalisation');
        
        $nb_synchro = 0;
        
        if (Env::has('id') && is_numeric(Env::v('id'))) {
            if (synchro_city(Env::v('id'))) $nb_synchro ++;
        }
        
        if ($action == 'missinglat') {
            $res = XDB::iterRow("SELECT id FROM geoloc_city WHERE lat = 0 AND lon = 0");
            while ($a = $res->next()) if (synchro_city($a[0])) $nb_synchro++;
        }
        
        if ($nb_synchro) 
            $page->trig(($nb_synchro > 1)?($nb_synchro." villes ont été synchronisées"):"Une ville a été synchronisée");
        
        $res = XDB::query("SELECT COUNT(*) FROM geoloc_city WHERE lat = 0 AND lon = 0");
        $page->assign("nb_missinglat", $res->fetchOneCell());
    }
    
    function handler_admin_dynamap(&$page, $action = false) {
        $page->changeTpl('geoloc/admin_dynamap.tpl');
        
        if ($action == 'cities_not_on_map') {
            require_once('geoloc.inc.php');
            if (!fix_cities_not_on_map(20))
                $page->trig("Impossible d'accéder au webservice");
            else
                $refresh = true;
        }
        
        if ($action == 'smallest_maps') {
            require_once('geoloc.inc.php');
            set_smallest_levels();
        }
        
        if ($action == 'precise_coordinates') {
            XDB::execute("UPDATE adresses AS a INNER JOIN geoloc_city AS c ON(a.cityid = c.id) SET a.glat = c.lat / 100000, a.glng = c.lon / 100000");
        }
        
        if ($action == 'newmaps') {
            require_once('geoloc.inc.php');
            if (!get_new_maps(Env::v('url')))
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
    }
    
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
