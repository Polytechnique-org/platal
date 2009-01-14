DROP TABLE IF EXISTS profile_display;

CREATE TABLE  profile_display (
  pid INT(11) NOT NULL DEFAULT 0,
  yourself VARCHAR(255) NOT NULL,
  public_name VARCHAR(255) NOT NULL,
  private_name VARCHAR(255) NOT NULL,
  directory_name VARCHAR(255) NOT NULL,
  short_name VARCHAR(255) NOT NULL,
  sort_name VARCHAR(255) NOT NULL,
  promo VARCHAR(255) DEFAULT '' NOT NULL,
  PRIMARY KEY(pid)
) CHARSET=utf8;

INSERT INTO  profile_display (pid, yourself, public_name, private_name, directory_name, short_name, sort_name)
     SELECT  u.user_id, u.prenom,
             CONCAT(u.prenom, ' ', IF(u.nom_usage != '', CONCAT(u.nom_usage, ' (', u.nom, ')') , u.nom)),
             CONCAT(u.prenom, ' ', IF(u.nom_usage != '', CONCAT(u.nom_usage, ' (', u.nom, ')') , u.nom),
               IF(q.profile_nick != '', CONCAT(' (alias ', q.profile_nick, ')'), '')),
             CONCAT(IF(u.nom_usage != '', CONCAT(u.nom_usage, ' (', u.nom, ')') , u.nom), ' ', u.prenom),
             CONCAT(u.prenom, ' ', IF(u.nom_usage != '', u.nom_usage, u.nom)),
             CONCAT(IF(u.nom_usage != '', u.nom_usage, u.nom), ' ', u.prenom)
       FROM  auth_user_md5   AS u
  LEFT JOIN  auth_user_quick AS q ON (u.user_id = q.user_id);


DROP TABLE IF EXISTS profile_name_search_enum;

CREATE TABLE IF NOT EXISTS profile_name_search_enum (
  id TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  explanations VARCHAR(255) NOT NULL,
  flags SET('has_particle', 'not_displayed', 'always_displayed', 'public') NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (name)
) CHARSET=utf8;

INSERT INTO  profile_name_search_enum (name, flags, explanations)
     VALUES  ('Nom patronymique', 'has_particle,always_displayed,public', 'Le nom de famille avec lequel tu es né'),
             ('Nom marital', 'has_particle,always_displayed,public',
              'Ton nom d\'épouse ou d\'époux'),
             ('Nom usuel', 'has_particle,always_displayed,public',
              'Le nom de famille que tu utilises usuellement s\'il est différent du nom patronymique, ce peut-être une  version racourcie de celui-ci, ton nom marital, une combianaison de ces deux noms...'),
             ('Prénom', 'always_displayed,public', 'Ton prénom'),
             ('Pseudonyme (nom de plume)', 'always_displayed,public',
              'Pseudonyme pour les artistes, gens de lettres'),
             ('Surnom', '', 'Surnom à l\'École ou ailleurs'),
             ('Prénom usuel', 'public', 'Si tu utilises une version raccourcie, francisée... de ton prénom'),
             ('Autre prénom', '', 'Si tu as d\'autres prénoms et que tu souhaites les faire apparaître'),
             ('Autre nom', '', 'Si tu as d\'autres noms et que tu souhaites les faire apparaître'),
             ('Nom initial', 'has_particle,not_displayed,public', ''),
             ('Prénom initial', 'has_particle,not_displayed,public', '');


DROP TABLE IF EXISTS profile_name_search;

CREATE TABLE IF NOT EXISTS profile_name_search (
  id TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'id of this name for this user',
  pid INT(11) NOT NULL COMMENT 'id of alumni',
  name VARCHAR(255) NOT NULL COMMENT 'name to search for',
  particle VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'particle of the name to search for',
  typeid TINYINT(2) UNSIGNED NOT NULL COMMENT 'type of name',
  PRIMARY KEY (pid, id)
) CHARSET=utf8 COMMENT = 'Names of alumni (search table)';

INSERT INTO  profile_name_search (pid, name, typeid)
     SELECT  u.user_id, u.nom, e.id
       FROM  auth_user_md5            AS u
 INNER JOIN  profile_name_search_enum AS e ON (e.name = 'Nom patronymique')
      WHERE  nom != '';

INSERT INTO  profile_name_search (pid, name, typeid)
     SELECT  u.user_id, u.nom_ini, e.id
       FROM  auth_user_md5            AS u
 INNER JOIN  profile_name_search_enum AS e ON (e.name = 'Nom initial')
      WHERE  nom_ini != '';

INSERT INTO  profile_name_search (pid, name, typeid)
     SELECT  u.user_id, u.prenom, e.id
       FROM  auth_user_md5            AS u
 INNER JOIN  profile_name_search_enum AS e ON (e.name = 'Prénom')
      WHERE  prenom != '';

INSERT INTO  profile_name_search (pid, name, typeid)
     SELECT  u.user_id, u.prenom_ini, e.id
       FROM  auth_user_md5            AS u
 INNER JOIN  profile_name_search_enum AS e ON (e.name = 'Prénom initial')
      WHERE  prenom_ini != '';

INSERT INTO  profile_name_search (pid, name, typeid)
     SELECT  u.user_id, u.nom_usage, e.id
       FROM  auth_user_md5            AS u
 INNER JOIN  profile_name_search_enum AS e ON (e.name = 'Nom usuel')
      WHERE  nom_usage != '';

INSERT INTO  profile_name_search (pid, name, typeid)
     SELECT  q.user_id, q.profile_nick, e.id
       FROM  auth_user_quick          AS q
 INNER JOIN  profile_name_search_enum AS e ON (e.name = 'Surnom')
      WHERE  profile_nick != '';

-- vim:set syntax=mysql:
