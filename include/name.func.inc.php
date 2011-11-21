<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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

// Some particles should not be capitalized (cf "ORTHOTYPO", by Jean-Pierre
// Lacroux).
// Note that some of them are allowed to use capital letters in some cases,
// for instance "De" in American English.
static $particles = array('d', 'de', 'an', 'auf', 'von', 'von dem',
                          'von der', 'zu', 'of', 'del', 'de las',
                          'de les', 'de los', 'las', 'los', 'y', 'a',
                          'da', 'das', 'do', 'dos', 'af', 'av');


function build_javascript_names($data, $isFemale)
{
    $names = array();
    foreach (explode(';-;', $data) as $key => $item) {
        $names[$key] = explode(';', $item);
    }
    $lastnames = array(
        'lastname_main'     => $names[0][0],
        'lastname_ordinary' => $names[0][1],
        'lastname_marital'  => $names[0][2],
        'pseudonym'         => $names[0][3]
    );
    $firstnames = array(
        'firstname_main'     => $names[1][0],
        'firstname_ordinary' => $names[1][1]
    );
    $private_names_count = intval(count($names[2]) / 2);
    $private_names = array();
    for ($i = 0; $i < $private_names_count; ++$i) {
        $private_names[] = array('type' => $names[2][2 * $i], 'name' => $names[2][2 * $i + 1]);
    }

    return build_first_name($firstnames) . ' ' . build_full_last_name($lastnames, $isFemale) . ';' . build_private_name($private_names);
}

function build_email_alias(array $public_names)
{ 
    return PlUser::makeUserName(build_first_name($public_names), build_short_last_name($public_names));
}

function build_display_names(array $public_names, array $private_names, $isFemale)
{
    $short_last_name = build_short_last_name($public_names);
    $full_last_name = build_full_last_name($public_names, $isFemale);
    $private_last_name_end = build_private_name($private_names);
    $firstname = build_first_name($public_names);

    $display_names = array();
    $display_names['public_name']    = build_full_name($firstname, $full_last_name);
    $display_names['private_name']   = $display_names['public_name'] . $private_last_name_end;
    $display_names['directory_name'] = build_directory_name($firstname, $full_last_name);
    $display_names['short_name']     = build_full_name($firstname, $short_last_name);
    $display_names['sort_name']      = build_sort_name($firstname, $short_last_name);

    return $display_names;
}

function build_short_last_name(array $lastnames)
{
    return ($lastnames['lastname_ordinary'] == '') ? $lastnames['lastname_main'] : $lastnames['lastname_ordinary'];
}

function build_full_last_name(array $lastnames, $isFemale)
{
    if ($lastnames['lastname_ordinary'] != '') {
        $name = $lastnames['lastname_ordinary'] . ' (' . $lastnames['lastname_main'] . ')';
    } else {
        $name = $lastnames['lastname_main'];
    }
    if ($lastnames['lastname_marital'] != '' || $lastnames['pseudonym'] != '') {
        $name .= ' (';
        if ($lastnames['lastname_marital'] != '') {
            $name .= ($isFemale ? 'Mme ' : 'M ') . $lastnames['lastname_marital'];
            $name .= (($lastnames['pseudonym'] == '') ? '' : ', ');
        }
        $name .= (($lastnames['pseudonym'] == '')? '' : $lastnames['pseudonym']) . ')';
    }
    return $name;
}

function build_first_name(array $firstnames)
{
    return ($firstnames['firstname_ordinary'] ? $firstnames['firstname_ordinary'] : $firstnames['firstname_main']);
}

function build_private_name(array $private_names)
{
    if (is_null($private_names) || count($private_names) == 0) {
        return '';
    }

    static $types = array('nickname' => 'alias ', 'firstname' => 'autres prénoms : ', 'lastname' => 'autres noms : ');
    $names_sorted = array('nickname' => array(), 'firstname' => array(), 'lastname' => array());

    foreach ($private_names as $private_name) {
        $names_sorted[$private_name['type']][] = $private_name['name'];
    }

    $names_array = array();
    foreach ($names_sorted as $type => $names) {
        if (count($names)) {
            $names_array[] = $types[$type] . implode(', ', $names);
        }
    }

    return ' (' . implode(', ', $names_array) . ')';
}

function build_directory_name($firstname, $lastname)
{
    if ($firstname == '') {
        return mb_strtoupper($lastname);
    }
    return mb_strtoupper($lastname) . ' ' . $firstname;
}

function build_full_name($firstname, $lastname)
{
    if ($firstname == '') {
        return $lastname;
    }
    return $firstname . ' ' . $lastname;
}

