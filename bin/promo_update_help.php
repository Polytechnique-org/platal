#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
require '../include/name.func.inc.php';

$globals->debug = 0; // Do not store backtraces

$opts = getopt('f:p:');

if (empty($opts['f']) || empty($opts['p'])) {
    print "File name missing (-f=file_name) or promotion missing (-p=file_name).\n";
    exit;
}

$file = $opts['f'];
$handle = fopen($file, 'r');
$promo_year = $opts['p'];

$already = array();
$new = array();
$ambiguous = array();

while ($line = trim(fgets($handle))) {
    $data = explode(';', $line);
    $cond = new PFC_And(new UFC_NameTokens(split_name_for_search($data[0]), array(), false, false, Profile::LASTNAME));
    $cond->addChild(new UFC_NameTokens(split_name_for_search($data[1]), array(), false, false, Profile::FIRSTNAME));
    $uf = new UserFilter($cond);
    $profiles = $uf->getProfiles();
    switch (count($profiles)) {
      case 0:
        $new[] = $line;
        break;
      case 1:
        foreach ($profiles as $profile) {
            $already[] = $profile->hrid();
        }
        break;
      default:
        $hrids = array();
        foreach ($profiles as $profile) {
            $hrids[] = $profile->hrid();
        }
        $ambiguous[] = $line . ': ' . implode(', ', $hrids);
        break;
    }
}

$cond = new UFC_Promo('=', UserFilter::GRADE_MST, $promo_year);
$uf = new UserFilter($cond);
$profiles = $uf->getProfiles();
$promo = array();
foreach ($profiles as $profile) {
    $promo[] = $profile->hrid();
}

$intersect = array_intersect($promo, $already);
if (count($intersect) != count($already)) {
    print "There seems to be a problem: intersection of this promo and already found users differ.\n";
}
$to_remove = array_diff($promo, $intersect);

sort($new);
sort($ambiguous);
sort($to_remove);

print "New users:\n" . implode("\n", $new) . "\n\n";
print "Ambiguous users:\n" . implode("\n", $ambiguous) . "\n\n";
print "Users to remove (louk out for ambiguous users before!):\n" . implode("\n", $to_remove) . "\n";

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
