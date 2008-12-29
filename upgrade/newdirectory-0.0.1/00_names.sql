ALTER TABLE profile_display ADD COLUMN yourself VARCHAR(255) NOT NULL,
                            ADD COLUMN public_name VARCHAR(255) NOT NULL,
                            ADD COLUMN private_name VARCHAR(255) NOT NULL,
                            ADD COLUMN directory_name VARCHAR(255) NOT NULL,
                            ADD COLUMN short_name VARCHAR(255) NOT NULL,
                            ADD COLUMN sort_name VARCHAR(255) NOT NULL,
                            CHANGE COLUMN uid pid INT(11),
                            CHANGE COLUMN promo_display promo VARCHAR(255);

REPLACE INTO  profile_display (pid, yourself, public_name, private_name, directory_name, short_name, sort_name)
      SELECT  u.user_id, u.prenom,
              CONCAT(u.prenom, ' ', IF(u.nom_usage != '', CONCAT(u.nom_usage, ' (', u.nom, ')') , u.nom)),
              CONCAT(u.prenom, ' ', IF(u.nom_usage != '', CONCAT(u.nom_usage, ' (', u.nom, ')') , u.nom),
                IF(q.profile_nick != '', CONCAT(' (alias ', q.profile_nick, ')'), '')),
              CONCAT(IF(u.nom_usage != '', CONCAT(u.nom_usage, ' (', u.nom, ')') , u.nom), ', ', u.prenom),
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

INSERT INTO  profile_name_search_enum (name, flags)
     VALUES  ('Nom patronymique', 'has_particle,always_displayed,public'),
             ('Nom marital', 'has_particle,always_displayed,public'),
             ('Nom usuel', 'has_particle,always_displayed,public'),
             ('Prénom', 'always_displayed,public'),
             ('Pseudonyme (nom de plume)', 'always_displayed,public'),
             ('Surnom', ''),
             ('Prénom usuel', 'public'),
             ('Autre prénom', ''),
             ('Autre nom', ''),
             ('Nom initial', 'has_particle,not_displayed,public'),
             ('Prénom initial', 'has_particle,not_displayed,public');


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
