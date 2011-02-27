#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2011 Polytechnique.org                              *
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
/* Checks inconsistances in tables and joins. */

require './connect.db.inc.php';
require 'Console/Getopt.php';

function check($sql, $comment = '')
{
    $it = XDB::iterRow($sql);
    if ($err = XDB::error()) {
        echo $err;
    }
    if ($it->total() > 0) {
        echo "Erreur pour la vérification : $comment\n$sql\n\n";
        echo "|";
        while($col = $it->nextField()) {
            echo "\t" . $col->name . "\t|";
        }
        echo "\n";

        while ($arr = $it->next()) {
            echo "|";
            foreach ($arr as $val) {
                echo "\t$val\t|";
            }
            echo "\n";
        }
        echo "\n";
    }
}

function checkCount($sql, $comment = '')
{
    $count = XDB::rawFetchOneCell($sql);
    if ($err = XDB::error()) {
        echo $err;
    }
    if ($count > 0) {
        echo "Erreur pour la vérification : $comment\n$sql\n\n";
        echo "|\tTotal\t|\n|\t$count\t|\n\n";
    }
}

function info($sql, $comment = '', $onlyCounts = false)
{
    global $opt_verbose;
    if ($opt_verbose) {
        if ($onlyCounts) {
            checkCount($sql, $comment);
        } else {
            check($sql, $comment);
        }
    }
}

function infoCountEmpty($table, $field, $nonEmpty = false)
{
    $sql = "SELECT COUNT(*) FROM $table";
    if ($nonEmpty) {
        $sql .= " WHERE $field IS NOT NULL OR $field != ''";
        $negation = ' non';
    } else {
        $sql .= " WHERE $field IS NULL OR $field = ''";
        $negation = '';
    }
    $comment = "Nombre de champs '$field'$negation vides dans la table '$table'.";
    info($sql, $comment, true);
}

/* Parses options. */
$opts = Console_GetOpt::getopt($argv, 'v');
$opt_verbose = false;

if (PEAR::isError($opts)) {
    echo $opts->getMessage();
} else {
    $opts = $opts[0];
    foreach ($opts as $opt) {
        switch ($opt[0]) {
          case 'v':
            $opt_verbose = true;
            break;
        }
    }
}

/* Checks rewriting on deleted aliases. */
check("SELECT  s1.email, r.redirect, r.rewrite AS broken
         FROM  email_redirect_account AS r
   INNER JOIN  email_source_account   AS s1 ON (r.uid = s1.uid AND s1.type = 'forlife')
    LEFT JOIN  email_source_account   AS s2 ON (r.uid = s2.uid AND r.rewrite LIKE CONCAT(s2.email, '@%'))
        WHERE  r.rewrite != '' AND s2.uid IS NULL",
      "Personnes qui ont des rewrite sur un alias perdu.");

/* Lists unsound emails that remain unprocessed by the administrators. */
check("SELECT  s1.email, s2.email, w.email
         FROM  email_watch            AS w
   INNER JOIN  email_redirect_account AS r1 ON (w.email = r1.redirect)
    LEFT JOIN  email_redirect_account AS r2 ON (w.email = r2.redirect AND r1.uid != r2.uid)
   INNER JOIN  email_source_account   AS s1 ON (s1.uid = r1.uid AND s1.type = 'forlife')
    LEFT JOIN  email_source_account   AS s2 ON (s2.uid = r2.uid AND s2.type = 'forlife')
        WHERE  w.state = 'pending'
     GROUP BY  w.email
     ORDER BY  w.email",
      "Donne la liste des emails douteux actuellement non traites par les administrateurs.");

/* Lists dangerous and unsound emails. */
info("SELECT  s1.email, s2.email, w.email, w.state
        FROM  email_watch            AS w
  INNER JOIN  email_redirect_account AS r1 ON (w.email = r1.redirect)
   LEFT JOIN  email_redirect_account AS r2 ON (w.email = r2.redirect AND r1.uid != r2.uid)
  INNER JOIN  email_source_account   AS s1 ON (s1.uid = r1.uid AND s1.type = 'forlife')
   LEFT JOIN  email_source_account   AS s2 ON (s2.uid = r2.uid AND s2.type = 'forlife')
       WHERE  w.state != 'safe'
    GROUP BY  w.email
    ORDER BY  w.email",
     "Donne la liste des emails dangereux ou douteux.");

/* Lists homonyms who have an alias equals to their loginbis for more than a month. */
check("SELECT  e.email AS homonym, f.email AS forlife, e.expire
         FROM  email_source_account  AS e
   INNER JOIN  homonyms_list         AS l ON (e.uid = l.uid)
   INNER JOIN  homonyms_list         AS h ON (l.hrmid = h.hrmid)
   INNER JOIN  email_source_account  AS f ON (h.uid = f.uid AND f.type = 'forlife')
        WHERE  e.expire < NOW()
     ORDER BY  homonym, forlife",
      "Donne la liste des homonymes qui ont un alias égal à leur loginbis depuis plus d'un mois, il est temps de supprimer leur alias.");

// Counts empty profile fields that should never be empty.
infoCountEmpty('profile_addresses', 'text');
infoCountEmpty('profile_addresses', 'postalText');
infoCountEmpty('profile_education', 'eduid');
infoCountEmpty('profile_education', 'degreeid');
infoCountEmpty('profile_job', 'jobid');
infoCountEmpty('profile_mentor', 'expertise');
infoCountEmpty('profile_networking', 'address');
infoCountEmpty('profile_phones', 'search_tel');
infoCountEmpty('profile_phones', 'display_tel');

// XXX: counts the number of remaining issues due to the merge (to be removed once all merge related issues have been fixed)
infoCountEmpty('profile_merge_issues', 'issues', true);

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
