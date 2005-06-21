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
new_nonhtml_page('', AUTH_COOKIE);
header("Content-type: image/png");

$res = $globals->xdb->iterRow('SELECT lat, lon FROM geoloc_city AS gc LEFT JOIN adresses AS a ON(gc.id = a.cityid) WHERE a.cityid IS NOT NULL GROUP BY gc.id');

$img = imageCreateFromPng("../images/globe.png");

$coul = imagecolorallocate($img, 0, 0, 0);

while ($a = $res->next()) {
    $x = floor(($a[1]/100000 + 180 )/360*600);
    $y = floor((90 - $a[0]/100000)/180*300);
    imagefilledrectangle($img, $x, $y, $x+1, $y+1, $coul);
}

imagePng($img);
imagedestroy($img);
// vim:set et sws=4 sw=4 sts=4:
?>
