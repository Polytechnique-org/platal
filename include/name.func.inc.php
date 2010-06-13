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

function build_javascript_names($data, $isFemale)
{
    $data_array = explode(';;', $data);
    $n = count($data_array);
    $n--;
    for ($i = 0; $i < $n; $i++) {
        $searchname = explode(';', $data_array[$i]);
        if (isset($search_names[$searchname[0]])) {
            $search_names[$searchname[0]][] = $searchname[1];
        } else {
            $search_names[$searchname[0]] = array('fullname' => $searchname[1]);
        }
    }

    $sn_types_public  = build_types('public');
    $sn_types_private = build_types('private');
    $full_name        = build_full_name($search_names, $sn_types_public, $isFemale);
    return build_public_name($search_names, $sn_types_public, $full_name) . ';' .
           build_private_name($search_names, $sn_types_private);
}

function build_display_names(&$display_names, $search_names, $isFemale, $private_name_end = null, &$alias = null)
{
    $sn_types_public  = build_types('public');
    $full_name        = build_full_name($search_names, $sn_types_public, $isFemale);
    $display_names['public_name']    = build_public_name($search_names, $sn_types_public, $full_name);
    $display_names['private_name']   = $display_names['public_name'] . $private_name_end;
    $display_names['directory_name'] = build_directory_name($search_names, $sn_types_public, $full_name);
    $display_names['short_name']     = build_short_name($search_names, $sn_types_public, $alias);
    $display_names['sort_name']      = build_sort_name($search_names, $sn_types_public);
}

function build_types($pub = null)
{
    if ($pub == 'public') {
        $sql_pub = "AND FIND_IN_SET('public', flags)";
    } elseif ($pub == 'private') {
        $sql_pub = "AND NOT FIND_IN_SET('public', flags)";
    } else {
        $sql_pub = "";
    }
    $sql = "SELECT  id, type, name
              FROM  profile_name_enum
             WHERE  NOT FIND_IN_SET('not_displayed', flags)" . $sql_pub;
    $sn_types = XDB::iterator($sql);
    $types    = array();
    while ($sn_type = $sn_types->next()) {
        if ($pub) {
            $types[$sn_type['type']] = $sn_type['id'];
        } else {
            $types[$sn_type['id']]   = $sn_type['name'];
        }
    }
    return $types;
}

function build_full_name(&$search_names, &$sn_types, $isFemale)
{
    $name = "";
    if (isset($search_names[$sn_types['lastname_ordinary']])) {
        $name .= $search_names[$sn_types['lastname_ordinary']]['fullname'] . " ("
              . $search_names[$sn_types['lastname']]['fullname'] . ")";
    } else {
        $name .= $search_names[$sn_types['lastname']]['fullname'];
    }
    if (isset($search_names[$sn_types['lastname_marital']])
        || isset($search_names[$sn_types['pseudonym']])) {
        $name .= " (";
        if (isset($search_names[$sn_types['lastname_marital']])) {
            if ($isFemale) {
                $name .= "Mme ";
            } else {
                $name .= "M ";
            }
            $name .= $search_names[$sn_types['lastname_marital']]['fullname'];
            if (isset($search_names[$sn_types['pseudonym']])) {
                $name .= ", ";
            }
        }
        if (isset($search_names[$sn_types['pseudonym']])) {
            $name .= $search_names[$sn_types['pseudonym']]['fullname'];
        }
        $name .= ")";
    }
    return $name;
}

function build_public_name(&$search_names, &$sn_types, $full_name)
{
    return $search_names[$sn_types['firstname']]['fullname'] . " " . $full_name;
}

function build_private_name(&$search_names, &$sn_types)
{
    $name = "";
    if (isset($search_names[$sn_types['nickname']])
        || (isset($search_names[$sn_types['name_other']])
        || isset($search_names[$sn_types['name_other']]))) {
        $name .= " (";
        if (isset($search_names[$sn_types['nickname']])) {
            $name .= "alias " . $search_names[$sn_types['nickname']]['fullname'];
            $i = 0;
            while (isset($search_names[$sn_types['nickname']][$i])) {
                $name .= ", " . $search_names[$sn_types['nickname']][$i];
                $i++;
            }
            if (isset($search_names[$sn_types['name_other']])
                || isset($search_names[$sn_types['firstname_other']])) {
                $name .= ", ";
            }
        }
        if (isset($search_names[$sn_types['firstname_other']])) {
            $name .= "autres prénoms : " . $search_names[$sn_types['firstname_other']]['fullname'];
            $i = 0;
            while (isset($search_names[$sn_types['firstname_other']][$i])) {
                $name .= ", " . $search_names[$sn_types['firstname_other']][$i];
                $i++;
            }
            if (isset($search_names[$sn_types['name_other']])) {
                $name .= ", ";
            }
        }
        if (isset($search_names[$sn_types['name_other']])) {
            $name .= "autres noms : " . $search_names[$sn_types['name_other']]['fullname'];
            $i = 0;
            while (isset($search_names[$sn_types['name_other']][$i])) {
                $name .= ", " . $search_names[$sn_types['name_other']][$i];
                $i++;
            }
        }
        $name .= ")";
    }
    return $name;
}

function build_directory_name(&$search_names, &$sn_types, $full_name)
{
    return $full_name . " " . $search_names[$sn_types['firstname']]['fullname'];
}

