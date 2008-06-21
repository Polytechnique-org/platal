#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2008 Polytechnique.org                              *
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

ini_set('include_path', '.:../include:/usr/share/php');

require_once('connect.db.inc.php');
require_once('emails.inc.php');

$opts = getopt('o:');
if (($opts['o'] && $opts['o'] == '-') || empty($opts['o'])) {
    printf("Usage: emails.broken.helper.php -o <file>\n");
    exit(1);
} else {
    $output = $opts['o'];
}

$input_fd = fopen("php://stdin", "r");
$output_fd = fopen($output, "a");

while ($_email = fgets($input_fd)) {
    $_email = trim($_email);
    $email = valide_email($_email);
    if (empty($email) || $email=='@') {
        printf(">> %s: invalid email\n", $_email);
        continue;
    }

    $sel = XDB::query(
            "SELECT  e1.uid, e1.panne != 0 AS panne, count(e2.uid) AS nb_mails, u.nom, u.prenom, u.promo, a.alias
               FROM  emails        AS e1
          LEFT JOIN  emails        AS e2 ON (e1.uid = e2.uid AND FIND_IN_SET('active', e2.flags) AND e1.email != e2.email)
         INNER JOIN  auth_user_md5 AS u  ON (e1.uid = u.user_id)
         INNER JOIN  aliases       AS a  ON (u.user_id = a.id AND FIND_IN_SET('bestalias',a.flags))
              WHERE  e1.email = {?}
           GROUP BY  e1.uid", $email);
    if ($sel->numRows() > 0) {
        fwrite($output_fd, $email . "\n");
    } else {
        printf(">> %s: email not in the database\n", $_email);
    }
}

fclose($input_fd);
fclose($output_fd);

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
