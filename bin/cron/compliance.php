#!/usr/bin/php5 -q
<?php
/***************************************************************************
*  Copyright (C) 2003-2009 Polytechnique.org                              *
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

/**
 * Discards from the logger database session details for users.
 *
 * @param userPerms Permission level to use for that round of discards.
 * @param retentionPeriod Minimal number of months to keep entries for.
 * @param minimalBacklog Minimal number of entries to keep for any given user,
 *      no matter what.
 */
function discardExpiredSessions($userPerms, $retentionPeriod, $minimalBacklog) {
    XDB::execute(
        "DELETE  logger.s
           FROM  logger.sessions AS s
           JOIN  (SELECT  u.user_id,
                          (SELECT  us.start
                             FROM  logger.sessions AS us
                            WHERE  us.uid = u.user_id
                         ORDER BY  us.start DESC
                            LIMIT  {?}, 1) AS no_discard_limit
                    FROM  x4dat.auth_user_md5 AS u
                   WHERE  u.perms = {?}
                ORDER BY  u.user_id ASC) AS ut ON (ut.user_id = s.uid)
          WHERE  s.start < DATE_SUB(NOW(), INTERVAL {?} MONTH)
                 AND s.start < ut.no_discard_limit",
        $minimalBacklog - 1, $userPerms, $retentionPeriod);

    $affectedRows = XDB::affectedRows();
    echo "Users with permission '$userPerms': removed $affectedRows sessions.\n";
}

/**
 * Checks for sessions without a valid associated user id.
 */
function checkOrphanedSessions() {
    $res = XDB::query(
        "SELECT  COUNT(*)
           FROM  logger.sessions AS s
      LEFT JOIN  x4dat.auth_user_md5 AS u ON (u.user_id = s.uid)
          WHERE  u.user_id IS NULL");
    if (($count = $res->fetchOneCell())) {
        echo "Orphaned sessions: found $count orphaned sessions. Please fix that.\n";
    }
}

/**
 * Purges session events without a valid session.
 */
function purgeOrphanedEvents() {
    XDB::execute(
        "DELETE  logger.e
           FROM  logger.events AS e
      LEFT JOIN  logger.sessions AS s ON (s.id = e.session)
          WHERE  s.id IS NULL");
    $affectedRows = XDB::affectedRows();
    echo "Orphaned events: removed $affectedRows events.\n";
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
XDB::execute("OPTIMIZE TABLE logger.events");
XDB::execute("OPTIMIZE TABLE logger.sessions");

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>