function build_short_name(&$search_names, &$sn_types, &$alias = null)
{
    if (isset($search_names[$sn_types['lastname_ordinary']])) {
        $lastname = $search_names[$sn_types['lastname_ordinary']]['fullname'];
    } else {
        $lastname = $search_names[$sn_types['lastname']]['fullname'];
    }
    if (isset($search_names[$sn_types['firstname_ordinary']])) {
        $firstname = $search_names[$sn_types['firstname_ordinary']]['fullname'];
    } else {
        $firstname = $search_names[$sn_types['firstname']]['fullname'];
    }
    if ($alias) {
        $alias = PlUser::makeUserName($firstname, $lastname);
    }
    return $firstname . " " . $lastname;
}

function build_sort_name(&$search_names, &$sn_types)
{
    $name = "";
    if (isset($search_names[$sn_types['lastname_ordinary']])) {
        $name .= $search_names[$sn_types['lastname_ordinary']]['name'];
    } else {
        $name .= $search_names[$sn_types['lastname']]['name'];
    }
    $name .= " " . $search_names[$sn_types['firstname']]['fullname'];
    return $name;
}

function set_profile_display(&$display_names, $pid)
{
    XDB::execute("UPDATE  profile_display
                     SET  public_name = {?}, private_name = {?},
                          directory_name = {?}, short_name = {?}, sort_name = {?}
                   WHERE  pid = {?}",
                 $display_names['public_name'], $display_names['private_name'],
                 $display_names['directory_name'], $display_names['short_name'],
                 $display_names['sort_name'], $pid);
}

function build_sn_pub($pid)
{
    $res = XDB::iterator("SELECT  CONCAT(sn.particle, sn.name) AS fullname, sn.typeid,
                                  sn.particle, sn.name, sn.id
                            FROM  profile_name      AS sn
                      INNER JOIN  profile_name_enum AS e  ON (e.id = sn.typeid)
                           WHERE  sn.pid = {?} AND NOT FIND_IN_SET('not_displayed', e.flags)
                                  AND FIND_IN_SET('public', e.flags)
                        ORDER BY  NOT FIND_IN_SET('always_displayed', e.flags), e.id, sn.name",
                         $pid);
    $sn_old = array();
    while ($old = $res->next()) {
        $sn_old[$old['typeid']] = array('fullname' => $old['fullname'],
                                        'name'     => $old['name'],
                                        'particle' => $old['particle'],
                                        'id'       => $old['id']);
    }
    return $sn_old;
}

/** Transform a name to its canonical value so it can be compared
 * to another form (different case, with accents or with - instead
 * of blanks).
 * @see compare_basename to compare
 */
function name_to_basename($value) {
    $value = mb_strtoupper(replace_accent($value));
    return preg_replace('/[^A-Z]/', ' ', $value);
}

/** Compares two strings and check if they are two forms of the
 * same name (different case, with accents or with - instead of
 * blanks).
 * @see name_to_basename to retreive the compared string
 */
function compare_basename($a, $b) {
    return name_to_basename($a) == name_to_basename($b);
}

function set_alias_names(&$sn_new, $sn_old, $pid, $uid, $update_new = false, $new_alias = null)
{
    $has_new = false;
    foreach ($sn_new as $typeid => $sn) {
        if (isset($sn['pub'])) {
            if (isset($sn_old[$typeid]) && ($sn_old[$typeid]['fullname'] == $sn['fullname'] && $update_new)) {
                XDB::execute("UPDATE  profile_name
                                 SET  particle = {?}, name = {?}, typeid = {?}
                               WHERE  id = {?} AND pid = {?}",
                             $sn['particle'], $sn['name'], $typeid, $sn_old[$typeid]['id'], $pid);
                unset($sn_old[$typeid]);
            } elseif ($update_new
                      || (isset($sn_old[$typeid]) && compare_basename($sn_old[$typeid]['fullname'], $sn['fullname']))) {
                XDB::execute("INSERT INTO  profile_name (particle, name, typeid, pid)
                                   VALUES  ({?}, {?}, {?}, {?})",
                             $sn['particle'], $sn['name'], $typeid, $pid);
                unset($sn_old[$typeid]);
            } else {
                $has_new = true;
            }
        } else {
            if ($sn['fullname'] != '') {
                XDB::execute("INSERT INTO  profile_name (particle, name, typeid, pid)
                                   VALUES  ('', {?}, {?}, {?})",
                             $sn['fullname'], $typeid, $pid);
            }
            $i = 0;
            while (isset($sn[$i])) {
                XDB::execute("INSERT INTO  profile_name (particle, name, typeid, pid)
                                   VALUES  ('', {?}, {?}, {?})",
                             $sn[$i], $typeid, $pid);
                $i++;
            }
        }
    }
    if (count($sn_old) > 0) {
        if (!$update_new) {
            $has_new = true;
            foreach ($sn_old as $typeid => $sn) {
                XDB::execute("INSERT INTO  profile_name (particle, name, typeid, pid)
                                   VALUES  ({?}, {?}, {?}, {?})",
                             $sn['particle'], $sn['name'], $typeid, $pid);
            }
        } else {
            foreach ($sn_old as $typeid => $sn) {
                XDB::execute("DELETE FROM  profile_name
                                    WHERE  pid = {?} AND id = {?}",
                             $pid, $sn['id']);
            }
        }
    }
    if ($update_new) {
        XDB::execute("DELETE FROM  aliases
                            WHERE  FIND_IN_SET('usage', flags) AND uid = {?}",
                     $uid);
    }
    if ($new_alias) {
        XDB::execute("INSERT INTO  aliases (alias, type, flags, uid)
                           VALUES  ({?}, 'alias', 'usage', {?})",
                     $new_alias, $uid);
    }
    Profile::rebuildSearchTokens($pid);
    return $has_new;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
