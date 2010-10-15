#!/usr/bin/php5 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2010 Polytechnique.org                              *
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
/* Checks inconsistances in tables ans joins. */

require './connect.db.inc.php';
require 'Console/Getopt.php';

function check($sql, $comment = '')
{
    $it = XDB::iterRow($sql);
    if ($err = XDB::error()) {
        echo $err;
    }
    if ($it->total() > 0) {
        echo "Erreur pour la verification : $comment\n$sql\n\n";
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

function info($sql, $comment = '') {
    global $opt_verbose;
    if ($opt_verbose) {
        check($sql, $comment);
    }
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
check("SELECT  a.alias, e.email, e.rewrite AS broken
         FROM  aliases AS a
   INNER JOIN  emails  AS e ON (a.uid = e.uid AND rewrite != '')
    LEFT JOIN  aliases AS b ON (b.uid = a.uid AND rewrite LIKE CONCAT(b.alias, '@%') AND b.type != 'homonyme')
        WHERE  a.type = 'a_vie' AND b.type IS NULL",
      "Personnes qui ont des rewrite sur un alias perdu.");

/* Lists unsound emails that remain unprocessed by the administrators. */
check("SELECT  a1.alias, a2.alias, e1.email, e2.flags
         FROM  emails      AS e1
   INNER JOIN  emails      AS e2 ON (e1.email = e2.email AND e1.uid != e2.uid AND
                                     (e1.uid < e2.uid OR NOT FIND_IN_SET('active', e2.flags)))
   INNER JOIN  email_watch AS w  ON (w.email = e1.email AND w.state = 'pending')
   INNER JOIN  aliases     AS a1 ON (a1.uid = e1.uid AND a1.type = 'a_vie')
   INNER JOIN  aliases     AS a2 ON (a2.uid = e2.uid AND a2.type = 'a_vie')
        WHERE  FIND_IN_SET('active', e1.flags)
     ORDER BY  a1.alias",
      "Donne la liste des emails douteux actuellement non traites par les administrateurs.");

/* Lists dangerous and unsound emails. */
info("SELECT  a1.alias, a2.alias, e1.email, e2.flags, w.state
        FROM  emails      AS e1
  INNER JOIN  emails      AS e2 ON (e1.email = e2.email AND e1.uid != e2.uid AND
                                    (e1.uid < e2.uid OR NOT FIND_IN_SET('active', e2.flags)))
  INNER JOIN  email_watch AS w  ON (w.email = e1.email AND w.state != 'safe')
  INNER JOIN  aliases     AS a1 ON (a1.uid = e1.uid AND a1.type = 'a_vie')
  INNER JOIN  aliases     AS a2 ON (a2.uid = e2.uid AND a2.type = 'a_vie')
       WHERE  FIND_IN_SET('active', e1.flags)
    ORDER BY  a1.alias",
     "Donne la liste des emails dangereux ou douteux.");

/* Lists homonyms who have an alias equals to their loginbis for more than a month. */
check("SELECT  a.alias AS username, b.alias AS loginbis, b.expire
         FROM  aliases AS a
   INNER JOIN  aliases AS b ON (a.uid=b.uid AND b.type != 'homonyme' and b.expire < NOW())
        WHERE  a.type = 'a_vie'",
      "Donne la liste des homonymes qui ont un alias égal à leur loginbis depuis plus d'un mois, il est temps de supprimer leur alias.");

// XXX: counts the number of remaining issues due to the merge (to be removed once all merge related issues have been fixed)
check('SELECT  COUNT(*)
         FROM  profile_merge_issues
        WHERE  issues IS NULL OR issues = \'\'',
      'Dénombre les erreurs dues à la fusion restantes.');

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
