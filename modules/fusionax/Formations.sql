-- Import complet des formations

DROP TABLE IF EXISTS `fusionax_formations`;

CREATE TABLE IF NOT EXISTS `fusionax_formations` (
  FO CHAR(2) NOT NULL COMMENT 'Vaut toujours FO pour cette table',
  ax_id VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  Intitule_diplome VARCHAR(60) NOT NULL COMMENT 'Intitulé du diplôme',
  Intitule_formation VARCHAR(60) NOT NULL COMMENT 'Intitulé de la formation',
  Descr_formation VARCHAR(60) NOT NULL COMMENT 'Description de la formation',
  PRIMARY KEY (ax_id, Intitule_diplome, Intitule_formation)
) ENGINE=InnoDB, CHARSET=utf8;

LOAD DATA LOCAL INFILE  '{?}Formations.txt' INTO TABLE  fusionax_formations FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n'
(FO, ax_id, Intitule_diplome, Intitule_formation, Descr_formation);
