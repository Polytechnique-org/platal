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
/* Updates the gapps_accounts table with Plat/al information. */

require('./connect.db.inc.php');
if (!$globals->googleapps->domain) {
  exit;
}

/* Updates the l_userid parameter for newer user accounts. */
$res = XDB::iterator("SELECT  g.g_account_name, a.id
                        FROM  gapps_accounts AS g
                   LEFT JOIN  aliases as a ON (a.alias = g.g_account_name AND a.type = 'a_vie')
                       WHERE  (g.l_userid IS NULL OR g.l_userid <= 0) AND
                              a.id IS NOT NULL");
while ($account = $res->next()) {
  XDB::execute("UPDATE  gapps_accounts
                   SET  l_userid = {?}
                 WHERE  g_account_name = {?}",
               $account['id'], $account['g_account_name']);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
