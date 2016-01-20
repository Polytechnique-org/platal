#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

/**
 * Check that for every profile, the best postal address which is used to
 * send mail is the right one according to a rule in Address::updateAxMail().
 *
 * To fix something which has been reported by this script, you only need to
 * run "Address::updateBestMail($pid)" with $pid being the Profile ID
 */

require '../connect.db.inc.php';
require_once '../../classes/address.php';

$admin_visibility = Visibility::get(Visibility::VIEW_ADMIN);

// Enumerate every profile
$pids = XDB::iterRow("SELECT pid from profiles");
while ($row = $pids->next()) {
    $pid = $row[0];

    // Find the address which would be selected as "AX mail"
    // But don't update anything
    $best_mail = Address::updateBestMail($pid, true);
    if (is_null($best_mail)) {
        continue;
    }

    // Just continue if the returned address is already selected
    $flags = new PlFlagSet($best_mail['flags']);
    if ($flags->hasFlag('dn_best_mail')) {
        continue;
    }

    // The current profile is buggy.
    // Let's fetch more data to print detailed information
    $profile = Profile::get($pid);
    $addresses = ProfileField::getForPID('ProfileAddresses', array($pid), $admin_visibility);
    $addresses = $addresses->get(Profile::ADDRESS_POSTAL);

    $old_mail = null;
    $new_mail = null;
    foreach ($addresses as $addr) {
        if ($addr->flags->hasFlag('dn_best_mail')) {
            $old_mail = $addr;
        } else if ($addr->id == $best_mail['id']) {
            $new_mail = $addr;
        }
    }

    echo "Profile " . $profile->hrid() . " ($pid) has a wrongly selected best mail.\n";
    if (is_null($old_mail)) {
        echo "... no currently selected best mail\n";
    } else {
        echo "... currently selected best mail: " . $old_mail->formatted_address .
            " (flags: " . $old_mail->flags->flags() . ", pub: " . $old_mail->pub . ")\n";
    }
    if (is_null($new_mail)) {
        echo "... unable to find newly selected best mail (BUG!)\n";
    } else {
        echo "... best mail that would be selected: " . $new_mail->formatted_address .
            " (flags: " . $new_mail->flags->flags() . ", pub: " . $new_mail->pub . ")\n";
    }
    echo "\n";
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
