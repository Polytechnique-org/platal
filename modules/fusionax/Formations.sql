-- Import complet des formations

DROP TABLE IF EXISTS `fusionax_formations`;

CREATE TABLE IF NOT EXISTS `fusionax_formations` (
  FO CHAR(2) NOT NULL COMMENT 'Vaut toujours FO pour cette table',
  id_ancien VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  Intitule_diplome VARCHAR(60) collate utf8_general_ci NOT NULL COMMENT 'Intitulé du diplôme',
  Intitule_formation VARCHAR(60) collate utf8_general_ci NOT NULL COMMENT 'Intitulé de la formation',
  Descr_formation VARCHAR(60) collate utf8_general_ci NOT NULL COMMENT 'Description de la formation',
  tmp_1 VARCHAR(60) collate utf8_general_ci NOT NULL,
  tmp_2 VARCHAR(60) collate utf8_general_ci NOT NULL,
  tmp_3 VARCHAR(60) collate utf8_general_ci NOT NULL,
  tmp_4 VARCHAR(60) collate utf8_general_ci NOT NULL,
  PRIMARY KEY (id_ancien, Intitule_diplome, Intitule_formation)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

                              LOAD DATA LOCAL INFILE  'Formations.txt'
                                          INTO TABLE  fusionax_formations
FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'  (FO, id_ancien, Intitule_diplome, Intitule_formation, Descr_formation, tmp_1, tmp_2, tmp_3, tmp_4);
