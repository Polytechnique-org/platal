DROP TABLE IF EXISTS profile_mentor;
DROP TABLE IF EXISTS profile_mentor_country;
DROP TABLE IF EXISTS profile_mentor_sector;

CREATE TABLE profile_mentor (
  pid INT(11) NOT NULL DEFAULT 0,
  expertise TEXT NOT NULL,
  PRIMARY KEY (pid)
) ENGINE=InnoDB, CHARSET=utf8;

INSERT INTO  profile_mentor (pid, expertise)
     SELECT  uid, expertise
       FROM  #x4dat#.mentor;

CREATE TABLE profile_mentor_country (
  pid INT(11) NOT NULL DEFAULT 0,
  country CHAR(2) NOT NULL DEFAULT "FR",
  PRIMARY KEY (pid, country),
  INDEX pid (pid)
) ENGINE=InnoDB, CHARSET=utf8;

INSERT INTO  profile_mentor_country (pid, country)
     SELECT  uid, pid
       FROM  #x4dat#.mentor_pays;

CREATE TABLE profile_mentor_sector (
  pid INT(11) NOT NULL DEFAULT 0,
  sectorid TINYINT(2) UNSIGNED NOT NULL,
  subsectorid SMALLINT(3) UNSIGNED NOT NULL,
  PRIMARY KEY (pid, sectorid, subsectorid),
  INDEX pid (pid)
) ENGINE=InnoDB, CHARSET=utf8;

INSERT IGNORE INTO profile_mentor_sector (pid, sectorid, subsectorid)
     SELECT ms4.uid, s.id, ss.id
       FROM #x4dat#.mentor_secteurs AS ms4
 INNER JOIN #x4dat#.emploi_secteur AS s4 ON (s4.id = ms4.secteur)
 INNER JOIN profile_job_sector_enum AS s ON (s.name = s4.label)
  LEFT JOIN #x4dat#.emploi_ss_secteur AS ss4 ON (ss4.id = ms4.ss_secteur)
  LEFT JOIN profile_job_subsector_enum AS ss ON (ss.name = ss4.label);

-- vim:set syntax=mysql:
