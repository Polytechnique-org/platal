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

require 'xnet.inc.php';

new_nonhtml_page('geoloc/getData.tpl');
$nodata = ($globals->asso('pub') != 'public') && !S::has_perms();

header("Content-type: text/xml");

require_once('geoloc.inc.php');
require_once('search.inc.php');

$querystring = "";
foreach ($_GET as $v => $a)
	if ($v != 'mapid')
		$querystring .= urlencode($v).'='.urlencode($a).'&amp;';
$page->assign('searchvars', $querystring);
if (Env::has('mapid'))
    $mapid = Env::i('mapid', -2);
else
    $mapid = false;

$_REQUEST['asso_id'] = $globals->asso('id');
$_REQUEST['only_current'] = 1;
$assoField   = new RefSField('asso_id',array('gxm.asso_id'),'groupex.membres','gxm','u.user_id=gxm.uid');

if (!$nodata) {
    list($countries, $cities) = geoloc_getData_subcountries($mapid, array($assoField), 10);
    $page->assign('countries', $countries);
    $page->assign('cities', $cities);
}

$page->run();
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
