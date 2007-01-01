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


$GLOBALS['tabname_array'] = Array(
    "general"  => "Général",
    "adresses" => "Adresses\npersonnelles",
    "poly"     => "Groupes X\nBinets",
    "deco"     => "Décorations\nMédailles",
    "emploi"   => "Informations\nprofessionnelles",
    "skill"    => "Compétences\ndiverses",
    "mentor"   => "Mentoring"
);

$page->assign('onglets', $GLOBALS['tabname_array']);

function get_next_tab($tabname) {
    $tabname_array = $GLOBALS['tabname_array'];

    reset ($tabname_array);
    while (list($current_tab, ) = each($tabname_array)) {
        if ($current_tab == $tabname){
            $res = key($tabname_array);// each() sets key to the next element
            if (is_null($res)) {
                reset($tabname_array);
                return key($tabname_array);
            }
            return $res;
        }
    }

    return null;
}

?>
