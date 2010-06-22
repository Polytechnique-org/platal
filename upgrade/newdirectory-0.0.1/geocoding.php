#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';
require_once 'geocoding.inc.php';

$globals->debug = 0; // Do not store backtraces.

$res = XDB::query('SELECT  MIN(pid), MAX(pid)
                     FROM  profiles');

$pids = $res->fetchOneRow();

$minPid = $pids[0];
$maxPid = $pids[1];

echo "Filling the 'text' fied is over. Geocoding will start now and will take a few days.\n";

// Tries to geocode all the addresses.
for ($pid = $minPid; $pid < $maxPid + 1; ++$pid) {
    $res = XDB::iterator('SELECT  *
                            FROM  profile_addresses
                           WHERE  pid = {?}',
                         $pid);

    while ($address = $res->next()) {
        $updateTime = $address['updateTime'];
        $gmapsGeocoder = new GMapsGeocoder();
        $address = $gmapsGeocoder->getGeocodedAddress($address);

        if (!isset($address['geoloc'])) {
            // TODO: use address and phone classes to update profile_job_enum and profile_phones once they are done.

            XDB::execute('DELETE FROM  profile_addresses
                                WHERE  pid = {?} AND id = {?} AND type = {?}',
                         $address['pid'], $address['id'], $address['type']);

            Geocoder::getAreaId($address, 'administrativeArea');
            Geocoder::getAreaId($address, 'subAdministrativeArea');
            Geocoder::getAreaId($address, 'locality');
            XDB::execute('INSERT INTO  profile_addresses (pid, type, id, flags, accuracy,
                                                          text, postalText, postalCode, localityId,
                                                          subAdministrativeAreaId, administrativeAreaId,
                                                          countryId, latitude, longitude, updateTime, pub, comment,
                                                          north, south, east, west)
                               VALUES  ({?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?}, {?},
                                        {?}, {?}, FROM_UNIXTIME({?}), {?}, {?}, {?}, {?}, {?}, {?})',
                         $address['pid'], $address['type'], $address['id'], $flags, $address['accuracy'],
                         $address['text'], $address['postalText'], $address['postalCode'], $address['localityId'],
                         $address['subAdministrativeAreaId'], $address['administrativeAreaId'],
                         $address['countryId'], $address['latitude'], $address['longitude'],
                         $updateTime, $address['pub'], $address['comment'],
                         $address['north'], $address['south'], $address['east'], $address['west']);
        } else {
            XDB::execute('UPDATE  profile_addresses
                             SET  postalText = {?}
                           WHERE  pid = {?} AND id = {?} AND type = {?}',
                         $address['postalText'], $address['pid'], $address['id'], $address['type']);
        }

        sleep(60); // So we don't get blacklisted by Google.
    }
}

echo "Geocoding is over.\n";

/* vim:set et sw=4 sts=4 ts=4: */
?>
