DROP TABLE IF EXISTS profile_addresses;

CREATE TABLE profile_addresses (
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
  updateTime DATETIME NOT NULL DEFAULT 0,
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
) ENGINE=InnoDB, CHARSET=utf8;

INSERT INTO  profile_addresses (pid, id, postalCode, updateTime, pub, comment, latitude, longitude, countryId,
                                type, flags)
     SELECT  uid, adrid, postcode, datemaj, pub, NULL, glat, glng, country,
             IF(FIND_IN_SET('pro', 'statut'), 'job', 'home'),
             CONCAT(IF(FIND_IN_SET('res-secondaire', 'statut'), 'secondary,', ''),
                    IF(FIND_IN_SET('courrier', 'statut'), 'mail,', ''),
                    IF(FIND_IN_SET('active', 'statut'), 'current,', ''),
                    IF(FIND_IN_SET('temporaire', 'statut'), 'temporary', ''))
       FROM  #x4dat#.adresses;

DROP TABLE IF EXISTS geoloc_countries;
CREATE TABLE geoloc_countries (
  iso_3166_1_a2 CHAR(2) NOT NULL,
  iso_3166_1_a3 CHAR(3) NOT NULL,
  iso_3166_1_num SMALLINT(3) UNSIGNED NOT NULL,
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
  PRIMARY KEY(iso_3166_1_a2),
  UNIQUE KEY(iso_3166_1_a3),
  UNIQUE KEY(iso_3166_1_num),
  INDEX(iso_3166_1_a2),
  INDEX(phonePrefix)
) ENGINE=InnoDB, CHARSET=utf8;

UPDATE  geoloc_pays
   SET  n3 = 450
 WHERE  a2 = "MG";

UPDATE  geoloc_pays
   SET  n3 = 807
 WHERE  a2 = "MK";

UPDATE  geoloc_pays
  SET   n3 = 232
 WHERE  a2 = "ER";

INSERT INTO  geoloc_countries (iso_3166_1_a2, iso_3166_1_a3, iso_3166_1_num, worldRegion,
                               countryFR, country, capital, nationalityFR,
                               phonePrefix, phoneFormat, licensePlate)
     SELECT  a2, a3, n3, worldrgn, pays, country, capital, nat, phoneprf, phoneformat, license_plate
       FROM  geoloc_pays;

DROP TABLE IF EXISTS geoloc_administrativeareas;
CREATE TABLE geoloc_administrativeareas (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  country CHAR(2) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(id, name, country),
  INDEX(id),
  INDEX(name),
  INDEX(country)
) ENGINE=InnoDB, CHARSET=utf8;

DROP TABLE IF EXISTS geoloc_subadministrativeareas;
CREATE TABLE IF NOT EXISTS geoloc_subadministrativeareas (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  country CHAR(2) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(id, name, country),
  INDEX(id),
  INDEX(name),
  INDEX(country)
) ENGINE=InnoDB, CHARSET=utf8;

DROP TABLE IF EXISTS geoloc_localities;
CREATE TABLE IF NOT EXISTS geoloc_localities (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  country CHAR(2) NOT NULL,
  PRIMARY KEY(id),
  UNIQUE KEY(id, name, country),
  INDEX(id),
  INDEX(name),
  INDEX(country)
) ENGINE=InnoDB, CHARSET=utf8;

DROP TABLE geoloc_pays;
-- vim:set syntax=mysql:
