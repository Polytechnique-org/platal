#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';
require_once 'geocoding.inc.php';

$globals->debug = 0; // Do not store backtraces.

$res = XDB::query('SELECT  MIN(user_id), MAX(user_id)
                     FROM  auth_user_md5');

$pids = $res->fetchOneRow();

$minPid = $pids[0];
$maxPid = $pids[1];

echo "This will take a few minutes.\n".

// Fills the 'text' field in profile_addresses.
for ($pid = $minPid; $pid < $maxPid + 1; ++$pid) {
    $res  = XDB::iterator("SELECT  a.adrid AS id, a.adr1, a.adr2, a.adr3,
                                   UNIX_TIMESTAMP(a.datemaj) AS datemaj,
                                   a.postcode, a.city, a.cityid, a.region, a.regiontxt,
                                   a.pub, a.country, gp.pays AS countrytxt, gp.display,
                                   FIND_IN_SET('coord-checked', a.statut) AS checked,
                                   FIND_IN_SET('res-secondaire', a.statut) AS secondaire,
                                   FIND_IN_SET('courrier', a.statut) AS mail,
                                   FIND_IN_SET('temporaire', a.statut) AS temporary,
                                   FIND_IN_SET('active', a.statut) AS current,
                                   FIND_IN_SET('pro', a.statut) AS pro,
                                   a.glat AS precise_lat, a.glng AS precise_lon
                             FROM  adresses AS a
                       INNER JOIN  geoloc_pays AS gp ON(gp.a2 = a.country)
                            WHERE  uid = {?}
                         ORDER BY  adrid",
                           $pid);

    while ($address = $res->next()) {
        $text = get_address_text($address);
        XDB::iterator('UPDATE  profile_addresses
                          SET  text = {?}
                        WHERE  pid = {?} AND type = {?} AND id = {?}',
                      $text, $pid, $address['pro'] ? 'job' : 'home', $address['id']);
    }
}

echo "Filling the 'text' filles is over. Geocoding will start now and will take a few days\n";

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

function get_address_text($adr)
{
    $t = "";
    if (isset($adr['adr1']) && $adr['adr1']) $t.= $adr['adr1'];
    if (isset($adr['adr2']) && $adr['adr2']) $t.= "\n".$adr['adr2'];
    if (isset($adr['adr3']) && $adr['adr3']) $t.= "\n".$adr['adr3'];
    $l = "";
    if (isset($adr['display']) && $adr['display']) {
        $keys = explode(' ', $adr['display']);
        foreach ($keys as $key) {
            if (isset($adr[$key])) {
                $l .= " ".$adr[$key];
            } else {
                $l .= " ".$key;
            }
        }
        if ($l) substr($l, 1);
    } elseif ($adr['country'] == 'US' || $adr['country'] == 'CA' || $adr['country'] == 'GB') {
        if ($adr['city']) $l .= $adr['city'].",\n";
        if ($adr['region']) $l .= $adr['region']." ";
        if ($adr['postcode']) $l .= $adr['postcode'];
    } else {
        if (isset($adr['postcode']) && $adr['postcode']) $l .= $adr['postcode']." ";
        if (isset($adr['city']) && $adr['city']) $l .= $adr['city'];
    }
    if ($l) $t .= "\n".trim($l);
    if ($adr['country'] != '00' && (!$adr['countrytxt'] || $adr['countrytxt'] == strtoupper($adr['countrytxt']))) {
        $res = XDB::query('SELECT  pays
                             FROM  geoloc_pays
                            WHERE  a2 = {?}',
                          $adr['country']);
        $adr['countrytxt'] = $res->fetchOneCell();
    }
    if (isset($adr['countrytxt']) && $adr['countrytxt']) {
        $t .= "\n".$adr['countrytxt'];
    }
    return trim($t);
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
