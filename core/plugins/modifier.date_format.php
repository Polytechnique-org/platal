<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

function smarty_modifier_date_format($string, $format = '%x', $default_date=null)
{
    $d = empty($string) ? $default_date : $string;
    if (empty($format) || preg_match('/^[ 0\-]*$/', $d)) return;
    $f = str_replace('%X', '%T', str_replace('%x', '%e %B %Y', $format));

    if (preg_match('/^\d{14}$/', $d)) {
        $t = mktime(substr($d,8,2), substr($d,10,2), substr($d,12,2), substr($d,4,2), substr($d,6,2), substr($d,0,4));
    } elseif (preg_match('/^\d{8}$/', $d)) {
        $t = mktime(0, 0, 0, substr($d,4,2), substr($d,6,2), substr($d,0,4));
    } elseif (is_numeric($d)) {
        $t = intval($d);
    } else {
        $t = strtotime($d);
    }

    if ( $t != -1 ) {
        return strftime($f , $t);
    } else {
        require_once('Date.php');
        $date = new Date($d);
        return $date->format($f);
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
