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


require_once('xnet.inc.php');

$querystring = "";
foreach ($_GET as $v => $a)
	if ($v != 'initfile')
		$querystring .= '&'.urlencode($v).'='.urlencode($a);
$initfile = urlencode('geolocInit.php?'.$querystring);

if (urlencode(Env::v('initfile')) != $initfile)
{
	header("Location: dynamap.php?initfile=$initfile{$querystring}");
	die();
}

header("Content-type: application/x-shockwave-flash");

if ($globals->geoloc->use_map)
	readfile($globals->geoloc->dynamap_path);

?>
