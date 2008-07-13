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

require('./connect.db.inc.php');

function query ($sql) {
    XDB::execute($sql);
    if (XDB::errno() != 0) {
    	echo "error in \"$sql\" :\n", XDB::error(), "\n";
    }
}

// la table des notifs est nettoyée
$eight_days_ago = date("YmdHis", time() - 8*24*60*60);
query("DELETE FROM watch_ops WHERE known<$eight_days_ago");

query("DELETE FROM register_pending WHERE TO_DAYS(NOW()) - TO_DAYS(date) >= 365");
query("DELETE FROM register_pending WHERE hash = 'INSCRIT'");

// quelques tables sont triées pour que la lecture triée soit plus facile
query("ALTER TABLE applis_def ORDER BY text");
query("ALTER TABLE binets_def ORDER BY text");
query("ALTER TABLE groupesx_def ORDER BY text");
query("ALTER TABLE secteur ORDER BY text");
query("ALTER TABLE sections ORDER BY text");

// Prunes older autocomplete queries.
query("DELETE FROM search_autocomplete WHERE generated < DATE_SUB(NOW(), INTERVAL 1 DAY)");

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
