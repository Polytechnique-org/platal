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
        $Id: clean.php,v 1.5 2004/11/20 10:16:06 x2000habouzit Exp $
 ***************************************************************************/

require('./connect.db.inc.php');

function query ($sql) {
    mysql_query($sql);
    if (mysql_errno() != 0) {
	echo "error in \"$sql\" :\n", mysql_error(),"\n";
    }
}

// la table des notifs est nettoyée
$eight_days_ago = date("YmdHis",mktime() - 8*24*60*60);
query("DELETE FROM watch_ops WHERE known<$eight_days_ago");

// la table en_cours est nettoyée
query("DELETE FROM en_cours WHERE TO_DAYS(NOW()) - TO_DAYS(date) >= 365");
query("delete from en_cours where loginbis = 'INSCRIT'");

// la table envoidirect est nettoyée
query("update envoidirect set uid = CONCAT('+',uid) where uid not like '+%' and date_succes != 0");

// quelques tables sont triées pour que la lecture triée soit plus facile
query("alter table nationalites order by text");
query("alter table applis_def order by text");
query("alter table binets_def order by text");
query("alter table groupesx_def order by text");
query("alter table secteur order by text");
query("alter table sections order by text");

// on regarde si qqun a fait bcp de requêtes dans l'annuaire, puis on remete à 0
//$res = mysql_query("SELECT nom,prenom,promo,nb_recherches FROM auth_user_md5 as u INNER JOIN nb_recherches as r ON(u.user_id = r.uid) WHERE r.nb_recherches > 90 AND u.perms != 'admin' order by r.nb_recherches");
//while (list($n, $p, $pr, $nbr) = mysql_fetch_row($res))
//    echo $n." ".$p.", X".$pr." : ".$nbr." recherches dans l'annuaire !\n";
//query("UPDATE nb_recherches SET nb_recherches = 0");

?>
