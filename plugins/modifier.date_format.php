<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
    if (empty($format) && empty($default_date)) return;
    $f = empty($format) ? $default_date : $format;
    $f = str_replace('%X', '%T', str_replace('%x', '%d %B %Y', $f));

    if (preg_match('/^\d{14}$/', $string)) {
        $t = mktime(substr($string,8,2),substr($string,10,2),substr($string,12,2),
                substr($string,4,2),substr($string,6,2),substr($string,0,4));
    } else {
        $t = strtotime($string);
    }

    if ( $t != -1 ) {
        return strftime($f , $t);
    } else {
        require_once('Date.php');
        $date = new Date($string);
        return $date->format($f);
    }
}

?>
