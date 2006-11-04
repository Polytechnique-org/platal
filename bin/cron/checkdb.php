#!/usr/bin/php4 -q
<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
/*
 * verifie qu'il n'y a pas d'incoherences dans les tables de jointures
 * 
*/ 

require('./connect.db.inc.php');
require("Console/Getopt.php");

function check($sql, $commentaire='') {
    $res = mysql_query($sql);
    if ($err = mysql_error()) echo $err;
    if (mysql_num_rows($res)>0) {
        echo "Erreur pour la verification : $commentaire\n$sql\n\n";
        echo "|";
        while($col = mysql_fetch_field($res)) echo "\t".$col->name."\t|";
        echo "\n";

        while ($arr = mysql_fetch_row($res)) {
            echo "|";
            foreach ($arr as $val) echo "\t$val\t|";
            echo "\n";
        } 
        echo "\n";
    }
}

function info($sql,$commentaire='') {
    global $opt_verbose;
    if ($opt_verbose)
        check($sql,$commentaire);
}

/* on parse les options */
$opts = Console_GetOpt::getopt($argv, "v");
$opt_verbose=false;

if ( PEAR::isError($opts) ) {
    echo $opts->getMessage();
} else {
    $opts = $opts[0];
    foreach ( $opts as $opt) {
        switch ($opt[0]) {
            case "v":
                $opt_verbose=true;
            echo "Mode verbeux\n";
            break;
        }
    }
}

/* validite de adresses */
check("select uid, adrid from adresses where FIND_IN_SET('pro',statut)","Utilisateurs ayant encore une adresse pro dans leurs adresses");
check("select uid, adrid from adresses group by uid having count(adrid) > 7", "Utilisateurs ayant trop d'adresses");

/* Validite de entreprises */
check("select uid, entrid from entreprises group by uid having count(entrid) > 2","Utilisateurs ayant trop d'entreprises");

/* Validite des tables de langues, competences, mentoring*/
check("select uid, lid from langues_ins group by uid having count(lid) > 10","Utilisateurs ayant trop de langues");
check("select uid, cid from competences_ins group by uid having count(cid) > 20","Utilisateurs ayant trop de competences");

