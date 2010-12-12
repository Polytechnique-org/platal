-- Import complet des formations

DROP TABLE IF EXISTS `fusionax_formations`;

CREATE TABLE IF NOT EXISTS `fusionax_formations` (
  FO CHAR(2) NOT NULL COMMENT 'Vaut toujours FO pour cette table',
  ax_id VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  Intitule_formation VARCHAR(255) NOT NULL DEFAULT 0 COMMENT 'Intitulé de la formation',
  Intitule_diplome VARCHAR(255) NOT NULL DEFAULT 0 COMMENT 'Intitulé du diplôme',
  Descr_formation VARCHAR(255) NOT NULL COMMENT 'Description de la formation',
  pid INT(11) UNSIGNED DEFAULT NULL,
  eduid INT(4) DEFAULT NULL,
  degreeid INT(4) DEFAULT NULL,
  fieldid INT(2) DEFAULT NULL,
  PRIMARY KEY (ax_id, Intitule_diplome, Intitule_formation, Descr_formation),
  INDEX (Intitule_diplome(60)),
  INDEX (Intitule_formation(60)),
  INDEX (Descr_formation(60)),
  INDEX (pid),
  INDEX (eduid),
  INDEX (degreeid),
  INDEX (fieldid)
) ENGINE=InnoDB, CHARSET=utf8;

LOAD DATA LOCAL INFILE  '{?}Formations.txt' INTO TABLE  fusionax_formations CHARACTER SET utf8 FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'
(FO, ax_id, Intitule_formation, Intitule_diplome, Descr_formation);

    UPDATE  fusionax_formations    AS f
INNER JOIN  profile_education_enum AS e ON (f.Intitule_formation = e.abbreviation)
       SET  f.Intitule_formation = e.name
     WHERE  f.Intitule_formation != '';