// Returns the name on which the sort is performed, according to French
// typographic rules.
function build_sort_name($firstname, $lastname)
{
    // Remove uncapitalized particles.
    $particles = "/^(d'|(" . implode($particles, '|') . ') )/';
    $name = preg_replace($particles, '', $lastname);
    // Mac must also be uniformized.
    $lastname = preg_replace("/^(Mac|Mc)(| )/", 'Mac', $name);

    if ($firstname == '') {
        return $lastname;
    }
    return $lastname . ' ' . $firstname;
}

/** Splits a name into tokens, as used in search_name.
 * Used for search_name rebuilding and for queries.
 */
function split_name_for_search($name) {
    return preg_split('/[[:space:]\'\-]+/', strtolower(replace_accent($name)),
                      -1, PREG_SPLIT_NO_EMPTY);
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

function update_account_from_profile($uid)
{
    XDB::execute("UPDATE  accounts             AS a
              INNER JOIN  account_profiles     AS ap  ON (ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms))
              INNER JOIN  profile_public_names AS ppn ON (ppn.pid = ap.pid)
              INNER JOIN  profile_display      AS pd  ON (pd.pid = ap.pid)
                     SET  a.lastname = IF(ppn.lastname_ordinary = '', ppn.lastname_main, ppn.lastname_ordinary),
                          a.firstname = IF(ppn.firstname_ordinary = '', ppn.firstname_main, ppn.firstname_ordinary),
                          a.full_name = pd.short_name, a.directory_name = pd.directory_name
                   WHERE  a.uid = {?}",
                 $uid);
}

function update_display_names(Profile $profile, array $public_names, array $private_names = null)
{
    if (is_null($private_names)) {
        $private_names = XDB::fetchAllAssoc('SELECT  type, name
                                               FROM  profile_private_names
                                              WHERE  pid = {?}
                                           ORDER BY  type, id',
                                            $profile->id());
    }
    $display_names = build_display_names($public_names, $private_names, $profile->isFemale());

    XDB::execute('UPDATE  profile_display
                     SET  public_name = {?}, private_name = {?},
                          directory_name = {?}, short_name = {?}, sort_name = {?}
                   WHERE  pid = {?}',
                 $display_names['public_name'], $display_names['private_name'],
                 $display_names['directory_name'], $display_names['short_name'],
                 $display_names['sort_name'], $profile->id());

    Profile::rebuildSearchTokens($profile->id(), false);

    if ($profile->owner()) {
        update_account_from_profile($profile->owner()->id());
    }
}

function update_public_names($pid, array $public_names)
{
    XDB::execute('UPDATE  profile_public_names
                     SET  lastname_main = {?}, lastname_marital = {?}, lastname_ordinary = {?},
                          firstname_main = {?}, firstname_ordinary = {?}, pseudonym = {?}
                   WHERE  pid = {?}',
                 $public_names['lastname_main'], $public_names['lastname_marital'], $public_names['lastname_ordinary'],
                 $public_names['firstname_main'], $public_names['firstname_ordinary'], $public_names['pseudonym'], $pid);
}

// Returns the @p name with all letters in lower case, but the first one.
function mb_ucfirst($name)
{
    return mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
}

// Capitalizes the @p name using French typographic rules. Returns
// false when capitalization rule is not known for the name format.
function capitalize_name($name)
{
    // Some suffixes should not be captitalized either, eg 's' in Bennett's.
    static $suffixes = array('h', 's', 't');

    // Extracts the first token of the name.
    if (!preg_match('/^(\pL+)(([\' -])(.*))?$/ui', $name, $m)) {
        return false;
    }

    $token = mb_strtolower($m[1]);
    $separator = (isset($m[3]) ? $m[3] : false);
    $tail = (isset($m[4]) ? $m[4] : false);

    // Special case for "Malloc'h".
    if ($separator == "'" && in_array(strtolower($tail[0]), $suffixes) &&
        (strlen($tail) == 1 || $tail[1] == ' ')) {
        $token .= "'" . strtolower($tail[0]);
        $separator = (strlen($tail) == 1 ? false : $tail[1]);
        $tail = (strlen($tail) > 2 ? substr($tail, 2) : false);
    }

    // Capitalizes the first token.
    if (!in_array($token, $particles)) {
        $token = mb_ucfirst($token);
    }

    // Capitalizes the tail of the name.
    if ($tail) {
        if (($tail = capitalize_name($tail))) {
            return $token . $separator . $tail;
        }
        return false;
    }

    return $token . $separator;
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
