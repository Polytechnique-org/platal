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

function education_options($current = 0)
{
    $html = '<option value="-1">&nbsp;</option>';
    $res  = XDB::iterator("SELECT  e.id AS id, gc.country,
                                   IF(CHAR_LENGTH(e.name) > 76, e.abbreviation, e.name) AS name
                             FROM  profile_education_enum AS e
                        LEFT JOIN  geoloc_countries       AS gc ON (e.country = gc.iso_3166_1_a2)
                     WHERE EXISTS  (SELECT  *
                                      FROM  profile_education_degree AS d
                                     WHERE  e.id = d.eduid) AND e.name != {?}
                         ORDER BY  gc.country, e.name",
                          Profile::EDU_X);
    $country = "";
    while ($arr_edu = $res->next()) {
        if ($arr_edu["country"] != $country) {
            if ($country) {
                $html .= '</optgroup>';
            }
            $country = $arr_edu["country"];
            $html .= '<optgroup label="' . $country . '">';
        }
        $html .= '<option value="' . $arr_edu["id"] . '"';
        if ($arr_edu["id"] == $current) {
            $html .= " selected='selected'";
        }
        $html .= '>' . htmlspecialchars($arr_edu["name"]) . "</option>\n";
    }
    if ($country) {
        $html .= '</optgroup>';
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
    $html  = '';
    $res = XDB::iterRow("SELECT  eduid, degreeid
                           FROM  profile_education_degree
                       ORDER BY  eduid");
    $edu_degree = $res->next();
    for ($eduid = 1; $edu_degree; ++$eduid) {
        $html .= '[';
        if ($edu_degree['0'] == $eduid) {
            $html .= $edu_degree['1'];
            $edu_degree = $res->next();
            while ($edu_degree['0'] == $eduid) {
                $html .= ',' . $edu_degree['1'];
                $edu_degree = $res->next();
            }
        }
        $html .= ']';
        if ($edu_degree) {
            $html .= ",\n";
        }
    }
    return $html;
}
Platal::page()->register_function('education_degree', 'education_degree');

/** affiche tous les types possibles de diplômes
 */
function education_degree_all()
{
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
    $res = XDB::query("SELECT  degree
                         FROM  profile_education_degree_enum
                     ORDER BY  id");
    return '"' . implode('","', $res->fetchColumn()) . '"';
}
Platal::page()->register_function('education_degree_name', 'education_degree_name');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
