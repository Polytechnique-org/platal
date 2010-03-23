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
  subsectorid TINYINT(3) UNSIGNED NOT NULL,
  PRIMARY KEY (pid, sectorid, subsectorid),
  INDEX pid (pid)
) ENGINE=InnoDB, CHARSET=utf8;

-- vim:set syntax=mysql:
