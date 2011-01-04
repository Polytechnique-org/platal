#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

require './connect.db.inc.php';
require_once '../classes/address.php';

$globals->debug = 0; // Do not store backtraces

print "(Re)Formats postal addresses for all addresses in the database.\n";
$it = XDB::rawIterator('SELECT  *
                          FROM  profile_addresses
                      ORDER BY  pid, jobid, type, id');
$total = $it->total();
$i = 0;
$j = 0;
printf("\r%u / %u",  $i, $total);
while ($item = $it->next()) {
    $address = new Address($item);
    $address->format(array('postalText' => true));
    $address->delete();
    $address->save();

    ++$i;
    if ($i == 100) {
        ++$j;
        $i = 0;
        printf("\r%u / %u",  $i + 100 * $j, $total);
    }
}
print "Done.\n";

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
