DROP TABLE IF EXISTS tmp_profile_addresses;
CREATE TEMPORARY TABLE tmp_profile_addresses LIKE profile_addresses;
INSERT INTO tmp_profile_addresses SELECT * FROM profile_addresses;
DROP TABLE profile_addresses;
CREATE TABLE profile_addresses (
  pid int(11) unsigned NOT NULL DEFAULT '0',
  jobid int(6) unsigned NOT NULL DEFAULT '0',
  groupid SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  type enum('home','job','hq','group') NOT NULL DEFAULT 'home',
  id tinyint(3) unsigned NOT NULL DEFAULT '0',
  flags set('current','temporary','secondary','mail','cedex','deliveryIssue') DEFAULT NULL,
  accuracy tinyint(1) unsigned NOT NULL DEFAULT '0',
  text text NOT NULL,
  postalText text NOT NULL,
  postalCode varchar(255) DEFAULT NULL,
  localityId bigint(20) unsigned DEFAULT NULL,
  subAdministrativeAreaId int(11) unsigned DEFAULT NULL,
  administrativeAreaId int(11) unsigned DEFAULT NULL,
  countryId char(2) DEFAULT NULL,
  latitude float(10,7) DEFAULT NULL,
  longitude float(10,7) DEFAULT NULL,
  north float(10,7) DEFAULT NULL,
  south float(10,7) DEFAULT NULL,
  east float(10,7) DEFAULT NULL,
  west float(10,7) DEFAULT NULL,
  pub enum('public','ax','private') NOT NULL DEFAULT 'private',
  comment varchar(255) DEFAULT NULL,
  PRIMARY KEY (pid,jobid,groupid,type,id),
  KEY pid (pid),
  KEY jobid (jobid),
  KEY type (type),
  KEY adrid (id),
  KEY localityId (localityId),
  KEY administrativeAreaId (administrativeAreaId),
  KEY subAdministrativeAreaId (subAdministrativeAreaId),
  KEY countryId (countryId),
  CONSTRAINT profile_addresses_ibfk_1 FOREIGN KEY (localityId) REFERENCES geoloc_localities (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT profile_addresses_ibfk_2 FOREIGN KEY (subAdministrativeAreaId) REFERENCES geoloc_subadministrativeareas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT profile_addresses_ibfk_3 FOREIGN KEY (administrativeAreaId) REFERENCES geoloc_administrativeareas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT profile_addresses_ibfk_4 FOREIGN KEY (countryId) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO  profile_addresses (pid, jobid, groupid, type, id, flags, accuracy, text, postalText,
             postalCode, localityId, subAdministrativeAreaId, administrativeAreaId, countryId,
             latitude, longitude, north, south, east, west, pub, comment)
     SELECT  pid, jobid, 0, type, id, flags, accuracy, text, postalText,
             postalCode, localityId, subAdministrativeAreaId, administrativeAreaId, countryId,
             latitude, longitude, north, south, east, west, pub, comment
       FROM  tmp_profile_addresses;
DROP TABLE IF EXISTS tmp_profile_addresses;

-- vim:set syntax=mysql:
