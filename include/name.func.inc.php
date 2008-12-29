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

function build_names_display($data)
{
    $data_array = explode(';;', $data);
    $n = count($data_array);
    $n--;
    for ($i = 0; $i < $n; $i++) {
        $searchname = explode(';', $data_array[$i]);
        if ($searchname[1] != 0) {
            list($particle, $name) = explode(' ', $searchname[2], 2);
            if (!$name) {
                list($particle, $name) = explode('\'', $searchname[2], 2);
            }
        } else {
            $particle = '';
            $name     = $searchname[2];
        }
        if (!isset($search_names[$searchname[0]])) {
            $search_names[$searchname[0]] = array($searchname[2], $name);
        } else {
            $search_names[$searchname[0]] = array_merge($search_names[$searchname[0]], array($name));
        }
    }
    $sn_types_public  = build_types('public');
    $sn_types_private = build_types('private');
    $full_name        = build_full_name($search_names, $sn_types_public);
    return build_public_name($search_names, $sn_types_public, $full_name) . ';' .
        build_private_name($search_names, $sn_types_private);
}

function build_types($pub)
{
    if ($pub == 'public') {
        $sql_pub = "AND FIND_IN_SET('public', flags)";
    } elseif ($pub == 'private') {
        $sql_pub = "AND NOT FIND_IN_SET('public', flags)";
    } else {
        $sql_pub = "";
    }
    $sql = "SELECT  id, name
              FROM  profile_name_search_enum
             WHERE  NOT FIND_IN_SET('not_displayed', flags)" . $sql_pub;
    $sn_types = XDB::iterator($sql);
    $types    = array();
    while ($sn_type = $sn_types->next()) {
        $types[$sn_type['name']] = $sn_type['id'];
    }
    return $types;
}

function build_full_name(&$search_names, &$sn_types)
{
    $name = "";
    if (isset($search_names[$sn_types['Nom usuel']])) {
        $name .= $search_names[$sn_types['Nom usuel']][0] . " ("
              . $search_names[$sn_types['Nom patronymique']][0] . ")";
    } else {
        $name .= $search_names[$sn_types['Nom patronymique']][0];
    }
    if (isset($search_names[$sn_types['Nom marital']])
        || isset($search_names[$sn_types['Pseudonyme (nom de plume)']])) {
        if (isset($search_names[$sn_types['Nom marital']])) {
            $user = S::user();
            if ($user->isFemale()) {
                $name .= " (Mme ";
            } else {
                $name .= " (M ";
            }
            $name .= $search_names[$sn_types['Nom marital']][0];
            if (isset($search_names[$sn_types['Pseudonyme (nom de plume)']])) {
                $name .= ", ";
            }
        }
        if (isset($search_names[$sn_types['Pseudonyme (nom de plume)']])) {
            $name .= $search_names[$sn_types['Pseudonyme (nom de plume)']][0];
        }
        $name .= ")";
    }
    return $name;
}

function build_public_name(&$search_names, &$sn_types, $full_name)
{
    return $search_names[$sn_types['Prénom']][0] . " " . $full_name;
}

function build_private_name(&$search_names, &$sn_types)
{
    $name = "";
    if (isset($search_names[$sn_types['Surnom']])
        || (isset($search_names[$sn_types['Autre prénom']])
        || isset($search_names[$sn_types['Autre nom']]))) {
        $name .= " (";
        if (isset($search_names[$sn_types['Surnom']])) {
            $name .= "alias " . $search_names[$sn_types['Surnom']][0];
            $i = 2;
            while (isset($search_names[$sn_types['Surnom']][$i])) {
                $name .= ", " . $search_names[$sn_types['Surnom']][$i];
                $i++;
            }
            if (isset($search_names[$sn_types['Autre prénom']])
                || isset($search_names[$sn_types['Autre nom']])) {
                $name .= ", ";
            }
        }
        if (isset($search_names[$sn_types['Autre prénom']])) {
            $name .= "autres prénoms : " . $search_names[$sn_types['Autre prénom']][0];
            $i = 2;
            while (isset($search_names[$sn_types['Autre prénom']][$i])) {
                $name .= ", " . $search_names[$sn_types['Autre prénom']][$i];
                $i++;
            }
            if (isset($search_names[$sn_types['Autre nom']])) {
                $name .= ", ";
            }
        }
        if (isset($search_names[$sn_types['Autre nom']])) {
            $name .= "autres noms : " . $search_names[$sn_types['Autre nom']][0];
            $i = 2;
            while (isset($search_names[$sn_types['Autre nom']][$i])) {
                $name .= ", " . $search_names[$sn_types['Autre nom']][$i];
                $i++;
            }
        }
        $name .= ")";
    }
    return $name;
}

function build_directory_name(&$search_names, &$sn_types, $full_name)
{
    return $full_name . " " . $search_names[$sn_types['Prénom']][0];
}

function short_name(&$search_names, &$sn_types)
{
    $name = "";
    if (isset($search_names[$sn_types['Nom usuel']])) {
        $name .= $search_names[$sn_types['Nom usuel']][0];
    } else {
        $name .= $search_names[$sn_types['Nom patronymique']][0];
    }
    $name = " ";
    if (isset($search_names[$sn_types['Prénom usuel']])) {
        $name .= $search_names[$sn_types['Prénom usuel']][0];
    } else {
        $name .= $search_names[$sn_types['Prénom']][0];
    }
    return $name;
}

function sort_name(&$search_names, &$sn_types)
{
    $name = "";
    if (isset($search_names[$sn_types['Nom usuel']])) {
        $name .= $search_names[$sn_types['Nom usuel']][1];
    } else {
        $name .= $search_names[$sn_types['Nom patronymique']][1];
    }
    $name .= $search_names[$sn_types['Prénom']][0];
    return $name;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
