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

function display_ical($param) {
    $txt = '';
    if (isset($param['name']))
        $txt .= strtoupper($param['name']);
    if (isset($param['param']))
        $txt .= ';'.$param['param'];
    if (isset($param['value'])) {
        if ($txt) $txt .= ':';
        $txt .= utf8_encode(
                    preg_replace('/[,;:]/', '\\\\$0',
                    preg_replace("/(\r\n|\r|\n)/", '\n', $param['value'])));
    }
    $length = strlen($txt);
    $endline = "\n";
    $maxchars = 75;
    $folded_txt = substr($txt, 0, $maxchars);
    for ($i = $maxchars; $i < $length; $i += $maxchars - 1)
        $folded_txt .= $endline.' '.substr($txt, $i, $maxchars - 1);
    return $folded_txt;
}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
