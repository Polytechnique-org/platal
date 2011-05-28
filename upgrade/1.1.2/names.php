#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';
require_once 'name.func.inc.php';

$globals->debug = 0; // Do not store backtraces.

$options = getopt('', array('perform-updates::'));
if (isset($options['perform-updates']) && $options['perform-updates'] == 'YES') {
    $perform_updates = true;
} else {
    $perform_updates = false;
}

$other_data = XDB::rawFetchOneCell("SELECT  COUNT(*)
                                      FROM  profile_name      AS pn
                                INNER JOIN  profile_name_enum AS pne ON (pn.typeid = pne.id)
                                     WHERE  pne.type = 'name_other' OR pne.type = 'firstname_other'");
if ($other_data) {
    print "Update this script to take 'name_other' and 'firstname_other' into account.";
    exit();
} else {
    $aliases = XDB::fetchAllAssoc('pid', "SELECT  pid, name
                                            FROM  profile_private_names
                                           WHERE  type = 'nickname'");
}

// This contains a firstname and a lastname, both can be either main or ordinary.
function update_main($data, $string, &$update)
{
    $matches = explode(' ', $string);
    $count = count($matches);
    $i = 0;

    for (; $i < $count + 1; ++$i) {
        $firstname = implode(' ', array_slice($matches, 0, $i + 1));
        $lastname = implode(' ', array_slice($matches, $i + 1));
        if ($firstname == $data['firstname_main'] || $firstname == $data['firstname_ordinary']) {
            if ($lastname == $data['lastname_ordinary']) {
                return true;
            }
            if ($lastname != $data['lastname_main'] && $lastname != $data['lastname_ordinary']) {
                $update[] = XDB::format('lastname_ordinary = {?}', $lastname);
                return true;
            }
            return false;
        }
        if ($lastname == $data['lastname_main'] || $lastname == $data['lastname_ordinary']) {
            if ($firstname != $data['firstname_main'] && $firstname != $data['firstname_ordinary']) {
                $update[] = XDB::format('firstname_ordinary = {?}', $firstname);
            }
            if ($lastname == $data['lastname_ordinary']) {
                return true;
            }
            return false;
        }
    }
    return false;
}

// This is detected by a starting 'M/Mme'. But it can also include a pseudonym.
function update_marital($data, $string, &$update)
{
    preg_match('/^([^,]+)(?:, (.*))?$/', $string, $matches);
    if ($matches[1] != $data['lastname_marital']) {
        $update[] = XDB::format('lastname_marital = {?}', $matches[1]);
    }
    if (count($matches) == 3 && $matches[2] != $data['pseudonym']) {
        $update[] = XDB::format('pseudonym = {?}', $matches[2]);
    }
}

function update_private($data, $string, $pid, &$aliases, $perform_updates)
{
    // We here assume there are no other last/firstnames as there do not seem to be any in the db.
    $string = substr($string, 1, strlen($string) - 2);
    $string = substr($string, 6, strlen($string) - 6);

    if (!isset($aliases[$pid])) {
        if ($perform_updates) {
            XDB::execute("INSERT INTO  profile_private_names (pid, type, id, name)
                               VALUES  ({?}, 'nickname', 0, {?})",
                         $pid, $string);
        } else {
            print $string . ' (alias for pid ' . $pid . ")\n";
        }
    } elseif ($aliases[$pid] != $string) {
        if ($perform_updates) {
            XDB::execute('UPDATE  profile_private_names
                             SET  name = {?}
                           WHERE  pid = {?} AND name = {?}',
                         $string, $pid, $aliases[$pid]);
        } else {
            print $string . ' (new alias for pid ' . $pid . ' replacing ' . $aliases[$pid] . ")\n";
        }

    }
}

// This can either be a main name or a pseudonym.
function update_plain($data, $string, &$update, $has_ordinary)
{
    $string = substr($string, 1, strlen($string) - 2);
    if ($string == $data['lastname_main']) {
        return true;
    }

    if ($string != $data['pseudonym']) {
        if ($has_ordinary) {
            $update[] = XDB::format('pseudonym = {?}', $string);
        } else {
            $update[] = XDB::format('lastname_main = {?}', $string);
        }
        return true;
    }
    return false;
}

$res = XDB::rawIterator('SELECT  pd.pid, pd.private_name, pn.lastname_main, pn.lastname_marital, pn.lastname_ordinary,
                                 pn.firstname_main, pn.firstname_ordinary, pn.pseudonym
                           FROM  profile_display      AS pd
                     INNER JOIN  profile_public_names AS pn ON (pd.pid = pn.pid)');

$pattern = '/^([^\(\)]+)(?: (\([^\(\)]+\)))?(?: (\([^\(\)]+\)))?(?: (\([^\(\)]+\)))?$/';
while ($data = $res->next()) {
    preg_match($pattern, $data['private_name'], $matches);
    $has_ordinary = false;

    $count = count($matches);
    $update = array();
    $has_ordinary = update_main($data, $matches[1], $update);
    for ($i = 2; $i < $count; ++$i) {
        if (preg_match('/^\((?:M|Mme) (.+)\)$/', $matches[$i], $pieces)) {
            update_marital($data, $pieces[1], $update);
        } elseif (preg_match('/^\((?:alias|autres prénoms :|autres noms :) .+\)$/', $matches[$i], $pieces)) {
            update_private($data, $matches[$i], $data['pid'], $aliases, $perform_updates);
        } else {
            $has_ordinary = update_plain($data, $matches[$i], $update, $has_ordinary);
        }
    }

    if (count($update)) {
        $set = implode(', ', $update);
        if ($perform_updates) {
            XDB::rawExecute('UPDATE  profile_public_names
                                SET  ' . $set . '
                              WHERE  pid = ' . $data['pid']);
        } else {
            print $set . ' (for pid ' . $data['pid'] . ")\n";
        }
    }
}

if ($perform_updates) {
    print "\nUpdates done.\n";
} else {
    print "\nIf this seems correct, relaunch this script with option --perform-updates=YES.\n";
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
