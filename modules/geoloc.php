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
        );
    }

    function _make_qs()
    {
        $querystring = "";

        foreach ($_GET as $v => $a) {
            if ($v != 'initfile' && $v != 'p' && $v != 'mapid') {
                $querystring .= urlencode($v).'='.urlencode($a).'&amp;';
            }
        }

        return $querystring;
    }

    function handler_default(&$page)
    {
        global $globals;

        if (!is_file(dirname(__FILE__).'/geoloc/dynamap.swf') ||
             !is_file(dirname(__FILE__).'/geoloc/icon.swf'))
            $page->assign('request_geodesix', 1);

        if (!empty($GLOBALS['IS_XNET_SITE'])) {
            $page->useMenu();
            $page->setType($globals->asso('cat'));
            $page->assign('no_annu', 1);
        }

        require_once 'search.inc.php';
        $page->changeTpl('geoloc/index.tpl');
        $fields = new SFieldGroup(true, advancedSearchFromInput());
        $search = $fields->get_url();
        if (!Env::has('only_current'))
            $search .= '&only_current=on';
        elseif (Env::get('only_current') != 'on')
            $search .= '&only_current=';

        $search = preg_replace('/(^|&amp;)mapid=([0-9]+)(&amp;|$)/','\1\3', $search);
        if ($search)
            $search = '?'.$search;
        $page->assign('search',$search);

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

        $page->changeTpl('geoloc/geolocInit.tpl', NO_SKIN);

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

        $page->changeTpl('geoloc/getCityInfos.tpl', NO_SKIN);

        require_once('geoloc.inc.php');
        require_once('search.inc.php');

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
            SELECT u.user_id AS id, u.prenom, u.nom, u.promo
              FROM adresses AS a 
        INNER JOIN auth_user_md5 AS u ON(u.user_id = a.uid)
        INNER JOIN auth_user_quick AS q ON(q.user_id = a.uid)
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
            $page->changeTpl('geoloc/getData.tpl', SIMPLE);
        } else {
            header("Content-type: text/xml");
            header("Pragma:");
            $page->changeTpl('geoloc/getData.tpl', NO_SKIN);
        }

        require_once 'geoloc.inc.php';
        require_once 'search.inc.php';

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
}

?>
