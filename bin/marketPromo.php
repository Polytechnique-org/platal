#!/usr/bin/php5 -q
<?php

ini_set('include_path', '.:../include:/usr/share/php');
require_once 'connect.db.inc.php';
require_once 'marketing.inc.php';

$opts = getopt('f:l:m:');
if (($opts['f'] && $opts['f'] == '-') || empty($opts['f'])) {
    $file = 'php://stdin';
} else {
    $file = $opts['f'];
}
if (empty($opts['l']) || empty($opts['m'])) {
    exit;
}
$matcol = intval($opts['m']);
$logcol = intval($opts['l']);
$handle = fopen($file, 'r');

while ($data = fgetcsv($handle)) {
    $login  = $data[$logcol];
    $xorgid = preg_replace('/1(\d{2})(\d{3})/', '20${1}0\2', $data[$matcol]);
    if (!is_numeric($xorgid)) {
        echo "ERROR The matricule ($xorgid) is not a numerical value.\n";
        break;
    }
    $query = XDB::query("SELECT  a.uid
                           FROM  profiles         AS p
                     INNER JOIN  account_profiles AS ap ON (p.pid = ap.pid AND FIND_IN_SET('owner', perms)
                     INNER JOIN  accounts         AS a  ON (a.uid = ap.uid)
                          WHERE  p.xorg_id = {?}",
                        $xorgid);
    $uid = $query->fetchOneCell();
    if (!$uid) {
        echo "WARNING Can't find uid for matricule $xorgid (login $login)\n";
        continue;
    }
    $market = Marketing::get($uid, "$login@poly.polytechnique.fr");
    if (!is_null($market)) {
        echo "WARNING A marketing has already been to $xorgid on $login\n";
        continue;
    }
    $market = new Marketing($uid, "$login@poly.polytechnique.fr", 'default', null, 'staff');
    $market->add(false);
    $market->send();
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>

