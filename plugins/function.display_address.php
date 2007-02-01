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


function smarty_function_display_address($param, &$smarty) {
    require_once('geoloc.inc.php');
    $txtad = get_address_text($param['adr']);
    if (!$txtad &&
        !$param['adr']['tels'] && !count($param['adr']['tels']) &&
        !$param['adr']['tel'] &&
        !$param['adr']['fax'] &&
        !$param['adr']['mobile']) return "";

    $lines = explode("\n", $txtad);
    $txthtml = "";
    $map = "<a href=\"http://maps.google.fr/?q=".urlencode(str_replace('États-Unis d\'Amérique', 'USA', implode(", ", $lines)))."\"><img src=\"images/icons/map.gif\" alt=\"Google Maps\" title=\"Carte\"/></a>";
    if ($param['titre'])
    {
        if ($param['titre_div'])
            $txthtml .= "<div class='titre'>".$param['titre'].$map."</div>\n";
    else
           $txthtml .= "<em>".$param['titre']."</em>".$map."<br />\n";
    }
    foreach ($lines as $line)
    {
        $txthtml .= "<strong>".$line."</strong><br/>\n";
    }
    if ($param['adr']['tel'])
        $txthtml .= "<div>\n<em>Tél : </em>\n<strong>".$param['adr']['tel']."</strong>\n</div>\n";
    if ($param['adr']['fax'])
        $txthtml .= "<div>\n<em>Fax : </em>\n<strong>".$param['adr']['fax']."</strong>\n</div>\n";
    if ($param['adr']['mobile'])
        $txthtml .= "<div>\n<em>Tél : </em>\n<strong>".$param['adr']['mobile']."</strong>\n</div>\n";
    if ($param['adr']['tels'] && count($param['adr']['tels'])) {
        foreach ($param['adr']['tels'] as $tel) 
            $txthtml .= "<div>\n<em>".$tel['tel_type']."&nbsp;: </em>\n<strong>".$tel['tel']."</strong>\n</div>\n";
    }
    if (!$params['nodiv'])
    {
        $txthtml = "<div class='adresse'>\n".$txthtml."</div>\n";
    }
    return $txthtml;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
