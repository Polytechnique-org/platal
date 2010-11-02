#!/usr/bin/php5
<?php
// WARNING: this script takes a few weeks to be executed completly, thus run it into a screen.

require_once 'connect.db.inc.php';
require_once '../../classes/address.php';

$globals->debug = 0; // Do not store backtraces.

print "Tries to geocode addresses (due a bug in the previous release, all addresses must run once again).\n";
$time = XDB::fetchOneCell('SELECT  COUNT(distinct(pid), jobid)
                             FROM  profile_addresses');
$time = ceil($time / 60 / 24);
print "It will approximately take $time days.\n";

$it = XDB::rawIterator('SELECT  *
                          FROM  profile_addresses
                      ORDER BY  pid, jobid, type, id');
$total = $it->total();
$i = 0;
$j = 0;
printf("\r%u / %u",  $i, $total);
$pid = 0;
$jobid = 0;
while ($item = $it->next()) {
    $address = new Address($item);
    $address->format(array(true, true));
    $address->delete();
    $address->save();
    if (!($pid == $address->pid && $jobid == $address->jobid)) {
        $pid = $address->pid;
        $jobid = $address->jobid;
        sleep(60);
    }

    ++$i;
    ++$j;
    if ($j == 10) {
        $j = 0;
        printf("\r%u / %u",  $i, $total);
    }
}
printf("\r%u / %u",  $i, $total);
print "\nGeocoding done.\n\n";
print "That's all folks!\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
