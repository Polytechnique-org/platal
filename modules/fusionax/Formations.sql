-- Import complet des formations

DROP TABLE IF EXISTS `fusionax_formations`;

CREATE TABLE IF NOT EXISTS `fusionax_formations` (
  FO CHAR(2) NOT NULL COMMENT 'Vaut toujours FO pour cette table',
  ax_id VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  Intitule_diplome CHAR(60) NOT NULL DEFAULT 0 COMMENT 'Intitulé du diplôme',
  Intitule_formation CHAR(60) NOT NULL DEFAULT 0 COMMENT 'Intitulé de la formation',
  Descr_formation CHAR(60) NOT NULL COMMENT 'Description de la formation',
  pid INT(11) UNSIGNED DEFAULT NULL,
  eduid INT(4) DEFAULT NULL,
  degreeid INT(4) DEFAULT NULL,
  fieldid INT(2) DEFAULT NULL,
  PRIMARY KEY (ax_id, Intitule_diplome, Intitule_formation, Descr_formation),
  INDEX (Intitule_diplome),
  INDEX (Intitule_formation),
  INDEX (Descr_formation),
  INDEX (pid),
  INDEX (eduid),
  INDEX (degreeid),
  INDEX (fieldid)
) ENGINE=InnoDB, CHARSET=utf8;

LOAD DATA LOCAL INFILE  '{?}Formations.txt' INTO TABLE  fusionax_formations FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'
(FO, ax_id, Intitule_diplome, Intitule_formation, Descr_formation);
