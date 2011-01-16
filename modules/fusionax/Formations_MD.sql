-- Import complet des formations

DROP TABLE IF EXISTS `fusionax_formations_md`;

CREATE TABLE IF NOT EXISTS `fusionax_formations_md` (
  FO CHAR(2) NOT NULL COMMENT 'Vaut toujours FO pour cette table',
  ax_id VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  field VARCHAR(255) DEFAULT NULL COMMENT 'Domaine de la formation',
  pid INT(11) UNSIGNED DEFAULT NULL,
  fieldid INT(2) DEFAULT NULL,
  PRIMARY KEY (ax_id),
  INDEX (pid),
  INDEX (fieldid)
) ENGINE=InnoDB, CHARSET=utf8;

LOAD DATA LOCAL INFILE  '{?}Formations.txt' INTO TABLE  fusionax_formations_md CHARACTER SET utf8 FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'
(FO, ax_id, field);

UPDATE fusionax_formations_md SET field = TRIM(field);
    UPDATE  fusionax_formations_md       AS f
INNER JOIN  profile_education_field_enum AS e ON (f.field = e.field)
       SET  f.fieldid = e.id, f.field = NULL;
