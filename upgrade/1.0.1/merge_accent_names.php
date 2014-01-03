#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
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

require '../../bin/connect.db.inc.php';

$globals->debug = 0; // Do not store backtraces


$res = XDB::iterator("SELECT  p.pid, p.ax_id, p.hrpid,
                              f.Nom_patronymique, f.Nom_usuel, f.Nom_complet,
                              ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary
                        FROM  fusionax_anciens     AS f
                  INNER JOIN  profiles             AS p   ON (f.ax_id = p.ax_id)
                  INNER JOIN  profile_public_names AS ppn ON (p.pid = ppn.pid)
                    ORDER BY  pid");

function format($string)
{
    $string = preg_replace('/\-/', ' ', $string);
    return preg_replace('/\s+/', ' ', $string);
}

$updates_count = 0;
$count = 0;
$total = $res->total();
while ($item = $res->next()) {
    array_map('trim', $item);
    $ax_plain = array(
        'Nom_patronymique' => mb_strtolower(replace_accent($item['Nom_patronymique'])),
        'Nom_usuel'        => mb_strtolower(replace_accent($item['Nom_usuel'])),
        'Nom_complet'      => mb_strtolower(replace_accent($item['Nom_complet']))
    );
    $ax = array();
    foreach ($ax_plain as $key => $value) {
        $ax[$key] = format($value);
    }
    $xorg = array(
        'lastname_initial'  => format(mb_strtolower(replace_accent($item['lastname_initial']))),
        'lastname_main'     => format(mb_strtolower(replace_accent($item['lastname_main']))),
        'lastname_ordinary' => format(mb_strtolower(replace_accent($item['lastname_ordinary'])))
    );

    foreach ($xorg as $key => $name) {
        $ax_key = array_search($name, $ax);
        if ($ax_key !== false && mb_strtolower($item[$ax_key]) != mb_strtolower($item[$key]) && $ax_plain[$ax_key] == mb_strtolower($item[$key])) {
            XDB::execute("UPDATE  profile_public_names
                             SET  $key = {?}
                           WHERE  pid = {?}",
                         $item[$ax_key], $item['pid']);

            ++$updates_count;
        }
    }

    printf("\r%u / %u",  $count, $total);
    ++$count;
}
printf("\r%u / %u\n\n",  $count, $total);

echo "Nombre de mises à jour effectuées : " . $updates_count . ".\n";

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
