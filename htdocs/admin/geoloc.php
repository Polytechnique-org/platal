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
new_admin_page('admin/geoloc.tpl');
require_once("geoloc.inc.php");
$page->assign('xorg_title','Polytechnique.org - Administration - Geolocalisation');

$nb_synchro = 0;

if (Env::has('id') && is_numeric(Env::get('id'))) {
    if (synchro_city(Env::get('id'))) $nb_synchro ++;
}

if (Env::has('missinglat')) {
    $res = $globals->xdb->iterRow("SELECT id FROM geoloc_city WHERE lat = 0 AND lon = 0");
    while ($a = $res->next()) if (synchro_city($a[0])) $nb_synchro++;
}

if ($nb_synchro) 
    $page->trig(($nb_synchro > 1)?($nb_synchro." villes ont été synchronisées"):"Une ville a été synchronisée");

$res = $globals->xdb->query("SELECT COUNT(*) FROM geoloc_city WHERE lat = 0 AND lon = 0");
$page->assign("nb_missinglat", $res->fetchOneCell());

$page->run();

// vim:set et sws=4 sts=4 sw=4:
?>
