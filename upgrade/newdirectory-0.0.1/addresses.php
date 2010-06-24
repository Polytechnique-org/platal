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

echo "This will take a few minutes.\n";

// Fills the 'text' field in profile_addresses.
for ($pid = $minPid; $pid < $maxPid + 1; ++$pid) {
    // First deals with home addresses (located in #x4dat#.adresses).
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
                             FROM  #x4dat#.adresses    AS a
                       INNER JOIN  #x4dat#.geoloc_pays AS gp ON (gp.a2 = a.country)
                       INNER JOIN  account_profiles    AS ap ON (a.uid = ap.uid AND FIND_IN_SET('owner', ap.perms))
                            WHERE  ap.pid = {?}
                         ORDER BY  a.adrid",
                           $pid);

    while ($address = $res->next()) {
        $text = get_address_text($address);
        XDB::execute('UPDATE  profile_addresses
                         SET  text = {?}
                       WHERE  pid = {?} AND type = {?} AND id = {?}',
                     $text, $pid, $address['pro'] ? 'job' : 'home', $address['id']);
    }

    // Then deals with job addresses (located in #x4dat#.entreprises).
    $res  = XDB::iterator("SELECT  e.entrid AS id,  j.id AS jobid, e.adr1, e.adr2, e.adr3,
                                   e.postcode, e.city, e.cityid, e.region, e.regiontxt,
                                   e.adr_pub AS pub, e.country, gp.pays AS countrytxt, gp.display,
                                   e.glat AS precise_lat, e.glng AS precise_lon
                             FROM  #x4dat#.entreprises AS e
                       INNER JOIN  #x4dat#.geoloc_pays AS gp ON (gp.a2 = e.country)
                       INNER JOIN  account_profiles    AS ap ON (e.uid = ap.uid AND FIND_IN_SET('owner', ap.perms))
                       INNER JOIN  profile_job_enum    AS j  ON (e.entreprise = j.name)
                            WHERE  ap.pid = {?}
                         ORDER BY  e.entrid",
                           $pid);

    while ($address = $res->next()) {
        $text = get_address_text($address);
        XDB::execute('UPDATE  profile_addresses
                         SET  text = {?}
                       WHERE  pid = {?} AND type = {?} AND id = {?} AND jobid = {?}',
                     $text, $pid, 'job', $address['id'], $address['jobid']);
    }
}

function get_address_text($adr)
{
    $t = '';
    if (isset($adr['adr1']) && $adr['adr1']) $t .= $adr['adr1'];
    if (isset($adr['adr2']) && $adr['adr2']) $t .= "\n".$adr['adr2'];
    if (isset($adr['adr3']) && $adr['adr3']) $t .= "\n".$adr['adr3'];
    $l = '';
    if (isset($adr['display']) && $adr['display']) {
        $keys = explode(' ', $adr['display']);
        foreach ($keys as $key) {
            if (isset($adr[$key])) {
                $l .= ' ' . $adr[$key];
            } else {
                $l .= ' ' . $key;
            }
        }
        if ($l) substr($l, 1);
    } elseif ($adr['country'] == 'US' || $adr['country'] == 'CA' || $adr['country'] == 'GB') {
        if ($adr['city']) $l .= $adr['city'] . ",\n";
        if ($adr['region']) $l .= $adr['region'] . ' ';
        if ($adr['postcode']) $l .= $adr['postcode'];
    } else {
        if (isset($adr['postcode']) && $adr['postcode']) $l .= $adr['postcode'] . ' ';
        if (isset($adr['city']) && $adr['city']) $l .= $adr['city'];
    }
    if ($l) $t .= "\n" . trim($l);
    if ($adr['country'] != '00' && (!$adr['countrytxt'] || $adr['countrytxt'] == strtoupper($adr['countrytxt']))) {
        $res = XDB::query('SELECT  countryFR
                             FROM  geoloc_countries
                            WHERE  iso_3166_1_a2 = {?}',
                          $adr['country']);
        $adr['countrytxt'] = $res->fetchOneCell();
    }
    if (isset($adr['countrytxt']) && $adr['countrytxt']) {
        $t .= "\n" . $adr['countrytxt'];
    }
    return trim($t);
}

/* vim:set et sw=4 sts=4 ts=4: */
?>
