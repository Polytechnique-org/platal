-- Modify tables that are refered to.
ALTER TABLE profile_education_enum MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE profile_education_degree_enum MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE profile_education_field_enum MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE profile_section_enum MODIFY COLUMN id TINYINT(2) UNSIGNED NOT NULL DEFAULT 0;

-- Following tables all needs a sligth change in their definition to match their foreign correspondance.
ALTER TABLE profile_addresses MODIFY COLUMN localityId BIGINT(20) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_addresses MODIFY COLUMN subAdministrativeAreaId INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_addresses MODIFY COLUMN administrativeAreaId INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_binets MODIFY COLUMN binet_id TINYINT(3) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_education MODIFY COLUMN eduid INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_education MODIFY COLUMN degreeid INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_education MODIFY COLUMN fieldid INT(11) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_education_enum MODIFY COLUMN country CHAR(2) DEFAULT 'FR';
ALTER TABLE profile_education_degree MODIFY COLUMN eduid INT(11) UNSIGNED DEFAULT 0;
ALTER TABLE profile_education_degree MODIFY COLUMN degreeid INT(11) UNSIGNED DEFAULT 0;
ALTER TABLE profile_job MODIFY COLUMN jobid INT(6) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_job MODIFY COLUMN sectorid TINYINT(2) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_job MODIFY COLUMN subsectorid SMALLINT(3) UNSIGNED DEFAULT NULL;
ALTER TABLE profile_job MODIFY COLUMN subsubsectorid SMALLINT(3) UNSIGNED DEFAULT NULL;

-- Rename columns that should be called uid.
-- ALTER TABLE group_events CHANGE COLUMN organisateur_uid uid INT(11) UNSIGNED DEFAULT NULL;

-- Prepares table having ids set to 0.
DELETE FROM profile_mentor_country WHERE country = 'YU' OR country = '00';
UPDATE profile_addresses SET localityId = NULL WHERE localityId = 0;
UPDATE profile_addresses SET subAdministrativeAreaId = NULL WHERE subAdministrativeAreaId = 0;
UPDATE profile_addresses SET administrativeAreaId = NULL WHERE administrativeAreaId = 0;
UPDATE profile_addresses SET countryId = NULL WHERE countryId = '';
UPDATE profile_education SET eduid = NULL WHERE eduid = 0;
UPDATE profile_education SET degreeid = NULL WHERE degreeid = 0;
UPDATE profile_education SET fieldid = NULL WHERE fieldid = 0;
UPDATE profile_education_enum SET country = NULL WHERE country = '';
UPDATE profile_job SET jobid = NULL WHERE jobid = 0;
UPDATE profile_job SET sectorid = NULL WHERE sectorid = 0;
UPDATE profile_job SET subsectorid = NULL WHERE subsectorid = 0;
UPDATE profile_job SET subsubsectorid = NULL WHERE subsubsectorid = 0;
UPDATE profile_job_enum SET holdingid = NULL WHERE holdingid = 0;

-- Adds missing data in foreign tables.
INSERT INTO  geoloc_countries (iso_3166_1_a2, iso_3166_1_a3, iso_3166_1_num, worldRegion, country,
                               countryFR, capital, nationalityFR, licensePlate, belongsTo, phonePrefix)
     VALUES  ('GF', 'GUF', 254, 'EU', 'French Guiana', 'Guyane française', 'Cayenne', NULL, 'FR', 'FR', 594),
             ('GP', 'GLP', 312, 'EU', 'Guadeloupe', 'Guadeloupe', 'Basse-Terre', NULL, 'FR', 'FR', 590),
             ('MQ', 'MTQ', 474, 'EU', 'Martinique', 'Martinique', 'Fort-de-France', NULL, 'FR', 'FR', 596),
             ('NC', 'NCL', 540, 'EU', 'New Caledonia', 'Nouvelle-Calédonie', 'Nouméa', NULL, 'FR', 'FR', 687),
             ('PF', 'PYF', 258, 'EU', 'French Polynesia', 'Polynésie française', 'Papeete', NULL, 'FR', 'FR', 689),
             ('PR', 'PRI', 630, 'NA', 'Puerto Rico', 'Porto Rico', 'San Juan', NULL, 'USA', 'US', 1787),
             ('MP', 'MNP', 580, 'NA', 'Northern Mariana Islands', 'Îles Mariannes du Nord', ' Saipan', NULL, 'USA', 'US', 1670);

-- Following tables all refer to profiles.pid.
ALTER TABLE profile_addresses ADD FOREIGN KEY (localityId) REFERENCES geoloc_localities (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_addresses ADD FOREIGN KEY (subAdministrativeAreaId) REFERENCES geoloc_subadministrativeareas (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_addresses ADD FOREIGN KEY (administrativeAreaId) REFERENCES geoloc_administrativeareas (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_addresses ADD FOREIGN KEY (countryId) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_binets ADD FOREIGN KEY (binet_id) REFERENCES profile_binet_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_corps ADD FOREIGN KEY (original_corpsid) REFERENCES profile_corps_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_corps ADD FOREIGN KEY (current_corpsid) REFERENCES profile_corps_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_corps ADD FOREIGN KEY (rankid) REFERENCES profile_corps_rank_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_education ADD FOREIGN KEY (eduid) REFERENCES profile_education_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_education ADD FOREIGN KEY (degreeid) REFERENCES profile_education_degree_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_education ADD FOREIGN KEY (fieldid) REFERENCES profile_education_field_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_education_enum ADD FOREIGN KEY (country) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_education_degree ADD FOREIGN KEY (eduid) REFERENCES profile_education_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_education_degree ADD FOREIGN KEY (degreeid) REFERENCES profile_education_degree_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job ADD FOREIGN KEY (jobid) REFERENCES profile_job_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job ADD FOREIGN KEY (sectorid) REFERENCES profile_job_sector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job ADD FOREIGN KEY (subsectorid) REFERENCES profile_job_subsector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job ADD FOREIGN KEY (subsubsectorid) REFERENCES profile_job_subsubsector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job_alternates ADD FOREIGN KEY (subsubsectorid) REFERENCES profile_job_subsubsector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job_enum ADD FOREIGN KEY (holdingid) REFERENCES profile_job_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job_subsector_enum ADD FOREIGN KEY (sectorid) REFERENCES profile_job_sector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job_subsubsector_enum ADD FOREIGN KEY (sectorid) REFERENCES profile_job_sector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_job_subsubsector_enum ADD FOREIGN KEY (subsectorid) REFERENCES profile_job_subsector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_langskills ADD FOREIGN KEY (lid) REFERENCES profile_langskill_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_medal_grade_enum ADD FOREIGN KEY (mid) REFERENCES profile_medal_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_mentor_country ADD FOREIGN KEY (country) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_mentor_sector ADD FOREIGN KEY (sectorid) REFERENCES profile_job_sector_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_skills ADD FOREIGN KEY (cid) REFERENCES profile_skill_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_name ADD FOREIGN KEY (typeid) REFERENCES profile_name_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profile_networking ADD FOREIGN KEY (nwid) REFERENCES profile_networking_enum (nwid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profiles ADD FOREIGN KEY (section) REFERENCES profile_section_enum (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profiles ADD FOREIGN KEY (nationality1) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profiles ADD FOREIGN KEY (nationality2) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE profiles ADD FOREIGN KEY (nationality3) REFERENCES geoloc_countries (iso_3166_1_a2) ON DELETE CASCADE ON UPDATE CASCADE;

-- vim:set syntax=mysql:
