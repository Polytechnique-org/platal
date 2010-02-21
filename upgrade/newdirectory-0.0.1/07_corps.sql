DROP TABLE IF EXISTS profile_corps;
DROP TABLE IF EXISTS profile_corps_enum;
DROP TABLE IF EXISTS profile_corps_rank_enum;

CREATE TABLE IF NOT EXISTS profile_corps (
  uid INT(11) NOT NULL,
  original_corpsid INT(4) UNSIGNED NOT NULL DEFAULT 0,
  current_corpsid INT(4) UNSIGNED NOT NULL DEFAULT 0,
  rankid INT(4) UNSIGNED NOT NULL DEFAULT 0,
  corps_pub ENUM('private', 'ax', 'public') NOT NULL DEFAULT 'private',
  PRIMARY KEY(uid)
) ENGINE=InnoDB, CHARSET=utf8;


CREATE TABLE IF NOT EXISTS profile_corps_enum (
  id INT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL DEFAULT '',
  abbreviation CHAR(5) NOT NULL DEFAULT '',
  still_exists TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY(id),
  UNIQUE KEY(name),
  UNIQUE KEY(abbreviation)
) ENGINE=InnoDB, CHARSET=utf8;


CREATE TABLE IF NOT EXISTS profile_corps_rank_enum (
  id INT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL DEFAULT '',
  abbreviation CHAR(5) NOT NULL DEFAULT '',
  PRIMARY KEY(id),
  UNIQUE KEY(name),
  UNIQUE KEY(abbreviation)
) ENGINE=InnoDB, CHARSET=utf8;

    UPDATE  profile_corps      AS c
INNER JOIN  profile_corps_enum AS e ON (c.original_corpsid = e.id)
 LEFT JOIN  profile_corps_enum AS a ON (a.name = "Aucun (anc. démissionnaire)")
       SET  c.original_corpsid = a.id
     WHERE  e.name = "Ancien élève étranger";

UPDATE  profile_corps_enum
   SET  name = "Aucun"
 WHERE  name = "Aucun (anc. démissionnaire)";

DELETE FROM  profile_corps_enum
      WHERE  name = "Ancien élève étranger";


ALTER TABLE watch_profile MODIFY field enum('nom', 'freetext', 'mobile', 'nationalite', 'nationalite2',
                                            'nationalite3', 'nick', 'web', 'networking', 'edus', 'addresses',
                                            'section', 'binets', 'medals', 'cv', 'jobs', 'photo', 'corps');

--  vim:set syntax=mysql:
