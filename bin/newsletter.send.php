#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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

require('./connect.db.inc.php');
require("newsletter.inc.php");

$opt = getopt('i:h');

if(empty($opt['i']) || isset($opt['h'])) {
    echo <<<EOF
usage: send_nl.php -i nl_id
       sends the NewsLetter of id "id"
EOF;
    exit;
}

$id = intval($opt['i']);
$nl = new NewsLetter($id);
$nl->setSent();

while(true) {
    $res = XDB::iterRow(
            "SELECT  ni.user_id, a.alias,
                     u.prenom, IF(u.nom_usage='', u.nom, u.nom_usage),
                     FIND_IN_SET('femme', u.flags),
                     q.core_mail_fmt AS pref
               FROM  newsletter_ins  AS ni
         INNER JOIN  auth_user_md5   AS u  USING(user_id)
         INNER JOIN  auth_user_quick AS q  ON(q.user_id = u.user_id)
         INNER JOIN  aliases         AS a  ON(u.user_id=a.id AND FIND_IN_SET('bestalias',a.flags))
          LEFT JOIN  emails          AS e  ON(e.uid=u.user_id AND e.flags='active')
              WHERE  ni.last<{?} AND e.email IS NOT NULL
           GROUP BY  ni.user_id
              LIMIT  60", $id);
    if (!$res->total()) { exit; }

    $sent = Array();
    while (list($uid, $bestalias, $prenom, $nom, $sexe, $fmt) = $res->next()) {
        $sent[] = "user_id='$uid'";
        $nl->sendTo($prenom, $nom, $bestalias, $sexe, $fmt=='html');
    }
    XDB::execute('UPDATE newsletter_ins SET last={?} WHERE '.implode(' OR ', $sent), $id);
    sleep(60);
}

?>
