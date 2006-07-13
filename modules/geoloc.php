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
            'geoloc'                  => $this->make_hook('default', AUTH_COOKIE),
            'geoloc/icon.php'         => $this->make_hook('icon',    AUTH_COOKIE),
            'geoloc/dynamap.php'      => $this->make_hook('dynamap', AUTH_COOKIE),
            'geoloc/geolocInit.php'   => $this->make_hook('init',    AUTH_COOKIE),
            'geoloc/getCityInfos.php' => $this->make_hook('city',    AUTH_COOKIE),
            'geoloc/getData.php'      => $this->make_hook('data',    AUTH_COOKIE),
        );
    }

    function _make_qs()
    {
        $querystring = "";

        foreach ($_GET as $v => $a) {
            if ($v != 'initfile') {
                $querystring .= '&'.urlencode($v).'='.urlencode($a);
            }
        }

        return $querystring;
    }

    function handler_default(&$page)
    {
        global $globals;

        require_once 'search.inc.php';

        $page->changeTpl('geoloc/index.tpl');

        $res = $globals->xdb->query('SELECT COUNT(DISTINCT uid)
                                       FROM adresses WHERE cityid IS NOT NULL');
        $page->assign('localises', $res->fetchOneCell());

        $fields = new SFieldGroup(true, advancedSearchFromInput());
        $search = $fields->get_url();
        if (Env::has('only_current') && Env::get('only_current') != 'on') {
            $search .= '&only_current=';
        }
        $search = preg_replace('/(^|&amp;)mapid=([0-9]+)(&amp;|$)/','\1\3', $search);
        if ($search) {
            $page->assign('dynamap_vars', $search);
        }

        $page->assign('use_map', $globals->geoloc->use_map());

        return PL_OK;
    }

    function handler_icon(&$page)
    {
        global $globals;

        header("Content-type: application/x-shockwave-flash");

        if ($globals->geoloc->use_map()) {
            readfile($globals->geoloc->icon_path);
            exit;
        }

        return PL_NOT_FOUND;
    }

    function handler_dynamap(&$page)
    {
        global $globals;

        $querystring = $this->_make_qs();
        $initfile    = urlencode('geolocInit.php?'.$querystring);

        if (urlencode(Env::get('initfile')) != $initfile) {
            header("Location: dynamap.php?initfile=$initfile{$querystring}");
            die();
        }

        header("Content-type: application/x-shockwave-flash");

        if ($globals->geoloc->use_map()) {
            readfile($globals->geoloc->dynamap_path);
            exit;
        }

        return PL_NOT_FOUND;
    }

    function handler_init(&$page)
    {
        global $globals;

        new_nonhtml_page('geoloc/geolocInit.tpl', AUTH_COOKIE);

        header('Content-type: text/xml');
        $page->assign('querystring', $this->_make_qs());

        return PL_OK;
    }

    function handler_city(&$page)
    {
        global $globals;

        header("Content-type: text/xml");

        new_nonhtml_page('geoloc/getCityInfos.tpl', AUTH_COOKIE);
        // to debug sql use the next line
        //new_skinned_page('', AUTH_COOKIE);

        require_once('geoloc.inc.php');
        require_once('search.inc.php');

        $usual_fields = advancedSearchFromInput();
        $fields = new SFieldGroup(true, $usual_fields);
        $where = $fields->get_where_statement();
        if ($where) $where = "WHERE ".$where;

        $users = $globals->xdb->iterator("
            SELECT u.user_id AS id, u.prenom, u.nom, u.promo
              FROM adresses AS a 
        INNER JOIN auth_user_md5 AS u ON(u.user_id = a.uid)
        INNER JOIN auth_user_quick AS q ON(q.user_id = a.uid)
                ".$fields->get_select_statement()."
                ".$where."
             GROUP BY u.user_id LIMIT 11", $id);

        $page->assign('users', $users);

        return PL_OK;
    }

    function handler_data(&$page)
    {
        global $globals;

        // to debug sql use the next line
        if (Env::has('debug')) {
            $page->changeTpl('geoloc/getData.tpl');
            $page->assign('simple', true);
        } else {
            header("Content-type: text/xml");
            new_nonhtml_page('geoloc/getData.tpl', AUTH_COOKIE);
        }

        require_once 'geoloc.inc.php';
        require_once 'search.inc.php';

        $querystring = $this->_make_qs();
        $page->assign('searchvars', $querystring);

        $mapid = Env::has('mapid') ? Env::getInt('mapid', -2) : false;

        list($countries, $cities) = geoloc_getData_subcountries($mapid, advancedSearchFromInput(), 10);

        $page->assign('countries', $countries);
        $page->assign('cities', $cities);

        return PL_OK;
    }
}

?>
