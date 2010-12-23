#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';
require_once '../../classes/address.php';
require_once '../../classes/gmapsgeocoder.php';
require_once '../../classes/geocoder.php';

$globals->debug = 0; // Do not store backtraces.

$it = XDB::iterator('SELECT  gl.language, gc.country, gc.iso_3166_1_a2
                       FROM  geoloc_languages AS gl
                 INNER JOIN  geoloc_countries AS gc ON (gl.iso_3166_1_a2 = gc.iso_3166_1_a2)');

echo $it->total() . " pays à remplir.\n";
while ($item = $it->next()) {
    if ($item['language'] != 'fr') {
        $address = new Address(array('text' => $item['country']));
        $gmapsGeocoder = new GMapsGeocoder();
        $gmapsGeocoder->getGeocodedAddress($address, $item['language'], true);
        $country = $address->country;
    } else {
        $country = $item['country'];
    }
    $countryPlain = mb_strtoupper(replace_accent($country));
    XDB::execute('UPDATE  geoloc_languages
                     SET  country = {?}, countryPlain = {?}
                   WHERE  iso_3166_1_a2 = {?} AND language = {?}',
                 $country, $countryPlain, $item['iso_3166_1_a2'], $item['language']);
     sleep(1);
}

$it = XDB::rawIterator('SELECT  country, iso_3166_1_a2
                          FROM  geoloc_countries');
echo $it->total() . " pays à simplifier.\n";
while ($item = $it->next()) {
    XDB::execute('UPDATE  geoloc_countries
                     SET  countryPlain = {?}
                   WHERE  iso_3166_1_a2 = {?}',
                 mb_strtoupper(replace_accent($item['country'])), $item['iso_3166_1_a2']);
}

// Fixes geocoding errors.
XDB::rawExecute("REPLACE INTO  geoloc_languages (iso_3166_1_a2, language, country, countryPlain)
                       VALUES  ('FM', 'en', 'Federated States of Micronesia', 'FEDERATED STATES OF MICRONESIA'),
                               ('MH', 'en', 'Republic of the Marshall Islands', 'REPUBLIC OF THE MARSHALL ISLANDS'),
                               ('PS', 'ar', 'دولة فلسطين', 'دولة فلسطين'),
                               ('SB', 'en', 'Solomon Islands', 'SOLOMON ISLANDS'),
                               ('TW', 'zh-CN', '台湾', '台湾'),
                               ('TW', 'zh-TW', '台灣', '台灣'),
                               ('CZ', 'cs', 'Česká Republika', 'CESKA REPUBLIKA'),
                               ('CZ', 'sk', 'Česká Republika', 'CESKA REPUBLIKA'),
                               ('DO', 'es', 'República Dominicana', 'REPUBLICA DOMINICANA'),
                               ('GD', 'en', 'Grenada', 'GRENADA'),
                               ('MD', 'ro', 'Republica Moldova', 'REPUBLICA MOLDOVA'),
                               ('RU', 'ru', 'Россия', 'Россия'),
                               ('SK', 'sk', 'Slovenská Republika', 'SLOVENSKA REPUBLIKA'),
                               ('TZ', 'en', 'United Republic of Tanzania', 'UNITED REPUBLIC OF TANZANIA')");

/* vim:set et sw=4 sts=4 ts=4: */
?>
