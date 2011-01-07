#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';

$globals->debug = 0; //do not store backtraces

$phones = array(
    'AF' => '93',
    'AN' => '599',
    'BY' => '375',
    'FM' => '691',
    'GE' => '995',
    'GL' => '299',
    'ID' => '62',
    'IL' => '972',
    'IN' => '91',
    'IQ' => '964',
    'IR' => '98',
    'JO' => '962',
    'JP' => '81',
    'KG' => '996',
    'KW' => '965',
    'KZ' => '7',
    'LA' => '856',
    'LB' => '961',
    'LK' => '94',
    'MM' => '95',
    'MN' => '976',
    'MV' => '960',
    'MY' => '60',
    'NP' => '977',
    'OM' => '968',
    'PH' => '63',
    'PK' => '92',
    'QA' => '974',
    'SA' => '966',
    'SG' => '65',
    'SY' => '963',
    'TH' => '66',
    'TJ' => '992',
    'TM' => '993',
    'TR' => '90',
    'TW' => '886',
    'UZ' => '998',
    'VG' => '1284',
    'VN' => '84',
    'YE' => '967',
);

foreach ($phones as $country => $phone) {
    XDB::execute('UPDATE  geoloc_countries
                     SET  phonePrefix = {?}
                   WHERE  iso_3166_1_a2 = {?}',
                 $phone, $country);
}

$res = XDB::iterator('SELECT  pid, link_type, link_id, tel_id AS id, search_tel AS search, search_tel AS display
                        FROM  profile_phones
                       WHERE  search_tel LIKE \'33%\'');
while ($item = $res->next()) {
    $phone = new Phone($item);
    $phone->format();
    XDB::execute('UPDATE  profile_phones
                     SET  display_tel = {?}
                   WHERE  pid = {?} AND link_type = {?}
                          AND link_id = {?} AND tel_id = {?}',
                 $phone->display, $phone->pid(), $phone->linkType(),
                 $phone->linkId(), $phone->id());
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
