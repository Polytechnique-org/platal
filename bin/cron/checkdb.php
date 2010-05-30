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

/* hrid correctness */
check("SELECT  uid, full_name
         FROM  accounts
        WHERE  hruid IS NULL OR hruid = ''",
      "Utilisateur n'ayant pas de hruid.");
check("SELECT  p.pid, pd.public_name, pd.promo
         FROM  profiles        AS p
    LEFT JOIN  profile_display AS pd ON (p.pid = pd.pid)
        WHERE  p.hrpid IS NULL OR p.hrpid = ''",
      "Profil n'ayant pas de hrpid.");

/* No alumni is allowed to have empty names. */
check("SELECT  p.pid, p.hrpid
         FROM  profiles     AS p
   INNER JOIN  profile_name AS pn ON (p.pid = pn.pid)
        WHERE  name = ''",
      "Liste des personnes qui ont un de leur nom de recherche vide.");

/* Checks rewriting on deleted aliases. */
check("SELECT  a.alias, e.email, e.rewrite AS broken
         FROM  aliases AS a
   INNER JOIN  emails  AS e ON (a.uid = e.uid AND rewrite != '')
    LEFT JOIN  aliases AS b ON (b.uid = a.uid AND rewrite LIKE CONCAT(b.alias, '@%') AND b.type != 'homonyme')
        WHERE  a.type = 'a_vie' AND b.type IS NULL",
      "Personnes qui ont des rewrite sur un alias perdu.");

/* Publicity flags correctness */
check("SELECT  p.pid, p.hrpid, p.freetext_pub, p.medals_pub, p.alias_pub,
               pa.pub, pc.corps_pub, pj.pub, pj.email_pub, pn.pub, pp.pub, ph.pub
         FROM  profiles           AS p
    LEFT JOIN  profile_addresses  AS pa ON (p.pid = pa.pid)
    LEFT JOIN  profile_corps      AS pc ON (p.pid = pc.pid)
    LEFT JOIN  profile_job        AS pj ON (p.pid = pj.pid)
    LEFT JOIN  profile_networking AS pn ON (p.pid = pn.pid)
    LEFT JOIN  profile_phones     AS pp ON (p.pid = pp.pid)
    LEFT JOIN  profile_photos     AS ph ON (p.pid = ph.pid)
        WHERE  (p.freetext_pub != 'public' AND p.freetext_pub != 'private')
               OR (p.medals_pub != 'public' AND p.medals_pub != 'private')
               OR (p.alias_pub != 'public' AND p.alias_pub != 'private')
               OR (pa.pub != 'public' AND pa.pub != 'ax' AND pa.pub != 'private')
               OR (pc.corps_pub != 'public' AND pc.corps_pub != 'ax' AND pc.corps_pub != 'private')
               OR (pj.pub != 'public' AND pj.pub != 'ax' AND pj.pub != 'private')
               OR (pj.email_pub != 'public' AND pj.email_pub != 'ax' AND pj.email_pub != 'private')
               OR (pn.pub != 'public' AND pn.pub != 'private')
               OR (pp.pub != 'public' AND pp.pub != 'ax' AND pp.pub != 'private')
               OR (ph.pub != 'public' AND ph.pub != 'private')",
      'Profil ayant des flags de publicité manquant.');

/* Checks profile_*_enum all have a name to describe them. */
check("SELECT  *
         FROM  profile_binet_enum
        WHERE  text IS NULL OR text = ''");
check("SELECT  *
         FROM  profile_corps_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_corps_rank_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_education_degree_enum
        WHERE  degree IS NULL OR degree = ''");
check("SELECT  *
         FROM  profile_education_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_education_field_enum
        WHERE  field IS NULL OR field = ''");
check("SELECT  *
         FROM  profile_job_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_job_sector_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_job_subsector_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_job_subsubsector_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_langskill_enum
        WHERE  langue_fr IS NULL OR langue_fr = ''");
check("SELECT  *
         FROM  profile_medal_enum
        WHERE  text IS NULL OR text = ''");
check("SELECT  *
         FROM  profile_name_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_networking_enum
        WHERE  name IS NULL OR name = ''");
check("SELECT  *
         FROM  profile_section_enum
        WHERE  text IS NULL OR text = ''");
check("SELECT  *
         FROM  profile_skill_enum
        WHERE  text_fr IS NULL OR text_fr = ''");

/* Checks some other tables all have a name to describe them. */
check("SELECT  id, nom, diminutif
         FROM  groups
        WHERE  nom IS NULL OR nom = ''");
check("SELECT  fid, name
         FROM  forums
        WHERE  name IS NULL OR name = ''");

/* Checks phone formating. */
check("SELECT DISTINCT  g.phonePrefix
                  FROM  geoloc_countries AS g
          WHERE EXISTS  (SELECT  h.phonePrefix
                           FROM  geoloc_countries AS h
                          WHERE  h.phonePrefix = g.phonePrefix
                                 AND h.phoneFormat != (SELECT  i.phoneFormat
                                                         FROM  geoloc_countries AS i
                                                        WHERE  i.phonePrefix = g.phonePrefix
                                                        LIMIT  1))",
      "Préfixes téléphoniques qui ont des formats de numéros de téléphones différents selon les pays.");

/* Checks correctness of countries in the profiles. */
check("SELECT  pa.pid, pa.countryId
         FROM  profile_addresses AS pa
    LEFT JOIN  geoloc_countries  AS gc ON (a.countryId = gc.iso_3166_1_a2)
        WHERE  gc.countryFR IS NULL OR gc.countryFR = ''",
      "Donne la liste des pays dans les profils qui n'ont pas d'entrée correspondante dans geoloc_countries.");

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

/* Correctness of ax_id in profiles table. */
check("SELECT  pid, hrpid, ax_id, COUNT(ax_id) AS c
         FROM  profiles
        WHERE  ax_id != '0'
     GROUP BY  ax_id
       HAVING  c > 1",
      "À chaque personne de l'annuaire de l'AX doit correspondre AU PLUS UNE personne de notre annuaire -> si ce n'est pas le cas il faut regarder en manuel ce qui ne va pas !");

/* Checks there is no user with a disactivated Google Apps account and an active redirection towards Google Apps. */
check("SELECT  a.alias, g.g_status, eo.storage
         FROM  email_options  AS eo
   INNER JOIN  aliases        AS a ON (a.uid = eo.uid AND a.type = 'a_vie')
   INNER JOIN  gapps_accounts AS g ON (g.l_userid = eo.uid)
        WHERE  FIND_IN_SET('googleapps', eo.storage) > 0 AND g.g_status != 'active'",
      "Utilisateurs ayant une redirection vers Google Apps alors que leur compte GApps n'est pas actif.");

/* TODO: add check on foreign keys for every table! */

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
