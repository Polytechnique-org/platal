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
 
function hash_encrypt($s) {
    return sha1($s);
}

function hash_xor($a, $b) {
    $c = "";
    $i = strlen($a);
    $j = strlen($b);
    if ($i < $j) {
        $d = $a; $a = $b; $b = $d;
        $k = $i; $i = $j; $j = $k;
    }
    for ($k = 0; $k < $j; $k++)
        $c .= dechex(hexdec($a{$k}) ^ hexdec($b{$k}));
    for (; $k < $i; $k++)
        $c .= $a{$k};
    return $c;
}

?>