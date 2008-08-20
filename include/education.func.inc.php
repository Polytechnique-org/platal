<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

function education_options($current = 0)
{
    $html = '<option value="-1"></option>';
    $res  = XDB::iterator("SELECT  *
                             FROM  profile_education_enum
                         ORDER BY  name");
    while ($arr_edu = $res->next()) {
        $html .= '<option value="' . $arr_edu["id"] . '"';
        if ($arr_edu["id"] == $current) {
            $html .= " selected='selected'";
        }
        $html .= '>' . htmlspecialchars($arr_edu["name"]) . "</option>\n";
    }
    return $html;
}

/** pour appeller education_options depuis smarty
 */
function _education_options_smarty($params)
{
    if(!isset($params['selected'])) {
        $params['selected'] = 0;
    }
    return education_options($params['selected']);
}
Platal::page()->register_function('education_options', '_education_options_smarty');

/** affiche un Array javascript contenant les diplômes de chaque formation
 */
function education_degree()
{
    $html = "";
    $res = XDB::iterRow("SELECT  eduid, degreeid
                           FROM  profile_education_degree AS d
                     INNER JOIN  profile_education_enum   AS e ON (e.id = d.eduid)
                       ORDER BY  e.name");
    if ($edu_degree = $res->next()) {
        $eduid = $edu_degree['0'];
        $html .= "[";
        $html .= $edu_degree['1'];
        $edu_degree = $res->next();
        while ($edu_degree['0'] == $eduid) {
            $html .= "," . $edu_degree['1'];
            $edu_degree = $res->next();
        }
        $html .= "]";
    }
    while ($edu_degree) {
        $eduid = $edu_degree['0'];
        $html .= ",\n[";
        $html .= $edu_degree['1'];
        $edu_degree = $res->next();
        while ($edu_degree['0'] == $eduid) {
            $html .= "," . $edu_degree['1'];
            $edu_degree = $res->next();
        }
        $html .= "]";
    }
    return $html;
}
Platal::page()->register_function('education_degree', 'education_degree');

/** affiche tous les types possibles de diplômes
 */
function education_degree_all()
{
    $html = "";
    $res = XDB::query("SELECT  id
                         FROM  profile_education_degree_enum
                     ORDER BY  id");
    return implode(',', $res->fetchColumn());
}
Platal::page()->register_function('education_degree_all', 'education_degree_all');

/** affiche les noms de tous les diplômes possibles
 */
function education_degree_name()
{
    $html = "";
    $res = XDB::query("SELECT  degree
                           FROM  profile_education_degree_enum
                       ORDER BY  id");
    return '\'' . implode('\',\'', $res->fetchColumn()) . '\'';
}
Platal::page()->register_function('education_degree_name', 'education_degree_name');

/** formatte une formation pour l'affichage
 */
function education_fmt($name, $url, $degree, $grad_year, $field, $program, $sexe, $long)
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
            $txt .= "domaine : $field)\">";
        }
    }

    if (($degree != "Licence") || ($long)) {
        if (($degree != "Ingénieur") && ($degree != "Diplôme")) {
            $txt .= $degree;
        }
        if ($name != "Université") {
            if ($name) {
                $txt .= ' ';
            }
            if ($url != ' ') {
                $txt .= "<a href=\"$url\" onclick=\"return popup(this)\">$name</a>";
            } else {
                $txt .= $name;
            }
        }
    }
    $txt .= "</span>";

    return $txt;
}

function _education_fmt($params, &$smarty)
{
    extract($params);
    return education_fmt($name, $url, $degree, $grad_year, $field, $program, $sexe, $long);
}
Platal::page()->register_function('education_fmt', '_education_fmt');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
