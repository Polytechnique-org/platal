DROP TABLE IF EXISTS profile_job_sector_enum;

CREATE TABLE profile_job_sector_enum (
  id TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id),
  UNIQUE INDEX(name(128))
) ENGINE=InnoDB, CHARSET=utf8;


DROP TABLE IF EXISTS profile_job_subsector_enum;

CREATE TABLE profile_job_subsector_enum (
  id SMALLINT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
  sectorid TINYINT(2) UNSIGNED NOT NULL,
  flags SET('optgroup') DEFAULT '' NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id)
) ENGINE=InnoDB, CHARSET=utf8;


DROP TABLE IF EXISTS profile_job_subsubsector_enum;

CREATE TABLE profile_job_subsubsector_enum (
  id SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  sectorid TINYINT(2) UNSIGNED NOT NULL,
  subsectorid TINYINT(3) UNSIGNED NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  flags SET('display') NOT NULL DEFAULT 'display',
  PRIMARY KEY(id)
) ENGINE=InnoDB, CHARSET=utf8;


DROP TABLE IF EXISTS profile_job_alternates;

CREATE TABLE profile_job_alternates (
  id TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subsubsectorid SMALLINT(3) UNSIGNED NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id, subsubsectorid),
  UNIQUE INDEX(name(128))
) ENGINE=InnoDB, CHARSET=utf8;


ALTER TABLE profile_phones CHANGE COLUMN link_type link_type ENUM('address','pro','user', 'hq') NOT NULL DEFAULT 'user';

DROP TABLE IF EXISTS profile_job_enum;

CREATE TABLE profile_job_enum (
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

CREATE TABLE profile_job (
  id TINYINT(1) UNSIGNED NOT NULL,
  pid INT(11) NOT NULL DEFAULT 0,
  jobid INT(6) UNSIGNED NOT NULL DEFAULT 0,
  sectorid TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subsectorid SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0,
  subsubsectorid SMALLINT(3) UNSIGNED NOT NULL DEFAULT 0,
  description VARCHAR(255) NOT NULL DEFAULT '',
  url VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  pub ENUM('private', 'ax', 'public') DEFAULT 'private',
  email_pub ENUM('private', 'ax', 'public') DEFAULT 'private',
  PRIMARY KEY (pid, id),
  INDEX pid (pid)
) ENGINE=InnoDB, CHARSET=utf8;

# Fill sector table with old sectors.
INSERT INTO profile_job_sector_enum (name)
     SELECT label
       FROM #x4dat#.emploi_secteur;

INSERT INTO profile_job_subsector_enum (sectorid, name)
     SELECT s.id, ss.label
       FROM #x4dat#.emploi_ss_secteur AS ss
 INNER JOIN #x4dat#.emploi_secteur AS s4 ON (ss.secteur = s4.id)
 INNER JOIN profile_job_sector_enum AS s ON (s.name = s4.label);

# Since subsubsectors are not supported by the old sectors set, define
# a fake value per subsector.
INSERT INTO profile_job_subsubsector_enum (id, sectorid, subsectorid, name)
     SELECT ss.id, ss.sectorid, ss.id, CONCAT(s.name, " (", ss.name, ")")
       FROM profile_job_subsector_enum AS ss
 INNER JOIN profile_job_sector_enum AS s ON (s.id = ss.sectorid)
   ORDER BY ss.id;

INSERT IGNORE INTO  profile_job_enum (name, url)
            SELECT  entreprise, web
              FROM  #x4dat#.entreprises
             WHERE  entreprise != '';

INSERT INTO  profile_job (id, pid, jobid, email, pub, email_pub, sectorid, subsectorid, subsubsectorid, description)
     SELECT  e.entrid, e.uid, j.id, e.email,
             IF(e.pub = '', 'private', e.pub), IF(e.email_pub = '', 'private', e.email_pub),
             IF(s.id IS NOT NULL, s.id, 0), IF(ss.id IS NOT NULL, ss.id, 0),
             IF(ss.id IS NOT NULL, ss.id, 0), e.poste
       FROM  #x4dat#.entreprises       AS e
 INNER JOIN  profile_job_enum  AS j  ON (e.entreprise = j.name)
  LEFT JOIN  #x4dat#.fonctions_def     AS f  ON (f.id = e.fonction)
  LEFT JOIN  #x4dat#.emploi_ss_secteur AS ss4 ON (ss4.id = e.ss_secteur)
  LEFT JOIN  #x4dat#.emploi_secteur    AS s4  ON (s4.id = e.secteur)
  LEFT JOIN  profile_job_sector_enum   AS s   ON (s.name = s4.label)
  LEFT JOIN  profile_job_subsector_enum AS ss ON (ss.name = ss4.label AND ss.sectorid = s.id)
      WHERE  e.entreprise != '';

-- vim:set syntax=mysql:
