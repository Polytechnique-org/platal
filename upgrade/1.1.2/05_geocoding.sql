DROP TABLE IF EXISTS profile_addresses_components;
DROP TABLE IF EXISTS profile_addresses_components_enum;

CREATE TABLE profile_addresses_components_enum (
  id BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  short_name VARCHAR(255) NOT NULL DEFAULT '',
  long_name VARCHAR(255) NOT NULL DEFAULT '',
  types SET('street_address', 'route', 'intersection', 'political', 'country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'colloquial_area', 'locality', 'sublocality', 'neighborhood', 'premise', 'subpremise', 'postal_code', 'natural_feature', 'airport', 'park', 'point_of_interest', 'post_box', 'street_number', 'floor', 'room') NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY (types, long_name),
  KEY (types, short_name)
) ENGINE=InnoDB, CHARSET=utf8;

CREATE TABLE profile_addresses_components (
  pid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  jobid INT(6) UNSIGNED NOT NULL DEFAULT 0,
  groupid SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  type ENUM('home', 'job', 'hq', 'group') NOT NULL DEFAULT 'home',
  id TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  component_id BIGINT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (pid, jobid, groupid, type, id, component_id),
  KEY(component_id),
  FOREIGN KEY (pid, jobid, groupid, type, id) REFERENCES profile_addresses (pid, jobid, groupid, type, id) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (component_id) REFERENCES profile_addresses_components_enum (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB, CHARSET=utf8;

ALTER TABLE profile_addresses DROP FOREIGN KEY profile_addresses_ibfk_1;
ALTER TABLE profile_addresses DROP FOREIGN KEY profile_addresses_ibfk_2;
ALTER TABLE profile_addresses DROP FOREIGN KEY profile_addresses_ibfk_3;
ALTER TABLE profile_addresses DROP FOREIGN KEY profile_addresses_ibfk_4;

ALTER TABLE profile_addresses DROP COLUMN accuracy;
ALTER TABLE profile_addresses DROP COLUMN postalCode;
ALTER TABLE profile_addresses DROP COLUMN localityId;
ALTER TABLE profile_addresses DROP COLUMN subAdministrativeAreaId;
ALTER TABLE profile_addresses DROP COLUMN administrativeAreaId;
ALTER TABLE profile_addresses DROP COLUMN countryId;
ALTER TABLE profile_addresses DROP COLUMN north;
ALTER TABLE profile_addresses DROP COLUMN south;
ALTER TABLE profile_addresses DROP COLUMN east;
ALTER TABLE profile_addresses DROP COLUMN west;

ALTER TABLE profile_addresses ADD COLUMN formatted_address TEXT NOT NULL DEFAULT '' AFTER postalText;
ALTER TABLE profile_addresses ADD COLUMN types SET('street_address', 'route', 'intersection', 'political', 'country', 'administrative_area_level_1', 'administrative_area_level_2', 'administrative_area_level_3', 'colloquial_area', 'locality', 'sublocality', 'neighborhood', 'premise', 'subpremise', 'postal_code', 'natural_feature', 'airport', 'park', 'point_of_interest', 'post_box', 'street_number', 'floor', 'room') NOT NULL DEFAULT '' AFTER formatted_address;
ALTER TABLE profile_addresses ADD COLUMN southwest_latitude FLOAT(10, 7) DEFAULT NULL AFTER longitude;
ALTER TABLE profile_addresses ADD COLUMN southwest_longitude FLOAT(10, 7) DEFAULT NULL AFTER southwest_latitude;
ALTER TABLE profile_addresses ADD COLUMN northeast_latitude FLOAT(10, 7) DEFAULT NULL AFTER southwest_longitude;
ALTER TABLE profile_addresses ADD COLUMN northeast_longitude FLOAT(10, 7) DEFAULT NULL AFTER northeast_latitude;
ALTER TABLE profile_addresses ADD COLUMN location_type ENUM('ROOFTOP', 'RANGE_INTERPOLATED', 'GEOMETRIC_CENTER', 'APPROXIMATE') DEFAULT NULL AFTER northeast_longitude;
ALTER TABLE profile_addresses ADD COLUMN partial_match BOOLEAN NOT NULL DEFAULT false AFTER location_type;
ALTER TABLE profile_addresses ADD COLUMN geocoding_date DATE DEFAULT NULL;
ALTER TABLE profile_addresses ADD COLUMN geocoding_calls TINYINT NOT NULL DEFAULT 0;

UPDATE  profile_addresses
   SET  latitude = NULL, longitude = NULL;

DROP TABLE IF EXISTS geoloc_administrativeareas;
DROP TABLE IF EXISTS geoloc_localities;
DROP TABLE IF EXISTS geoloc_subadministrativeareas;

-- vim:set syntax=mysql:
