#!/usr/bin/php5
<?php
// WARNING: this script takes a few weeks to be executed completly, thus run it into a screen.

require_once 'connect.db.inc.php';
require_once '../../classes/phone.php';
require_once '../../classes/address.php';

$globals->debug = 0; // Do not store backtraces.

echo "Formats non formated phones.\n";
$it = XDB::rawIterator("SELECT  search_tel AS search, display_tel AS display, comment, link_id,
                                tel_type AS type, link_type, tel_id AS id, pid, pub
                          FROM  profile_phones
                         WHERE  search_tel = '' OR search_tel IS NULL
                      ORDER BY  pid, link_id, tel_id");
while ($phone = new Phone($it->next())) {
  $phone->delete();
  $phone->save();
}

echo "Deletes duplicated phones.\n";
$it = XDB::rawIterator("SELECT  search_tel AS search, display_tel AS display, comment, link_id,
                                tel_type AS type, link_type, tel_id AS id, pid, pub
                          FROM  profile_phones
                         WHERE  link_type = 'user'
                      ORDER BY  pid, tel_id");
$phones = array();
$duplicates = array();
$phone = new Phone($it->next());
$pid = $phone->pid();
$phones[] = $phone;
$count = 1;
while ($phone = new Phone($it->next())) {
  if ($phone->pid() == $pid) {
    $phone[] = $phone;
    ++$count;
  } else {
    if ($count != 1) {
      for ($i = 0; $i < $count; ++$i) {
        for ($j = $i + 1; $j < $count; ++$j) {
          if ($phones[$i]->search() == $phones[$j]->search()) {
            $duplicates[$i] = true;
          }
        }
      }
      foreach ($duplicates as $key => $bool) {
        unset($phones[$key]);
      }
      if (count($phones) != $count) {
        Phone::deletePhones($pid, 'user');
        $id = 0;
        foreach ($phones as $phone) {
          $phone->setId($id);
          $phone->save();
          ++$id;
        }
        XDB::execute('UPDATE  profile_merge_issues
                         SET  issues = REPLACE(issues, \'phone\', \'\')
                       WHERE  pid = {?}', $pid);
      }
      unset($duplicates);
    }
    unset($phones);
    $pid = $phone->pid();
    $phones[] = $phone;
    $count = 1;
  }
}

echo "Tries to geocode addresses (due a bug in the previous release, all addresses must run once again).\n";
$it = XDB::rawIterator('SELECT  *
                          FROM  profile_addresses
                      ORDER BY  pid, jobid, type, id');
$pid = 0;
$jobid = 0;
while ($address = $it->next()) {
  $address->format(array(true, true));
  $address->delete();
  $address->save();
  if (!($pid == $address->pid && $jobid == $address->jobid)) {
    $pid = $address->pid;
    $jobid = $address->jobid;
    sleep(60);
  }
}

echo "Deletes duplicated addresses.\n";
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

/* vim:set et sw=4 sts=4 ts=4: */
?>
