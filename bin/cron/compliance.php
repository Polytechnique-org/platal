#!/usr/bin/php5 -q
<?php
/***************************************************************************
*  Copyright (C) 2003-2013 Polytechnique.org                              *
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

require './connect.db.inc.php';

/**
 * Discards from the logger database session details for users.
 *
 * @param userPerms Permission level to use for that round of discards.
 * @param retentionPeriod Minimal number of months to keep entries for.
 * @param minimalBacklog Minimal number of entries to keep for any given user,
 *      no matter what.
 */
function discardExpiredSessions($userPerms, $retentionPeriod, $minimalBacklog) {
    $begin = time();
    switch ($userPerms) {
      case 'user':
        $state   = 'active';
        $isAdmin = 0;
        break;
      case 'admin':
        $state   = 'active';
        $isAdmin = 1;
        break;
      case 'disabled':
        $state   = 'disabled';
        $isAdmin = 0;
        break;
      default:
        return;
    }

    list($low, $high) = XDB::fetchOneRow(
        "SELECT  MIN(uid), MAX(uid)
           FROM  #x5dat#.accounts
           WHERE  state = {?} AND is_admin = {?}",
        $state, $isAdmin);

    $batchSize = 500;
    $nbBatches = 0;
    $affectedRows = 0;

    // Run in batches.
    for ($lowUID = $low; $lowUID <= $high; $lowUID += $batchSize) {

        // Slight optimization for last loop: adjust to exactly what's necessary.
        $highUID = min($high + 1, $lowUID + $batchSize);

        XDB::execute(
            "DELETE  s
               FROM  log_sessions AS s
               JOIN  (SELECT  a.uid,
                              (SELECT  us.start
                                 FROM  log_sessions AS us
                                WHERE  us.uid = a.uid AND (us.suid IS NULL OR us.suid = 0)
                             ORDER BY  us.start DESC
                                LIMIT  {?}, 1) AS no_discard_limit
                        FROM  #x5dat#.accounts AS a
                       WHERE  a.state = {?} AND a.is_admin = {?}
                              AND a.uid >= {?} AND a.uid < {?}
                    ORDER BY  a.uid ASC) AS ut ON (ut.uid = s.uid)
              WHERE  s.start < DATE_SUB(NOW(), INTERVAL {?} MONTH)
                     AND s.start < ut.no_discard_limit",
            $minimalBacklog - 1, $state, $isAdmin, $lowUID, $highUID, $retentionPeriod);

        $nbBatches += 1;
        $affectedRows += XDB::affectedRows();
    }

    $duration = time() - $begin;
    echo "Users with permission '$userPerms': removed $affectedRows sessions in $duration seconds ($nbBatches batches).\n";
}

/**
 * Checks for sessions without a valid associated user id.
 */
function checkOrphanedSessions() {
    $begin = time();
    $res = XDB::query(
        "SELECT  COUNT(*)
           FROM  log_sessions     AS s
      LEFT JOIN  #x5dat#.accounts AS a ON (a.uid = s.uid)
          WHERE  a.uid IS NULL");
    if (($count = $res->fetchOneCell())) {
        $duration = time() - $begin;
        echo "Orphaned sessions: found $count orphaned sessions in $duration seconds. Please fix that.\n";
    }
}

/**
 * Purges session events without a valid session.
 */
function purgeOrphanedEvents() {
    $begin = time();
    XDB::execute(
        "DELETE  e
           FROM  log_events AS e
      LEFT JOIN  log_sessions AS s ON (s.id = e.session)
          WHERE  s.id IS NULL");
    $affectedRows = XDB::affectedRows();
    $duration = time() - $begin;
    echo "Orphaned events: removed $affectedRows events in $duration seconds.\n";
}

// Remove expired sessions.
//  For normal user, we only keep 12 months of data (and at least the last two sessions).
//  For administrator, we also keep data for 12 months, but with a backlog of at least 20 sessions.
//  For disabled users, we keep data for 5 years, and with a backlog of at least 2 sessions.
//  For other users, no data are discarded.
discardExpiredSessions('user', 12, 2);
discardExpiredSessions('admin', 12, 20);
discardExpiredSessions('disabled', 60, 2);

// Purge orphaned entries; events are purged automatically, sessions require explicit
// action from the administrator.
checkOrphanedSessions();
purgeOrphanedEvents();

// Many rows have been removed from the two logger tables. Let's optimize them.
XDB::execute("OPTIMIZE TABLE log_events");
XDB::execute("OPTIMIZE TABLE log_sessions");

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
