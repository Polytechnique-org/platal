<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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


function display_education($name, $url, $degree, $grad_year, $field, $program, $sexe, $long)
{
    $field = strtolower($field);
    $txt = "";

    if ($grad_year || $field || $program) {
        $txt .= "<span title=\"(";
        if ($program) {
            $txt .= $program;
            if ($grad_year || $field) {
                $txt .= ", ";
            }
        }
        if ($grad_year) {
            if ($sexe) {
                $txt .= "diplômée en $grad_year";
            } else {
                $txt .= "diplômé en $grad_year";
            }
            if ($field) {
                $txt .= ", ";
            }
        }
        if ($field) {
            $txt .= "domaine : $field";
        }
        $txt .= ")\">";
    }

    if (($degree != "Lic.") || ($long)) {
        if (($degree != "Ing.") && ($degree != "Dipl.")) {
            $txt .= $degree;
        }
        if ($name) {
            $txt .= ' ';
        }
        if ($url != '') {
            $txt .= "<a href=\"$url\" onclick=\"return popup(this)\">$name</a>";
        } else {
            $txt .= $name;
        }
    }
    if ($grad_year || $field || $program) {
        $txt .= "</span>";
    }

    return $txt;
}

function smarty_function_display_education($params, &$smarty)
{
    $params  = new PlDict($params);
    $edu     = $params->v('edu');
    if (!$params->has('sex')) {
        $profile = $params->v('profile');
        $sex = $profile->isFemale();
    } else {
        $sex = $params->b('sex');
    }
    return display_education(($edu->school_short == '') ?  $edu->school : $edu->school_short,
                             $edu->school_url, $edu->degree_short, $edu->grad_year,
                             $edu->field, $edu->program, $sex, $params->b('long'));
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
