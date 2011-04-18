#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';

$globals->debug = 0; // Do not store backtraces.

$it = XDB::rawIterator('SELECT  uid, full_name, email, type
                          FROM  accounts
                         WHERE  type NOT IN (\'x\', \'master\', \'phd\')');
$total = $it->total();
$done = 0;

while ($item = $it->next()) {
    if ($item['type'] == 'virtual') {
        $firstname = '';
        $lastname = $item['full_name'];
    } elseif ($item['full_name'] && strpos(' ', $item['full_name'])) {
        list($firstname, $lastname) = explode(' ', $item['full_name']);
    } else {
        list($local_part, ) = explode('@', strtolower($item['email']));
        $parts = explode('.', $local_part);
        if (count($parts) == 1) {
            $lastname = ucfirst($local_part);
            $firstname = '';
        } else {
            $firstname = ucfirst($parts[0]);
            $lastname = ucwords(implode(' ', array_slice($parts, 1)));
        }
    }

    XDB::execute('UPDATE  accounts
                     SET  firstname = {?}, lastname = {?}
                   WHERE  uid = {?}',
                 $firstname, $lastname, $item['uid']);
    ++$done;
    printf("\r%u / %u",  $done, $total);
}
print "\n\nDone.\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
