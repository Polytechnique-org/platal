#!/usr/bin/php4 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
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
 ***************************************************************************
        $Id: send_nl.php,v 1.2 2004-10-31 16:20:25 x2000chevalier Exp $
 ***************************************************************************/

require('./connect.db.inc.php');
require("../../include/newsletter.inc.php");

function query ($sql) {
    mysql_query($sql);
    if (mysql_errno() != 0) {
	echo "error in \"$sql\" :\n", mysql_error(),"\n";
    }
}

$opts = getopt('i:h');

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
    $sql = mysql_query("SELECT  ni.user_id,ni.pref, a.alias, u.prenom,u.nom, FIND_IN_SET(u.flags, 'femme')
			  FROM  newsletter_ins AS ni
		    INNER JOIN  auth_user_md5  AS u  USING(user_id)
		    INNER JOIN  aliases        AS a  ON(u.user_id=a.id AND a.type='a_vie')
		         WHERE  ni.last<$id
			 LIMIT  60");
    if(!mysql_num_rows($res)) exit(0);
    $sent = Array();
    while(list($uid,$fmt,$forlife,$prenom,$nom,$sexe) = mysql_fetch_row($sql)) {
	$sent[] = "user_id='$uid'";
	$nl->sendTo($prenom,$nom,$forlife,$sexe,$html=='html');
    }
    mysql_free_result($res);
    mysql_query("UPDATE newsletter_ins SET last=$id WHERE ".implode(' OR ',$sent));
    sleep(60);
}

?>
