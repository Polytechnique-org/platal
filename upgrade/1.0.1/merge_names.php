#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
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

$res = XDB::rawFetchAllAssoc("SELECT  p.pid, p.ax_id, p.hrpid,
                                      f.Nom_patronymique, f.Nom_usuel, f.Nom_complet,
                                      ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary
                                FROM  fusionax_anciens     AS f
                          INNER JOIN  profiles             AS p   ON (f.ax_id = p.ax_id)
                          INNER JOIN  profile_public_names AS ppn ON (p.pid = ppn.pid)
                               WHERE  IF(f.partic_patro, CONCAT(f.partic_patro, CONCAT(' ', f.Nom_patronymique)), f.Nom_patronymique) NOT IN (ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary)
                                      OR IF(f.partic_nom, CONCAT(f.partic_nom, CONCAT(' ', f.Nom_usuel)), f.Nom_usuel) NOT IN (ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary)
                                      OR f.Nom_complet NOT IN (ppn.lastname_initial, ppn.lastname_main, ppn.lastname_marital, ppn.lastname_ordinary)");

function fix_ax_particles($ax, $xorg, $item)
{
    $count = 0;
    foreach ($ax as $ax_key => $ax_name) {
        if (!in_array($ax_name, $xorg)) {
            $new_name = '';
            foreach ($xorg as $xorg_key => $xorg_name) {
                if ($xorg_name && strpos($xorg_name, $ax_name) !== false) {
                    if ($xorg_name == 'de ' . $ax_name) {
                        $new_name = 'de ' . $item[$ax_key];
                    } elseif ($xorg_name == "d'" . $ax_name) {
                        $new_name = "d'" . $item[$ax_key];
                    } elseif ($xorg_name == 'du ' . $ax_name) {
                        $new_name = 'du ' . $item[$ax_key];
                    }

                    if ($new_name) {
                        XDB::execute("UPDATE  fusionax_anciens
                                         SET  $ax_key = {?}
                                       WHERE  ax_id = {?}",
                                     $new_name, $item['ax_id']);
                        ++$count;
                        break;
                    }
                }
            }
            if ($new_name) {
                continue;
            }
            foreach ($ax as $ax_key2 => $ax_name2) {
                if ($ax_name2 && strpos($ax_name2, $ax_name) !== false) {
                    if ($ax_name2 == 'de ' . $ax_name) {
                        $new_name = 'de ' . $item[$ax_key];
                    } elseif ($ax_name2 == "d'" . $ax_name) {
                        $new_name = "d'" . $item[$ax_key];
                    } elseif ($ax_name2 == 'du ' . $ax_name) {
                        $new_name = 'du ' . $item[$ax_key];
                    }

                    if ($new_name) {
                        XDB::execute("UPDATE  fusionax_anciens
                                         SET  $ax_key = {?}
                                       WHERE  ax_id = {?}",
                                     $new_name, $item['ax_id']);
                        ++$count;
                        break;
                    }
                }
            }
        }
    }

    return $count;
}

function fix_xorg_particles($ax, $xorg, $item)
{
    $count = 0;
    foreach ($ax as $ax_key => $ax_name) {
        if (!in_array($ax_name, $xorg)) {
            foreach ($xorg as $xorg_key => $xorg_name) {
                $new_name = '';
                if ($xorg_name && strpos($ax_name, $xorg_name) !== false) {
                    if ($ax_name == 'de ' . $xorg_name) {
                        $new_name = 'de ' . $item[$xorg_key];
                    } elseif ($ax_name == "d'" . $xorg_name) {
                        $new_name = "d'" . $item[$xorg_key];
                    } elseif ($ax_name == 'du ' . $xorg_name) {
                        $new_name = 'du ' . $item[$xorg_key];
                    }

                    if ($new_name) {
                        XDB::execute("UPDATE  profile_public_names
                                         SET  $xorg_key = {?}
                                       WHERE  pid = {?}",
                                     $new_name, $item['pid']);
                        ++$count;
                        break;
                    }
                }
            }
        }
    }

    return $count;
}

function fix_xorg_full_name($ax, $xorg, $item)
{
    $new_name = '';
    if (!in_array($ax['Nom_complet'], $xorg) && strpos($ax['Nom_complet'], $xorg['lastname_main']) !== false && $xorg['lastname_ordinary'] == '') {
        XDB::execute("UPDATE  profile_public_names
                         SET  lastname_main = {?}, lastname_ordinary = {?}
                       WHERE  pid = {?}",
                     $item['Nom_complet'], $item['lastname_main'], $item['pid']);
        return 1;
    }

    return 0;
}

function fix_xorg_ordinary_name($ax, $xorg, $item)
{
    $new_name = '';
    if (!in_array($ax['Nom_usuel'], $xorg) && $xorg['lastname_ordinary'] == '') {
        XDB::execute("UPDATE  profile_public_names
                         SET  lastname_ordinary = {?}
                       WHERE  pid = {?}",
                     $item['Nom_usuel'], $item['pid']);
        return 1;
    }

    return 0;
}

function format($string)
{
    $string = preg_replace('/\-/', ' ', $string);
    return preg_replace('/\s+/', ' ', $string);
}

$updates_count = 0;
$count = 0;
$total = count($res);
foreach($res as $item) {
    array_map('trim', $item);
    $ax = array(
        'Nom_patronymique' => format(mb_strtolower(replace_accent($item['Nom_patronymique']))),
        'Nom_usuel'        => format(mb_strtolower(replace_accent($item['Nom_usuel']))),
        'Nom_complet'      => format(mb_strtolower(replace_accent($item['Nom_complet'])))
    );
    $xorg = array(
        'lastname_initial'  => format(mb_strtolower(replace_accent($item['lastname_initial']))),
        'lastname_main'     => format(mb_strtolower(replace_accent($item['lastname_main']))),
        'lastname_ordinary' => format(mb_strtolower(replace_accent($item['lastname_ordinary'])))
    );

    $updates_count += fix_ax_particles($ax, $xorg, $item);
    $updates_count += fix_xorg_particles($ax, $xorg, $item);
    $updates_count += fix_xorg_full_name($ax, $xorg, $item);
    $updates_count += fix_xorg_ordinary_name($ax, $xorg, $item);
    printf("\r%u / %u",  $count, $total);
    ++$count;
}
printf("\r%u / %u\n\n",  $count, $total);

echo "Nombre de mises à jour effectuées : " . $updates_count . ".\n";

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
