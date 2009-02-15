CREATE TABLE IF NOT EXISTS profile_addresses (
  pid INT(11) DEFAULT NULL,
  jobid INT(6) UNSIGNED DEFAULT NULL,
  type ENUM('home','job','hq') NOT NULL DEFAULT 'home',
  id TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  flags SET('current','temporary','secondary','mail','cedex') DEFAULT NULL,
  accuracy TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  text TEXT NOT NULL,
  postalText TEXT NOT NULL,
  postalCode VARCHAR(255) DEFAULT NULL,
  localityId INT(11) DEFAULT NULL,
  subAdministrativeAreaId INT(11) DEFAULT NULL,
  administrativeAreaId INT(11) DEFAULT NULL,
  countryId CHAR(2) DEFAULT NULL,
  latitude FLOAT(10,7) DEFAULT NULL,
  longitude FLOAT(10,7) DEFAULT NULL,
  north FLOAT(10,7) DEFAULT NULL,
  south FLOAT(10,7) DEFAULT NULL,
  east FLOAT(10,7) DEFAULT NULL,
  west FLOAT(10,7) DEFAULT NULL,
  updateTime DATE NOT NULL DEFAULT 0,
  pub ENUM('public','ax','private') NOT NULL DEFAULT 'private',
  comment VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY(pid, jobid, type, id),
  INDEX pid (pid),
  INDEX jobid (jobid),
  INDEX type (type),
  INDEX adrid (id),
  INDEX localityId (localityId),
  INDEX administrativeAreaId (administrativeAreaId),
  INDEX subAdministrativeAreaId (subAdministrativeAreaId),
  INDEX countryId (countryId)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS geoloc_countries (
  iso_3166_1 CHAR(2) NOT NULL,
  iso_3166_2 CHAR(3) NOT NULL,
  iso_3166_3 SMALLINT(3) UNSIGNED NOT NULL,
  worldRegion CHAR(2) DEFAULT NULL,
  countryFR VARCHAR(255) NOT NULL,
  country VARCHAR(255) NOT NULL,
  capital VARCHAR(255) NOT NULL,
  nationalityFR VARCHAR(255) DEFAULT NULL,
  nationality VARCHAR(255) DEFAULT NULL,
  phonePrefix SMALLINT(5) UNSIGNED DEFAULT NULL,
  phoneFormat VARCHAR(255) NOT NULL,
  licensePlate CHAR(4) DEFAULT NULL,
  belongsTo CHAR(2) DEFAULT NULL,
  PRIMARY KEY(iso_3166_1),
  INDEX(iso_3166_1),
  INDEX(phonePrefix)
) CHARSET=utf8;

INSERT INTO  geoloc_countries (iso_3166_1, iso_3166_2, iso_3166_3, worldRegion,
                               countryFR, country, capital, nationalityFR,
                               phonePrefix, phoneFormat, licensePlate)
     SELECT  a2, a3, n3, worldrgn, pays, country, capital, nat, phoneprf, phoneformat, license_plate
       FROM  geoloc_pays;

CREATE TABLE IF NOT EXISTS geoloc_administrativeareas (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  country CHAR(2) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(id, name, country),
  INDEX(id),
  INDEX(name),
  INDEX(country)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS geoloc_subadministrativeareas (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  country CHAR(2) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(id, name, country),
  INDEX(id),
  INDEX(name),
  INDEX(country)
) CHARSET=utf8;

CREATE TABLE IF NOT EXISTS geoloc_localities (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  country CHAR(2) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(id, name, country),
  INDEX(id),
  INDEX(name),
  INDEX(country)
) CHARSET=utf8;

-- vim:set syntax=mysql:
