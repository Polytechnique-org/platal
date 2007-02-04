#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

require('./connect.db.inc.php');

/* 
 * Check duplicated addresses
 */
$sql = "SELECT a1.alias, a2.alias, e1.email
          FROM emails        AS e1
    INNER JOIN emails        AS e2 ON (e1.email = e2.email AND e1.uid != e2.uid 
                                       AND (e1.uid < e2.uid OR NOT FIND_IN_SET(e2.flags, 'active')))
     LEFT JOIN emails_watch  AS w  ON (e1.email = w.email)
    INNER JOIN aliases       AS a1 ON (a1.id = e1.uid AND a1.type = 'a_vie')
    INNER JOIN aliases       AS a2 ON (a2.id = e2.uid AND a2.type = 'a_vie')
    INNER JOIN auth_user_md5 AS u1 ON (a1.id = u1.user_id)
    INNER JOIN auth_user_md5 AS u2 ON (a2.id = u2.user_id)
         WHERE FIND_IN_SET(e1.flags, 'active') AND u1.nom != u2.nom_usage AND u2.nom != u1.nom_usage AND w.email IS NULL
      ORDER BY a1.alias";

$it = Xdb::iterRow($sql);

$insert   = array();
$conflits = array();
while (list($alias1, $alias2, $mail) = $it->next()) {
    $insert[]   = "('$mail', 'pending', CURDATE(), NOW())";
    $conflits[] = "* $mail sur $alias1 et $alias2";
}

if (count($conflits) > 0) {
    echo "Nouvelles adresses en doublon detectees :\n" . join("\n", $conflits)
        . "\n\nVous pouvez entrer les informations collectees a ce sujet sur la page :\n"
        . "http://www.polytechnique.org/admin/emails/duplicated";

    echo "\n\n";
    $sql = "INSERT IGNORE INTO emails_watch (email, state, detection, last)
                        VALUES " . join(", ", $insert);
    XDB::execute($sql);
    if (XDB::errno() != 0) {
        echo 'Error : ' . XDB::error() . "\n$sql";
    }
}

/*
 * Check dead emails
 */
if ($panne_level > 0) {
    $sql = "SELECT  e.email, a.alias AS forlife
              FROM  emails  AS e
        INNER JOIN  aliases AS a ON a.id = e.uid AND a.type = 'a_vie'
             WHERE  e.panne_level = $panne_level AND e.flags = 'active'
          ORDER BY  a.alias";
    $res = Xdb::query($sql);

    if ($res->numRows()) {
        $result = $res->fetchAllAssoc();
        echo "Nouvelles adresses en panne detectees :\n";
        foreach ($result as $assoc) {
            echo '* ' . $assoc['email'] . ' pour ' . $assoc['forlife'] . "\n";
        }
        echo "\n\n";

        Xdb::execute("UPDATE  emails
                         SET  flags = 'panne'
                       WHERE  panne_level = 3 AND flags = 'active'");
    }

    Xdb::execute("UPDATE  emails
                     SET  panne_level = $panne_level
                   WHERE  panne_level > $panne_level");
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
