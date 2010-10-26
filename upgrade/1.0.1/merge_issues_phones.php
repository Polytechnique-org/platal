#!/usr/bin/php5
<?php
// WARNING: this script takes a few days to be executed completly, thus run it into a screen.

require_once 'connect.db.inc.php';
require_once '../../classes/phone.php';

$globals->debug = 0; // Do not store backtraces.

echo "Formats non formated phones. (1/2)\n";
$it = XDB::rawIterator("SELECT  search_tel AS search, display_tel AS display, comment, link_id,
                                tel_type AS type, link_type, tel_id AS id, pid, pub
                          FROM  profile_phones
                         WHERE  search_tel = '' OR search_tel IS NULL
                      ORDER BY  pid, link_id, tel_id");
while ($phone = new Phone($it->next())) {
  $phone->delete();
  $phone->save();
}

echo "Deletes duplicated phones. (2/2)\n";
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

echo "That's all folks!\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
