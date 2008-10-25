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

/* Updates the l_userid parameter for newer nicknames. */
$res = XDB::iterator(
    "SELECT  g.g_account_name, a.id
       FROM  gapps_nicknames AS g
  LEFT JOIN  aliases AS a ON (a.alias = g.g_account_name AND a.type = 'a_vie')
      WHERE  (g.l_userid IS NULL or g.l_userid <= 0) AND a.id IS NOT NULL
   GROUP BY  g_account_name");
while ($nickname = $res->next()) {
    XDB::execute(
        "UPDATE  gapps_nicknames
            SET  l_userid = {?}
          WHERE  g_account_name = {?}",
        $nickname['id'], $nickname['g_account_name']);
}

/* Emits a warning for nicknames without local user_id. */
$res = XDB::iterator(
    "SELECT  g.g_account_name
       FROM  gapps_nicknames AS g
  LEFT JOIN  aliases as a ON (a.alias = g.g_account_name AND a.type = 'a_vie')
      WHERE  (g.l_userid IS NULL OR g.l_userid <= 0) AND a.id IS NULL");
while ($nickname = $res->next()) {
    if (!preg_match("/^admin-/", $nickname['g_account_name'])) {
        printf("Warning: Nickname '%s' has no local user_id.\n", $nickname['g_account_name']);
    }
}

/* Checks that all nicknames have been synchronized to GoogleApps. Creates the
   missing ones. */
$res = XDB::iterator(
    "SELECT  g.l_userid AS id, f.alias AS username, a.alias AS nickname
       FROM  gapps_accounts AS g
 INNER JOIN  aliases AS f ON (f.id = g.l_userid AND f.type = 'a_vie')
 INNER JOIN  aliases AS a ON (a.id = g.l_userid AND a.type = 'alias')
  LEFT JOIN  gapps_nicknames AS n ON (n.l_userid = g.l_userid AND n.g_nickname = a.alias)
      WHERE  n.g_nickname IS NULL AND g.l_userid IS NOT NULL");
while ($nickname = $res->next()) {
    // Checks that the requested nickname doesn't look like a regular forlife;
    // we might run in troubler later if we don't keep the two repos. If we need
    // to add a forlife-looking nickname at some point, we'll do it manually.
    if (!preg_match('/^[-a-z]+\.[-a-z]+\.\d{4}$/', $nickname['nickname'])) {
        XDB::execute(
            "INSERT  INTO gapps_queue
                SET  q_recipient_id = {?}, p_entry_date = NOW(), p_notbefore_date = NOW(),
                     p_priority = 'offline', j_type = 'n_create', j_parameters = {?}",
            $nickname['id'],
            json_encode($nickname));
    }
}

/* Checks that all nicknames in GoogleApps are also aliases on plat/al side.
   Deletes the invalid ones. */
$res = XDB::iterator(
    "SELECT  g.l_userid AS id, g.g_nickname AS nickname
       FROM  gapps_nicknames AS g
  LEFT JOIN  aliases AS a ON (a.id = g.l_userid AND a.type = 'alias' AND a.alias = g.g_nickname)
      WHERE  g.l_userid IS NOT NULL AND a.alias IS NULL");
while ($nickname = $res->next()) {
    XDB::execute(
        "INSERT  INTO gapps_queue
            SET  q_recipient_id = {?}, p_entry_date = NOW(), p_notbefore_date = NOW(),
                 p_priority = 'offline', j_type = 'n_delete', j_parameters = {?}",
        $nickname['id'],
        json_encode($nickname));
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
