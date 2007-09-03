#!/usr/bin/php5 -q
<?php

ini_set('include_path', '.:../include:/usr/share/php');
require_once('connect.db.inc.php');
require_once('marketing.inc.php');

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
    $login = $data[$logcol];
    $matri = preg_replace('/1(\d{2})(\d{3})/', '20${1}0\2', $data[$matcol]);
    if (!is_numeric($matri)) {
        echo "ERROR The matricule ($matri) is not a numerical value\n";
        break;
    }
    $query = XDB::query("SELECT  user_id
                           FROM  auth_user_md5
                          WHERE  matricule = {?}",
                        $matri);
    $uid = $query->fetchOneCell();
    if (!$uid) {
        echo "WARNING Can't find uid for matricule $matri (login $login)\n";
        continue;
    }
    $market = Marketing::get($uid, "$login@poly.polytechnique.fr");
    if (!is_null($market)) {
        echo "WARNING A marketing has already been to $matri on $login\n";
        continue;
    }
    $market = new Marketing($uid, "$login@poly.polytechnique.fr", 'default', null, 'staff');
    $market->add(false);
    $market->send();
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>

