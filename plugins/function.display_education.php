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


function display_education($name, $url, $degree, $gradYear, $field, $program, $full)
{
    $field = strtolower($field);
    $txt = '';

    if (($degree != 'Ing.') && ($degree != 'Dipl.')) {
        $txt .= $degree;
        if ($name) {
            $txt .= ' ';
        }
    }
    if ($url != '') {
        $txt .= '<a href="' . $url . '" target="_blank">' . $name . '</a>';
    } else {
        $txt .= $name;
    }

    if ($gradYear || $field || $program) {
        $details = '';
        if ($program) {
            $details .= $program;
            if ($gradYear || $field) {
                $details .= ', ';
            }
        }
        if ($gradYear) {
            $details .= $gradYear;
            if ($field) {
                $details .= ', ';
            }
        }
        if ($field) {
            $details .= $field;
        }

        if ($full) {
            $txt .= ' <small>(' . $details . ')</small>';
        } else {
            $txt = '<span title="' . $details . '">' . $txt . '</span>';
        }
    }

    return $txt;
}

function smarty_function_display_education($params, $smarty)
{
    $params = new PlDict($params);
    $edu = $params->v('edu');
    return display_education(($edu->school_short == '') ?  $edu->school : $edu->school_short,
                             $edu->school_url, $edu->degree_short, $edu->grad_year,
                             $edu->field, $edu->program, $params->b('full'));
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
