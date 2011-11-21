#!/usr/bin/php5
<?php
include 'connect.db.inc.php';
include '../../include/name.func.inc.php';

// Returns the lower-cased name; if proper capitalization rule was not known,
// warns the user, and returns the initial name.
function capitalize_name_checked($name)
{
    $capitalized = capitalize_name($name);
    if (!$capitalized) {
        echo "WARNING: Unable to capitalize '$name'.\n";
        return $name;
    }
    if (mb_strtolower($name, 'UTF-8') != mb_strtolower($capitalized, 'UTF-8')) {
        echo "WARNING: Capitalization of '$name' is unexpected: '$capitalized'\n";
        return $name;
    }

    return $capitalized;
}

// Returns true iff the @p name looks like it should be properly recapitalized.
function needs_conversion($name)
{
    if (strlen($name) == 0) {
        return false;
    }
    if ($name == mb_strtoupper($name, 'UTF-8')) {
        return true;
    }

    $name_length = mb_strlen($name, 'UTF-8');
    $name_capitals = preg_replace('/\P{Lu}/u', '', $name);
    return (mb_strlen($name_capitals, 'UTF-8') > 0.4 * $name_length);
}

// Retrieves all the names to convert.
$conversions = 0;
$names = XDB::iterRow('SELECT  pid, lastname_initial, lastname_main, lastname_marital, lastname_ordinary,
                               firstname_initial, firstname_main, firstname_ordinary, pseudonym
                         FROM  profile_public_names');
$name_list = array('lastname_initial', 'lastname_main', 'lastname_marital', 'lastname_ordinary',
                   'firstname_initial', 'firstname_main', 'firstname_ordinary', 'pseudonym');
while ($item = $names->next()) {
    foreach ($name_list as $type) {
        if ($item[$type] != '') {
            $item[$type] = capitalize_name_checked($item[$type]);
        }
    }

    XDB::execute('UPDATE  profile_public_names
                     SET  lastname_initial = {?}, lastname_main = {?}, lastname_marital = {?}, lastname_ordinary = {?},
                          firstname_initial = {?}, firstname_main = {?}, firstname_ordinary = {?}, pseudonym = {?}
                   WHERE  pid = {?}',
                 $item['lastname_initial'], $item['lastname_main'], $item['lastname_marital'], $item['lastname_ordinary'],
                 $item['firstname_initial'], $item['firstname_main'], $item['firstname_ordinary'], $item['pseudonym'],
                 $item['pid']);
    $profile = Profile::get($pid);
    update_display_names($profile, $item);

    $conversions++;
    unset($item, $profile);
}

echo "$conversions names from profiles properly recapitalized.\n";

$conversions = 0;
$names = XDB::iterRow('SELECT  uid, firstname, lastname
                         FROM  accounts
                        WHERE  NOT EXISTS (SELECT  1
                                             FROM  account_profiles
                                            WHERE  account_profiles.uid = accounts.uid)');

while ($item = $names->next()) {
    $lastname = capitalize_name_checked($item['lastname']);
    $firstname = capitalize_name_checked($item['firstname']);

    $full_name = build_full_name($firstname, $lastname);
    $directory_name = build_directory_name($firstname, $lastname);
    $sort_name = build_sort_name($firstname, $lastname);

    XDB::execute('UPDATE  accounts
                     SET  firstname = {?}, lastname = {?}, full_name = {?}, directory_name = {?}, sort_name = {?}
                   WHERE  uid = {?}',
                 $firstname, $lastname, $full_name, $directory_name, $sort_name);

    $conversions++;
    unset($item);
}

echo "$conversions names from accounts properly recapitalized.\n";

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
