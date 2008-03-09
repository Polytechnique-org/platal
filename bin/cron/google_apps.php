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

// Updates the gapps_accounts table with Plat/al information.
// Cleans-up the job queue, and execute post-queue hooks.

require_once('../connect.db.inc.php');
require_once('../../classes/plmailer.php');
require_once('../../include/googleapps.inc.php');
if (!$globals->mailstorage->googleapps_domain) {
  exit;
}

/* Updates the l_userid parameter for newer user accounts. */
$res = XDB::iterator(
    "SELECT  g.g_account_name, a.id
       FROM  gapps_accounts AS g
  LEFT JOIN  aliases as a ON (a.alias = g.g_account_name AND a.type = 'a_vie')
      WHERE  (g.l_userid IS NULL OR g.l_userid <= 0) AND a.id IS NOT NULL");
while ($account = $res->next()) {
    XDB::execute(
        "UPDATE  gapps_accounts
            SET  l_userid = {?}
          WHERE  g_account_name = {?}",
        $account['id'], $account['g_account_name']);
}

/* Emits a warning for GApps accounts without local user_id. */
$res = XDB::iterator(
    "SELECT  g.g_account_name
       FROM  gapps_accounts AS g
  LEFT JOIN  aliases as a ON (a.alias = g.g_account_name AND a.type = 'a_vie')
      WHERE  (g.l_userid IS NULL OR g.l_userid <= 0) AND a.id IS NULL");
while ($account = $res->next()) {
    if (!preg_match("/^admin-/", $account['g_account_name'])) {
        printf("Warning: GApps account '%s' has no local user_id.\n", $account['g_account_name']);
    }
}

/* Retrieves successful job queues for post-queue processing. */
$res = XDB::iterator(
    "SELECT  q_id, q_recipient_id, j_type, j_parameters
       FROM  gapps_queue
      WHERE  p_status = 'success' AND q_recipient_id IS NOT NULL");
while ($job = $res->next()) {
    if ($job['j_type'] == 'u_create') {
        post_queue_u_create($job);
    } else if ($job['j_type'] == 'u_update') {
        post_queue_u_update($job);
    }
}

/* Removes successful jobs, and old failed jobs. */
XDB::execute(
    "DELETE  FROM gapps_queue
      WHERE  p_status = 'success' OR
             (p_status = 'hardfail' AND p_end_date < DATE_SUB(NOW(), INTERVAL 15 DAY))");

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
