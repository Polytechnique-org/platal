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

function applis_options($current=0)
{
    $html = '<option value = "-1"></option>';
    $res  = XDB::iterator("SELECT  *
                             FROM  profile_education_enum
                         ORDER BY  name");
    while ($arr_appli = $res->next()) {
        $html .= '<option value="' . $arr_appli["id"] . '"';
        if ($arr_appli["id"]==$current) {
            $html .= " selected='selected'";
        }
        $html .= '>' . htmlspecialchars($arr_appli["name"]) . "</option>\n";
    }
    return $html;
}

/** pour appeller applis_options depuis smarty
 */
function _applis_options_smarty($params)
{
    if(!isset($params['selected'])) {
        $params['selected'] = 0;
    }
    return applis_options($params['selected']);
}
Platal::page()->register_function('applis_options', '_applis_options_smarty');

/** affiche un Array javascript contenant les types de chaque appli
 */
function applis_type()
{
    $html = "";
    $res = XDB::iterRow("SELECT  eduid, degreeid
                           FROM  profile_education_degree AS d
                     INNER JOIN  profile_education_enum   AS e ON (e.id = d.eduid)
                       ORDER BY  e.name");
    if ($appli_type = $res->next()) {
        $eduid = $appli_type['0'];
        $html .= "[";
        $html .= $appli_type['1'];
        $appli_type = $res->next();
        while ($appli_type['0'] == $eduid) {
            $html .= "," . $appli_type['1'];
            $appli_type = $res->next();
        }
        $html .= "]";
    }
    while ($appli_type) {
        $eduid = $appli_type['0'];
        $html .= ",\n[";
        $html .= $appli_type['1'];
        $appli_type = $res->next();
        while ($appli_type['0'] == $eduid) {
            $html .= "," . $appli_type['1'];
            $appli_type = $res->next();
        }
        $html .= "]";
    }
    return $html;
}
Platal::page()->register_function('applis_type', 'applis_type');

/** affiche tous les types possibles d'applis
 */
function applis_type_all()
{
    $html = "";
    $res = XDB::query("SELECT  id
                         FROM  profile_education_degree_enum
                     ORDER BY  id");
    return implode(',', $res->fetchColumn());
}
Platal::page()->register_function('applis_type_all', 'applis_type_all');

/** affiche les noms de tous les types possibles d'applis
 */
function applis_type_name()
{
    $html = "";
    $res = XDB::query("SELECT  degree
                           FROM  profile_education_degree_enum
                       ORDER BY  id");
    return '\'' . implode('\',\'', $res->fetchColumn()) . '\'';
}
Platal::page()->register_function('applis_type_name', 'applis_type_name');

/** formatte une ecole d'appli pour l'affichage
 */
function applis_fmt($name, $url, $degree, $grad_year, $field, $sexe, $long)
{
    $field = strtolower($field);
    $txt = "";

    if ($grad_year || $field) {
        $txt .= "<span  title=\"(";
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

function _applis_fmt($params, &$smarty)
{
    extract($params);
    return applis_fmt($name, $url, $degree, $grad_year, $field, $sexe, $long);
}
Platal::page()->register_function('applis_fmt', '_applis_fmt');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
