#!/usr/bin/php4 -q
<?php
/*
 * verifie qu'il n'y a pas d'incoherences dans les tables de jointures
 * 
 * $Id: checkdb.php,v 1.1 2004-10-02 15:23:38 x2000habouzit Exp $
*/ 

ini_set("include_path","/usr/share/php");

require("../../include/xorg.misc.inc.php");
require("../../include/config.xorg.inc.php");
mysql_connect($globals->dbhost,$globals->dbuser,$globals->dbpwd);
mysql_select_db($globals->dbdb);

require("Console/Getopt.php");

function check($sql, $commentaire='') {
  $res=mysql_query($sql);
  if($err = mysql_error()) echo $err;
  if (mysql_num_rows($res)>0) {
    echo "Erreur pour la verification : $commentaire\n$sql\n\n";
    echo "|";
    while($col = mysql_fetch_field($res)) echo "\t".$col->name."\t|";
    echo "\n";
    
    while ($arr=mysql_fetch_row($res)) {
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
check("select a.* from aliases as a left join auth_user_md5 as u on u.user_id=a.id where (a.type='alias' or a.type='epouse' or a.type='login' or a.type='a_vie') and u.prenom is null");
check("select a.* from aliases as a left join listes_def as ld on ld.id=a.id where a.type like('liste%') and ld.topic is null");

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

/* validite de listes_ins */
check("select l.* from listes_ins as l left join auth_user_md5 as u on u.user_id=l.idu where l.idu and u.prenom is null");
check("select l.* from listes_ins as l left join listes_def as ld on ld.id=l.idl where ld.topic is null");

/* validite de listes_mod */
check("select l.* from listes_mod as l left join auth_user_md5 as u on u.user_id=l.idu where u.prenom is null");
check("select l.* from listes_mod as l left join listes_def as ld on ld.id=l.idl where ld.topic is null");

/* validite de photo */
check("select p.* from photo as p left join auth_user_md5 as u on u.user_id=p.uid where u.prenom is null");

/* validite des champ pays et region */
check("SELECT a.uid, a.pays FROM adresses AS a LEFT JOIN geoloc_pays AS gp ON a.pays = gp.a2 WHERE gp.pays IS NULL","donne la liste des pays dans les profils qui n'ont pas d'entree correspondante dans geoloc_pays");
check("SELECT a.uid, a.pays, a.region FROM adresses AS a LEFT JOIN geoloc_region AS gr ON (a.pays = gr.a2 AND a.region = gr.region) WHERE a.region != '' AND gr.name IS NULL","donne la liste des regions dans les profils qui n'ont pas d'entree correspondante dans geoloc_region");

/* vérifie que les champs promos d'identification et auth_user_md5 coïncident */
check("select a.promo as promo_ok,i.promo as promo_bad,a.matricule,a.nom,a.prenom from auth_user_md5 as a inner join identification as i on a.matricule = i.matricule where a.promo != i.promo", "vérifie que les champs promos d'identification et auth_user_md5 coïncident");

/* donne la liste des gens pour qui on a fait du marketing mais qui se sont inscrits depuis (nettoyage de envoidirect) */
info("select e.matricule,e.nom,e.prenom,e.promo from envoidirect as e inner join auth_user_md5 as a on e.matricule = a.matricule order by promo,nom;");

/* donne la liste des emails qui apparaissent 2 fois dans la table emails pour la même personne */
info("select e1.uid, e1.num, e2.num, e1.email, e1.flags, e2.flags from emails as e1, emails as e2 where e1.uid = e2.uid and e1.email = e2.email and e1.num < e2.num", "donne la liste des emails qui apparaissent 2 fois dans la table emails pour la même personne");
/* donne la liste des emails qui apparaissent 2 fois dans la table emails pour des personnes différentes */
info("SELECT  a1.alias, e1.uid, e1.num, a2.alias, e2.uid, e2.num, e1.email, e1.flags, e2.flags
        FROM  emails  AS e1
  INNER JOIN  emails  AS e2 ON(e1.email = e2.email AND e1.uid < e2.uid)
  INNER JOIN  aliases AS a1 ON(a1.id=e1.uid AND a1.type='a_vie')
  INNER JOIN  aliases AS a2 ON(a2.id=e2.uid AND a2.type='a_vie')",
"donne la liste des emails qui apparaissent 2 fois dans la table emails pour des personnes différentes");

/* vérif que tous les inscrits ont bien au moins un email actif */
check("SELECT  u.user_id, a.alias
	 FROM  auth_user_md5  AS u 
   INNER JOIN  aliases        AS a ON (u.user_id = a.id AND a.type='a_vie')
   INNER JOIN  identification AS i ON (i.matricule = u.matricule AND i.deces = 0)
    LEFT JOIN  emails         AS e ON(u.user_id=e.uid AND FIND_IN_SET('active',e.flags))
        WHERE  e.uid IS NULL",
"donne les inscrits qui n'ont pas d'email actif");

/* donne la liste des homonymes qui ont un alias égal à leur loginbis depuis plus d'un mois */
check("SELECT  a.alias AS username, b.alias AS loginbis, b.expire
         FROM  aliases AS a
   INNER JOIN  aliases AS b ON ( a.id=b.id AND b.type != 'homonyme' and b.expire > NOW() )
        WHERE  a.type = 'a_vie'",
"donne la liste des homonymes qui ont un alias égal à leur loginbis depuis plus d'un mois, il est temps de supprimer leur alias");

/* validite du champ matricule_ax de la table identification */
check("select matricule,nom,prenom,matricule_ax,count(matricule_ax) as c from identification where matricule_ax != '0' group by matricule_ax having c > 1", "à chaque personne de l'annuaire de l'AX (identification_ax) doit correspondre AU PLUS UNE personne de notre annuaire (identification) -> si ce n'est pas le cas il faut regarder en manuel ce qui ne va pas !");
?>
