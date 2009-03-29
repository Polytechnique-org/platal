DROP TABLE IF EXISTS profile_job_sector_enum;

CREATE TABLE IF NOT EXISTS profile_job_sector_enum (
  id TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id),
  UNIQUE KEY(name)
) CHARSET=utf8;

DROP TABLE IF EXISTS profile_job_subsector_enum;

CREATE TABLE IF NOT EXISTS profile_job_subsector_enum (
  id TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  sectorid TINYINT(2) UNSIGNED NOT NULL,
  flags SET('optgroup') DEFAULT '' NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id)
) CHARSET=utf8;

DROP TABLE IF EXISTS profile_job_subsubsector_enum;

CREATE TABLE IF NOT EXISTS profile_job_subsubsector_enum (
  id SMALLINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  sectorid TINYINT(2) UNSIGNED NOT NULL,
  subsectorid TINYINT(3) UNSIGNED NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  flags SET('display') NOT NULL DEFAULT 'display',
  PRIMARY KEY(id)
) CHARSET=utf8;

DROP TABLE IF EXISTS profile_job_alternates;

CREATE TABLE IF NOT EXISTS profile_job_alternates (
  id TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subsubsectorid SMALLINT(3) UNSIGNED NOT NULL,
  name VARCHAR(256) NOT NULL DEFAULT '',
  PRIMARY KEY(id, subsubsectorid),
  UNIQUE KEY(name)
) CHARSET=utf8;


ALTER TABLE profile_phones CHANGE COLUMN link_type link_type ENUM('address','pro','user', 'hq') NOT NULL DEFAULT 'user';

-- vim:set syntax=mysql:
