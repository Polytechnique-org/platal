#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2014 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/

require './connect.db.inc.php';
require_once '../classes/address.php';
require_once '../classes/geocoder.php';
require_once '../classes/gmapsgeocoder.php';
require_once '../classes/visibility.php';

$globals->debug = 0; // Do not store backtraces

$targets = array(
    'g' => 'pa.formatted_address',
    'p' => 'pa.postalText'
);
$ranges = array(
    'f' => ' != \'\'',
    'e' => ' = \'\'',
    'a' => ' IS NOT NULL'
);

$options = getopt('g::t:r:h::', array('geocode::', 'target:', 'range:', 'help::'));

$help_required = isset($options['h']) || isset($options['help']);
$geocoding_required = isset($options['g']) || isset($options['geocode']);
$target = '';
$range = '';

if (isset($options['t'])) {
    $target = $options['t'];
} elseif (isset($options['target'])) {
    $target = $options['target'];
}

if (isset($options['r'])) {
    $range = $options['r'];
} elseif ($options['range']) {
    $range = $options['range'];
}

$missing_option = !array_key_exists($target, $targets) || !array_key_exists($range, $ranges);

if ($missing_option || $help_required) {
    echo <<<EOF
SYNOPSIS
    formatAddresses.php [-g] -t [g|p] -r [f|e|a]

DESCRIPTION
    formatAddresses.php formats addresses. If the addresses need geocoding, this
    must be specified (-g). The targetted group of addresses must be specified
    (non formatted addresses, formatted addresses, all addresses).

OPTIONS
    -g, --geocode
        Geocodes the adresses. If not required, the address will not be
        geolocated.
    -t, --target [ g | p ]
        The selection will be made either on the geocoding
        (formatted_address) or the postal address (postalText).
    -r, --range [ f | e | a ]
        The selection will include the addresses corresponding to the right
        target, which are formatted, empty (non formatted) or all addresses.
    -h, --help
        displays this help

EOF;
    exit;
}

print "Formats addresses.\n";

if ($range != 'a') {
    $where = $targets[$target] . $ranges[$range];
} else {
    $where = null;
}

if ($geocoding_required) {
    // Waiting time is computed as follows: 3600 * 24 / LIMIT,
    // where LIMIT is google's limit reduced to take into account site geocoding.
    $wait = ceil(3600 * 24 / 2000);
    $display_limit = 1;
} else {
    $wait = 0;
    $display_limit = 100;
}

$it = Address::iterate(array(), array(), array(), Visibility::get(Visibility::VIEW_PRIVATE), $where);

$total = $it->total();
$i = 0;
$j = 0;
$skipped = 0;
printf("\r%u / %u",  $i, $total);

while ($address = $it->next()) {
    $address->changed = ($geocoding_required ? 1 : 0);
    $address->format();
    if ($address->delete()) {
        $address->save(false);
    } else {
        ++$skipped;
    }

    ++$i;
    if ($i == $display_limit) {
        ++$j;
        $i = 0;
        printf("\r%u / %u",  $i + $display_limit * $j, $total);
    }
    sleep($wait * $address->geocoding_calls);
}
printf("\r%u / %u",  $i + $display_limit * $j, $total);

if ($skipped != 0) {
    printf("\n%u addresses skipped.\n", $skipped);
}

print "\nDone.\n";

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
