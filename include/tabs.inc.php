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


$tabname_array = Array(
    "general"  => "Informations\ngénérales",
    "adresses" => "Adresses\npersonnelles",
    "poly"     => "Informations\npolytechniciennes",
    "emploi"   => "Informations\nprofessionnelles",
    "skill"    => "Compétences\ndiverses",
    "mentor"   => "Mentoring"
);
    
$opened_tab = 'general';

$page->assign("onglets",$tabname_array);
$page->assign("onglet_last",'mentor');

function get_last_tab(){
    end($GLOBALS['tabname_array']);
    return key($GLOBALS['tabname_array']);
}

function get_next_tab($tabname){
    global $tabname_array;
    reset($tabname_array);
    $marker = false;
    while(list($current_tab,$current_tab_desc) = each($tabname_array)){
        if($current_tab == $tabname){
            $res = key($tabname_array);// each() sets key to the next element
            if($res != NULL)// if it was the last call of each(), key == NULL => we return the first key
                return $res;
            else{
                reset($tabname_array);
                return key($tabname_array);
            }
        }
    }
    // We should not arrive to this point, but at least, we return the first key
    reset($tabname_array);
    return key($tabname_array);
}

?>
