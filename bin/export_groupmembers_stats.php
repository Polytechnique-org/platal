#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2016 Polytechnique.org                              *
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

/**
 * Export some statistics about group members
 *
 * These stats help building a view of the younger generations in X groups
 */

require_once './connect.db.inc.php';

/**
 * Build and show promotion statistics from a query giving UIDs
 */
function show_promo_stats_from_uids($uids_query) {
    $uids = $uids_query->fetchColumn();

    // Do not process one UID twice
    $uids = array_unique($uids);
    //echo count($uids) . " UIDs\n";
    if (!count($uids)) {
        return;
    }

    // Get profiles
    $pids = Profile::getPIDsFromUIDs($uids);
    //echo count($pids) . " PIDs\n";
    if (!count($uids)) {
        return;
    }

    // Count each category of promotions
    $promos_stats = array();
    foreach ($pids as $pid) {
        $profile = Profile::get($pid);
        if ($profile->isDead()) {
            continue;
        }
        $promo = $profile->promo();
        unset($profile);

        if ($promo == '') {
            continue;
        } elseif (preg_match('/^[DMX](19|20)[0-9][0-9]$/', $promo)) {
            $pcat = substr($promo, 0, 4) . 'X';
        } else {
            // For example "D (en cours)"
            $pcat = $promo;
        }

        if (isset($promos_stats[$pcat])) {
            $promos_stats[$pcat] ++;
        } else {
            $promos_stats[$pcat] = 1;
        }
    }
    ksort($promos_stats);
    foreach ($promos_stats as $pcat => $num) {
        echo "  $pcat:$num";
    }
}

// For testing purpose, analyze the composition of the promotion groups!
/*
$promo_groups = XDB::fetchAllAssoc(
    'SELECT  id, nom
       FROM  groups
      WHERE  cat = "Promotions"');
foreach ($promo_groups as $pg) {
    echo "- " . $pg['nom'] . ":";
    $uids_query = XDB::query(
        'SELECT  gm.uid
           FROM  group_members AS gm
          WHERE  gm.asso_id = {?}',
        $pg['id']);
    show_promo_stats_from_uids($uids_query);
    echo "\n";
}
*/

echo "Statistiques tous groupes actifs (hors promo et institutions):\n";
$uids_query = XDB::query(
    'SELECT  gm.uid
       FROM  group_members AS gm
  LEFT JOIN  groups AS g ON (gm.asso_id = g.id)
      WHERE  g.status = "active"
             AND g.cat NOT IN ("Institutions", "Promotions")');
show_promo_stats_from_uids($uids_query);
echo "\n";

echo "Statistiques groupes agréés par l'AX:\n";
$uids_query = XDB::query(
    'SELECT  gm.uid
       FROM  group_members AS gm
  LEFT JOIN  groups AS g ON (gm.asso_id = g.id)
      WHERE  g.status = "active"
             AND g.ax
             AND g.cat NOT IN ("Institutions", "Promotions")');
show_promo_stats_from_uids($uids_query);
echo "\n\n";

echo "Statistiques par catégorie de groupes:\n";
$gdoms = XDB::fetchAllAssoc(
    'SELECT  id, nom
       FROM  group_dom');
foreach ($gdoms as $domain) {
    echo "- " . $domain['nom'] . ":";
    $uids_query = XDB::query(
        'SELECT  gm.uid
           FROM  group_members AS gm
      LEFT JOIN  groups AS g ON (gm.asso_id = g.id)
          WHERE  g.status = "active" AND g.dom = {?}',
        $domain['id']);
    show_promo_stats_from_uids($uids_query);
    echo "\n";
}