/* validite de aliases */
check("SELECT a.*
        FROM aliases       AS a
        LEFT JOIN auth_user_md5 AS u ON u.user_id=a.id
        WHERE (a.type='alias' OR a.type='a_vie') AND u.prenom is null");

/* validite de applis_ins */
check("select a.* from applis_ins as a left join auth_user_md5 as u on u.user_id=a.uid where u.prenom is null");
check("select a.* from applis_ins as a left join applis_def as ad on ad.id=a.aid where ad.text is null");

/* validite de binet_users */
check("select b.* from binets_ins as b left join auth_user_md5 as u on u.user_id=b.user_id where u.prenom is null");
check("select b.* from binets_ins as b left join binets_def as bd on bd.id=b.binet_id where bd.text is null");

/* validite de contacts */
check("select c.* from contacts as c left join auth_user_md5 as u on u.user_id=c.uid where u.prenom is null");
check("select c.* from contacts as c left join auth_user_md5 as u on u.user_id=c.contact where u.prenom is null");

/* validite de emails */
check("select e.* from emails as e left join auth_user_md5 as u on u.user_id=e.uid where e.uid and u.prenom is null");

/* validite de forums */
check("select f.* from forums.abos as f left join auth_user_md5 as u on u.user_id=f.uid where u.prenom is null");
check("select f.* from forums.abos as f left join forums.list as fd on fd.fid=f.fid where fd.nom is null");
check("select f.* from forums.respofaqs as f left join forums.list as fd on fd.fid=f.fid where fd.nom is null");
check("select f.* from forums.respofaqs as f left join auth_user_md5 as u on u.user_id=f.uid where u.prenom is null");

/* validite de groupesx_ins */
check("select g.* from groupesx_ins as g left join auth_user_md5 as u on u.user_id=g.guid where u.prenom is null");
check("select g.* from groupesx_ins as g left join groupesx_def as gd on g.gid=g.gid where gd.text is null");

/* validite de photo */
check("select p.* from photo as p left join auth_user_md5 as u on u.user_id=p.uid where u.prenom is null");

/* validite des champ pays et region */
check("SELECT a.uid, a.country FROM adresses AS a LEFT JOIN geoloc_pays AS gp ON a.country = gp.a2 WHERE gp.pays IS NULL","donne la liste des pays dans les profils qui n'ont pas d'entree correspondante dans geoloc_pays");
/* les régions ne sont valides que dans les adresses pros */
check("SELECT e.uid, e.country, e.region FROM entreprises AS e LEFT JOIN geoloc_region AS gr ON (e.country = gr.a2 AND e.region = gr.region) WHERE e.region != '' AND gr.name IS NULL","donne la liste des regions dans les profils pros qui n'ont pas d'entree correspondante dans geoloc_region");

/* donne la liste des emails douteux que les administrateurs n'ont pas encore traité */
check("SELECT  a1.alias, a2.alias, e1.email, e2.flags
        FROM  emails        AS e1
        INNER JOIN  emails        AS e2 ON(e1.email = e2.email AND e1.uid!=e2.uid AND 
            (e1.uid<e2.uid  OR  NOT FIND_IN_SET(e2.flags,'active'))
            )
        INNER JOIN  emails_watch  AS w  ON(w.email = e1.email AND w.state = 'pending')
        INNER JOIN  aliases       AS a1 ON(a1.id=e1.uid AND a1.type='a_vie')
        INNER JOIN  aliases       AS a2 ON(a2.id=e2.uid AND a2.type='a_vie')
        INNER JOIN  auth_user_md5 AS u1 ON(a1.id=u1.user_id)
        INNER JOIN  auth_user_md5 AS u2 ON(a2.id=u2.user_id)
        WHERE  FIND_IN_SET(e1.flags,'active') AND u1.nom!=u2.nom_usage AND u2.nom!=u1.nom_usage
        ORDER BY  a1.alias",
        "donne la liste des emails douteux actuellement non traites par les administrateurs");

/* donne la liste des emails dangereux ou douteux*/
info("SELECT  a1.alias, a2.alias, e1.email, e2.flags, w.state
        FROM  emails        AS e1
        INNER JOIN  emails        AS e2 ON(e1.email = e2.email AND e1.uid!=e2.uid AND 
            (e1.uid<e2.uid  OR  NOT FIND_IN_SET(e2.flags,'active'))
            )
        INNER JOIN  emails_watch  AS w  ON(w.email = e1.email AND w.state != 'safe')
        INNER JOIN  aliases       AS a1 ON(a1.id=e1.uid AND a1.type='a_vie')
        INNER JOIN  aliases       AS a2 ON(a2.id=e2.uid AND a2.type='a_vie')
        INNER JOIN  auth_user_md5 AS u1 ON(a1.id=u1.user_id)
        INNER JOIN  auth_user_md5 AS u2 ON(a2.id=u2.user_id)
        WHERE  FIND_IN_SET(e1.flags,'active') AND u1.nom!=u2.nom_usage AND u2.nom!=u1.nom_usage
        ORDER BY  a1.alias",
        "donne la liste des emails dangereux ou douteux");


/* vérif que tous les inscrits ont bien au moins un email actif */
info("SELECT  u.user_id, a.alias
        FROM  auth_user_md5  AS u 
        INNER JOIN  aliases        AS a ON (u.user_id = a.id AND a.type='a_vie')
        LEFT JOIN  emails         AS e ON(u.user_id=e.uid AND FIND_IN_SET('active',e.flags))
        WHERE  e.uid IS NULL AND u.deces = 0
        ORDER BY u.promo, u.nom, u.prenom",
        "donne les inscrits qui n'ont pas d'email actif");

/* donne la liste des homonymes qui ont un alias égal à leur loginbis depuis plus d'un mois */
check("SELECT  a.alias AS username, b.alias AS loginbis, b.expire
        FROM  aliases AS a
        INNER JOIN  aliases AS b ON ( a.id=b.id AND b.type != 'homonyme' and b.expire < NOW() )
        WHERE  a.type = 'a_vie'",
        "donne la liste des homonymes qui ont un alias égal à leur loginbis depuis plus d'un mois, il est temps de supprimer leur alias");

/* verifie qu'il n'y a pas de gens qui recrivent sur un alias qu'ils n'ont plus */

check("SELECT  a.alias AS a_un_pb, email, rewrite AS broken
        FROM  aliases AS a
        INNER JOIN  emails  AS e ON (a.id=e.uid AND rewrite!='')
        LEFT  JOIN  aliases AS b ON (b.id=a.id AND rewrite LIKE CONCAT(b.alias,'@%') AND b.type!='homonyme')
        WHERE  a.type='a_vie' AND b.type IS NULL","gens qui ont des rewrite sur un alias perdu");

/* validite du champ matricule_ax de la table auth_user_md5 */
check("SELECT  matricule,nom,prenom,matricule_ax,COUNT(matricule_ax) AS c
        FROM  auth_user_md5
        WHERE  matricule_ax != '0'
        GROUP BY  matricule_ax
        having  c > 1", "à chaque personne de l'annuaire de l'AX (identification_ax) doit correspondre AU PLUS UNE personne de notre annuaire (auth_user_md5) -> si ce n'est pas le cas il faut regarder en manuel ce qui ne va pas !");
?>
