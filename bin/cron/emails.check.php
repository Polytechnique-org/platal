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

/* Number of consecutive month of bounce before deactivating a redirection
 */
$panne_level = 3;

require './connect.db.inc.php';
require 'Console/Getopt.php';

/*
 * Parse the command-line options.
 */
$opts = Console_GetOpt::getopt($argv, 'v');
$opt_verbose = false;

if (PEAR::isError($opts)) {
    echo $opts->getMessage();
} else {
    $opts = $opts[0];
    foreach ($opts as $opt) {
        if ($opt[0] == 'v') {
            $opt_verbose = true;
        }
    }
}

/*
 * Check duplicated addresses
 */
$it = Xdb::iterRow("SELECT  s1.email, s2.email, r1.redirect
                      FROM  email_redirect_account AS r1
                INNER JOIN  email_redirect_account AS r2 ON (r1.redirect = r2.redirect AND r1.uid != r2.uid)
                INNER JOIN  email_source_account   AS s1 ON (s1.uid = r1.uid AND s1.type = 'forlife')
                INNER JOIN  email_source_account   AS s2 ON (s2.uid = r2.uid AND s2.type = 'forlife')
                 LEFT JOIN  email_watch            AS w  ON (r1.redirect = w.email)
                     WHERE  w.email IS NULL
                  GROUP BY  r1.redirect
                  ORDER BY  r1.redirect, s1.email");

$insert   = array();
$conflits = array();
while (list($alias1, $alias2, $mail) = $it->next()) {
    $insert[]   = "('$mail', 'pending', CURDATE(), NOW())";
    $conflits[] = "* $mail sur $alias1 et $alias2";
}

if (count($conflits) > 0) {
    echo "Nouvelles adresses en doublon détectées :\n" . join("\n", $conflits)
        . "\n\nVous pouvez entrer les informations collectées à ce sujet sur la page :\n"
        . "https://www.polytechnique.org/admin/emails/duplicated";

    echo "\n\n";
    $sql = "INSERT IGNORE INTO  email_watch (email, state, detection, last)
                        VALUES  " . join(", ", $insert);
    XDB::execute($sql);
    if (XDB::errno() != 0) {
        echo 'Error : ' . XDB::error() . "\n$sql";
    }
}

/*
 * Check dead emails
 */
if ($panne_level > 0) {
    $res = XDB::fetchAllAssoc("SELECT  r.redirect, a.hruid
                                 FROM  email_redirect_account AS r
                           INNER JOIN  accounts               AS a ON (a.uid = r.uid)
                                WHERE  r.broken_level = {?} AND r.flags != 'broken'
                             ORDER BY  a.hruid",
                              $panne_level);

    if ($res) {
        echo "Nouvelles adresses en panne detectees :\n";
        foreach ($res as $assoc) {
            echo '* ' . $assoc['redirect'] . ' pour ' . $assoc['hruid'] . "\n";
        }
        echo "\n\n";

        Xdb::execute("UPDATE  email_redirect_account
                         SET  flags = 'broken'
                       WHERE  broken_level = 3");
    }

    Xdb::execute('UPDATE  email_redirect_account
                     SET  broken_level = {?}
                   WHERE  broken_level > {?}',
                 $panne_level, $panne_level);
}

/*
 * Retrieve the users with no active redirection, but still one working
 * inactive redirection.
 */
if ($opt_verbose) {
    $res = XDB::fetchAllAssoc("SELECT  a.hruid, r2.redirect
                                 FROM  accounts               AS a
                            LEFT JOIN  email_redirect_account AS r1 ON (a.uid = r1.uid AND r1.flags = 'active')
                           INNER JOIN  email_redirect_account AS r2 ON (a.uid = r2.uid AND r2.flags = 'inactive'
                                                                        AND r2.type != 'imap' AND r2.type != 'homonym')
                                WHERE  r1.uid IS NULL
                             GROUP BY  a.uid");

    if ($res) {
        echo "Camarades n'ayant plus d'adresses actives, mais ayant une adresse inactive :\n";
        foreach ($res as $user) {
            echo '* ' . $user['redirect'] . ' pour ' . $user['hruid'] . "\n";
        }
    }
    echo "\n";
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
