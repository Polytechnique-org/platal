#!/usr/bin/php5
<?php
// WARNING: this script takes a few weeks to be executed completly, thus run it into a screen.

require_once 'connect.db.inc.php';
require_once '../../classes/address.php';

$globals->debug = 0; // Do not store backtraces.

echo "Tries to geocode addresses (due a bug in the previous release, all addresses must run once again). (1/2)\n";
$it = XDB::rawIterator('SELECT  *
                          FROM  profile_addresses
                      ORDER BY  pid, jobid, type, id');
$pid = 0;
$jobid = 0;
while ($address = new Address($it->next())) {
  $address->format(array(true, true));
  $address->delete();
  $address->save();
  if (!($pid == $address->pid && $jobid == $address->jobid)) {
    $pid = $address->pid;
    $jobid = $address->jobid;
    sleep(60);
  }
}

echo "Deletes duplicated addresses. (2/2)\n";
$it = XDB::rawIterator("SELECT  *
                          FROM  profile_addresses
                         WHERE  type = 'home'
                      ORDER BY  pid, id");
$addresses = array();
$duplicates = array();
$address = new Address($it->next());
$pid = $address->pid;
$addresses[] = $address;
$count = 1;
while ($address = new Address($it->next())) {
  if ($address->pid == $pid) {
    $address[] = $address;
    ++$count;
  } else {
    if ($count != 1) {
      for ($i = 0; $i < $count; ++$i) {
        for ($j = $i + 1; $j < $count; ++$j) {
          if ($addresses[$i]->text == $addresses[$j]->text) {
            $duplicates[$i] = true;
          }
        }
      }
      foreach ($duplicates as $key => $bool) {
        unset($addresses[$key]);
      }
      if (count($addresses) != $count) {
        Address::deleteAddresses($pid, 'home');
        $id = 0;
        foreach ($addresses as $address) {
          $address->setId($id);
          $address->save();
          ++$id;
        }
        XDB::execute('UPDATE  profile_merge_issues
                         SET  issues = REPLACE(issues, \'address\', \'\')
                       WHERE  pid = {?}', $pid);
      }
      unset($duplicates);
    }
    unset($addresses);
    $pid = $address->pid;
    $addresses[] = $address;
    $count = 1;
  }
}

echo "That's all folks!\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
