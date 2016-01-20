#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

require_once './connect.db.inc.php';
require_once 'wats4u.inc.php';
ini_set('memory_limit', '128M');

$lines = generate_wats4u_extract();
$nb_lines = 0;
while ($line = $lines->next()) {
    fputcsv(
        STDOUT,  // Handle
        array(  // Fields
            $line['ax_id'],
            $line['school'],
            $line['diploma'],
            $line['promo'],
            $line['birth_name'],
            $line['last_name'],
            $line['first_name'],
            $line['female'] ? 'F' : 'M',
            $line['email'],
            $line['paying'],
        ),
        ';' // Delimiter
    );
    $nb_lines += 1;
}

fwrite(STDERR, "Wrote $nb_lines lines.\n");

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
