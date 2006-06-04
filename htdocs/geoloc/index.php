<?php
/***************************************************************************
 *  Copyright (C) 2003-2005 Polytechnique.org                              *
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
new_skinned_page('geoloc/index.tpl', AUTH_COOKIE);

$res = $globals->xdb->query('SELECT COUNT(DISTINCT uid) FROM adresses WHERE cityid IS NOT NULL');
$page->assign('localises', $res->fetchOneCell());

	require_once('search.inc.php');
$fields = new SFieldGroup(true, advancedSearchFromInput());
$search = $fields->get_url();
if (Env::has('only_current') && Env::get('only_current') != 'on') $search .= '&only_current=';
$search = preg_replace('/(^|&amp;)mapid=([0-9]+)(&amp;|$)/','\1\3', $search);
if ($search)
	$page->assign('dynamap_vars', $search);

$page->run();

// vim:set et sws=4 sw=4 sts=4:
?>
