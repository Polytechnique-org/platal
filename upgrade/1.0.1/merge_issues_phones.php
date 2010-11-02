#!/usr/bin/php5
<?php
// WARNING: this script takes a few minutes to be executed completly, thus run it into a screen.

require_once 'connect.db.inc.php';
require_once '../../classes/phone.php';

$globals->debug = 0; // Do not store backtraces.

print "Formats non formated phones. (1/2)\n";
$it = XDB::rawIterator("SELECT  search_tel AS search, display_tel AS display, comment, link_id,
                                tel_type AS type, link_type, tel_id AS id, pid, pub
                          FROM  profile_phones
                         WHERE  search_tel = '' OR search_tel IS NULL
                      ORDER BY  pid, link_id, tel_id");
$total = $it->total();
$i = 0;
$j = 0;
while ($item = $it->next()) {
    $phone = new Phone($item);
    $phone->delete();
    $phone->save();

    ++$i;
    ++$j;
    if ($j == 100) {
        $j = 0;
        printf("\r%u / %u",  $i, $total);
    }
}
printf("\r%u / %u",  $i, $n);
print "\nFormating done.\n\n";

print "Deletes duplicated phones. (2/2)\n";
$pids = XDB::rawFetchColumn("SELECT  DISTINCT(pid)
                               FROM  profile_phones AS p1
                              WHERE  link_type = 'user' AND EXISTS (SELECT  *
                                                                      FROM  profile_phones AS p2
                                                                     WHERE  p2.link_type = 'user' AND p2.pid = p1.pid AND p2.tel_id != p1.tel_id)
                           ORDER BY  pid");
$total = count($pids);
$done = 0;
$aux = 0;
$phones = array();
$duplicates = array();
foreach ($pids as $pid) {
    $count = 0;
    $it = XDB::iterator("SELECT  search_tel AS search, display_tel AS display, comment, link_id,
                                 tel_type AS type, link_type, tel_id AS id, pid, pub
                           FROM  profile_phones
                          WHERE  link_type = 'user' AND pid = {?}
                       ORDER BY  tel_id", $pid);
    while ($item = $it->next()) {
        $phones[] = new Phone($item);
        ++$count;
    }
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
        XDB::execute('UPDATE IGNORE  profile_merge_issues
                                SET  issues = REPLACE(issues, \'phone\', \'\')
                              WHERE  pid = {?}', $pid);
    }
    unset($duplicates);
    unset($phones);

    ++$done;
    ++$aux;
    if ($aux == 10) {
        $aux = 0;
        printf("\r%u / %u",  $done, $total);
    }
}
printf("\r%u / %u",  $done, $total);
print "\n\nThat's all folks!\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
