DROP TABLE IF EXISTS profile_job_enum;

CREATE TABLE IF NOT EXISTS profile_job_enum (
  id INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL DEFAULT '',
  acronym VARCHAR(255) NOT NULL DEFAULT '',
  url VARCHAR(255) NOT NULL DEFAULT '',
  holdingid INT(6) UNSIGNED DEFAULT NULL,
  NAF_code CHAR(5) NOT NULL DEFAULT '',
  AX_code BIGINT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (name)
) CHARSET=utf8;

DROP TABLE IF EXISTS profile_job;

CREATE TABLE IF NOT EXISTS profile_job (
  id TINYINT(1) UNSIGNED NOT NULL,
  uid INT(11) NOT NULL DEFAULT 0,
  jobid INT(6) UNSIGNED NOT NULL DEFAULT 0,
  functionid SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0,
  sectorid TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subsectorid TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  subsubsectorid SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0,
  description VARCHAR(255) NOT NULL DEFAULT '',
  url VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  pub ENUM('private', 'ax', 'public') DEFAULT 'private',
  email_pub ENUM('private', 'ax', 'public') DEFAULT 'private',
  PRIMARY KEY (uid, id)
) CHARSET=utf8;

INSERT INTO  profile_job_enum (name)
     SELECT  DISTINCT(entreprise)
       FROM  entreprises;

INSERT INTO  profile_job (id, uid, jobid, description, email, pub, email_pub, functionid, url)
     SELECT  e.entrid, e.uid, j.id, e.poste, e.email, e.pub, e.email_pub, e.fonction, e.web
       FROM  entreprises      AS e
 INNER JOIN  profile_job_enum AS j ON (e.entreprise = j.name);


-- vim:set syntax=mysql:
