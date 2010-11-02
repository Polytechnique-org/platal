#!/usr/bin/php5
<?php
// WARNING: this script takes a few weeks to be executed completly, thus run it into a screen.

require_once 'connect.db.inc.php';
require_once '../../classes/address.php';

$globals->debug = 0; // Do not store backtraces.

print "Tries to geocode addresses (due a bug in the previous release, all addresses must run once again). (1/2)\n";
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

print "Deletes duplicated addresses. (2/2)\n";
$pids = XDB::rawFetchColumn("SELECT  DISTINCT(pid)
                               FROM  profile_addresses AS a1
                              WHERE  type = 'home' AND EXISTS (SELECT  *
                                                                 FROM  profile_addresses AS a2
                                                                WHERE  a2.type = 'home' AND a2.pid = a1.pid AND a2.id != a1.id)
                           ORDER BY  pid");
$total = count($pids);
$done = 0;
$aux = 0;
$deleted = 0;
$addresses = array();
$rawAddresses = array();
$duplicates = array();
foreach ($pids as $pid) {
    $count = 0;
    $it = Address::iterate(array($pid), array(Address::LINK_PROFILE), array(0));
    while ($item = $it->next()) {
        $addresses[] = $item;
        $rawAddresses[] = preg_replace('/[^a-zA-Z0-9]/', '', replace_accent($item->text));
        ++$count;
    }
    for ($i = 0; $i < $count; ++$i) {
        for ($j = $i + 1; $j < $count; ++$j) {
            if ($rawAddresses[$i] == $rawAddresses[$j]) {
                $duplicates[$i] = true;
            }
        }
    }
    foreach ($duplicates as $key => $bool) {
        unset($addresses[$key]);
    }
    if (count($addresses) != $count) {
        $deleted += ($count - count($addresses));
        Address::deleteAddresses($pid, 'home');
        $id = 0;
        foreach ($addresses as $address) {
            $address->setId($id);
            $address->save();
            ++$id;
        }
        XDB::execute('UPDATE IGNORE  profile_merge_issues
                                SET  issues = REPLACE(issues, \'address\', \'\')
                              WHERE  pid = {?}', $pid);
    }
    unset($rawAddresses);
    unset($addresses);
    unset($duplicates);

    ++$done;
    ++$aux;
    if ($aux == 100) {
        $aux = 0;
        printf("\r%u / %u",  $done, $total);
    }
}
printf("\r%u / %u",  $done, $total);
print "\n$deleted addresses deleted.\n\n";

print "That's all folks!\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
