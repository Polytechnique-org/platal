DROP TABLE IF EXISTS profile_job_enum;

CREATE TABLE IF NOT EXISTS profile_job_enum (
  id INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL DEFAULT '',
  acronym VARCHAR(255) NOT NULL DEFAULT '',
  url VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  holdingid INT(6) UNSIGNED DEFAULT NULL,
  NAF_code CHAR(5) NOT NULL DEFAULT '',
  AX_code BIGINT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (name)
) ENGINE=InnoDB, CHARSET=utf8;

DROP TABLE IF EXISTS profile_job;

CREATE TABLE IF NOT EXISTS profile_job (
  id TINYINT(1) UNSIGNED NOT NULL,
  uid INT(11) NOT NULL DEFAULT 0,
  jobid INT(6) UNSIGNED NOT NULL DEFAULT 0,
  sectorid TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subsectorid TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  subsubsectorid SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0,
  description VARCHAR(255) NOT NULL DEFAULT '',
  url VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  pub ENUM('private', 'ax', 'public') DEFAULT 'private',
  email_pub ENUM('private', 'ax', 'public') DEFAULT 'private',
  PRIMARY KEY (uid, id),
  INDEX uid (uid)
) ENGINE=InnoDB, CHARSET=utf8;

INSERT IGNORE INTO  profile_job_enum (name, url)
            SELECT  entreprise, web
              FROM  #x4dat#.entreprises;

INSERT INTO  profile_job (id, uid, jobid, email, pub, email_pub, description)
     SELECT  e.entrid, e.uid, j.id, e.email, e.pub, e.email_pub,
             CONCAT_WS(', ', IF(e.poste = '', NULL, e.poste), IF(e.fonction = 0, NULL, f.fonction_fr),
                       IF(e.ss_secteur IS NULL , IF(e.secteur IS NULL, NULL, s.label), ss.label))
       FROM  #x4dat#.entreprises       AS e
 INNER JOIN  profile_job_enum  AS j  ON (e.entreprise = j.name)
  LEFT JOIN  #x4dat#.fonctions_def     AS f  ON (f.id = e.fonction)
  LEFT JOIN  #x4dat#.emploi_ss_secteur AS ss ON (ss.id = e.ss_secteur)
  LEFT JOIN  #x4dat#.emploi_secteur    AS s  ON (s.id = e.secteur);

-- vim:set syntax=mysql:
