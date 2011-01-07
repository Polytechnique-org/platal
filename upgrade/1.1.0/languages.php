#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';

$globals->debug = 0; // Do not store backtraces.

// First build the list provided by the iso codes.
$list = array();
exec('isoquery --iso=639', $list);

foreach ($list as $key => $item) {
    $array = explode("\t", $item);
    unset($list[$key]);
    $list[$array[0]] = array();
    foreach (array('iso_639_2t', 'iso_639_1') as $i => $field) {
        $list[$array[0]][$field] = $array[$i + 1];
    }
}

$res = XDB::iterator('SELECT  iso_639_2b
                        FROM  profile_langskill_enum
                       WHERE  iso_639_2b IN {?}',
                     array_keys($list));
while ($item = $res->next()) {
    $id = $item['iso_639_2b'];
    XDB::execute('UPDATE  profile_langskill_enum
                     SET  iso_639_2t = {?}, iso_639_1 = {?}
                   WHERE  iso_639_2b = {?}',
                 $list[$id]['iso_639_2t'], (($list[$id]['iso_639_1']) ? $list[$id]['iso_639_1'] : null), $id);
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
