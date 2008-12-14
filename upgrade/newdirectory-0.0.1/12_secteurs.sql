DROP TABLE IF EXISTS tmp_sectors;

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sectors (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sect CHAR(1) NOT NULL,
  subsector TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  subsubsector TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id)
) CHARSET=utf8;

LOAD DATA LOCAL INFILE 'rome-v3.csv' INTO TABLE  tmp_sectors FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'
(sect, subsector, subsubsector, name);

ALTER TABLE tmp_sectors ADD INDEX (sect);
ALTER TABLE tmp_sectors ADD INDEX (subsector);
ALTER TABLE tmp_sectors ADD INDEX (subsubsector);
ALTER TABLE tmp_sectors ADD INDEX (name);

DROP TABLE IF EXISTS profile_job_sector_enum;

CREATE TABLE IF NOT EXISTS profile_job_sector_enum (
  id TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(256) NOT NULL DEFAULT '',
  sect CHAR(1) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(name)
) CHARSET=utf8;

INSERT INTO  profile_job_sector_enum (name, sect)
     SELECT  name, sect
       FROM  tmp_sectors
      WHERE  subsector = 0 AND subsubsector = 0
   ORDER BY  id;

DROP TABLE IF EXISTS profile_job_subsector_enum;

CREATE TABLE IF NOT EXISTS profile_job_subsector_enum (
  id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  sectorid TINYINT(2) UNSIGNED NOT NULL,
  flags SET('optgroup') DEFAULT '' NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  sect CHAR(1) NOT NULL,
  subsector TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
) CHARSET=utf8;

INSERT INTO  profile_job_subsector_enum (sectorid, name, sect, subsector)
     SELECT  s.id, t.name, t.sect, t.subsector
       FROM  tmp_sectors             AS t
 INNER JOIN  profile_job_sector_enum AS s ON (t.sect = s.sect)
      WHERE  t.subsector != 0 AND t.subsubsector = 0
   ORDER BY  t.id;

    UPDATE  profile_job_subsector_enum AS su
INNER JOIN  profile_job_sector_enum    AS se
       SET  flags = 'optgroup'
     WHERE  (se.name = "Industrie" AND (su.name = "Logistique"
                                        OR su.name = "Transport aérien et activités aéroportuaires"
                                        OR su.name = "Transport maritime et fluvial et activités portuaires"
                                        OR su.name = "Transport terrestre"))
            OR (se.name = "Transport et logistique" AND (su.name = "Études et supports techniques à l'industrie"
                                                         OR su.name = "Production industrielle"));

DROP TABLE IF EXISTS profile_job_subsubsector_enum;

CREATE TABLE IF NOT EXISTS profile_job_subsubsector_enum (
  id SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  sectorid TINYINT(2) UNSIGNED NOT NULL,
  subsectorid TINYINT(3) UNSIGNED NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id),
  UNIQUE KEY(name)
) CHARSET=utf8;

INSERT INTO  profile_job_subsubsector_enum (sectorid, subsectorid, name)
     SELECT  s.sectorid, s.id, t.name
       FROM  tmp_sectors                AS t
 INNER JOIN  profile_job_subsector_enum AS s ON (t.sect = s.sect AND t.subsector = s.subsector)
      WHERE  t.subsector != 0 AND t.subsubsector != 0
   ORDER BY  t.id;

ALTER TABLE profile_job_sector_enum DROP COLUMN sect;
ALTER TABLE profile_job_subsector_enum DROP COLUMN sect;
ALTER TABLE profile_job_subsector_enum DROP COLUMN subsector;


ALTER TABLE profile_phones CHANGE COLUMN link_type link_type ENUM('address','pro','user', 'hq') NOT NULL DEFAULT 'user';

-- vim:set syntax=mysql:
