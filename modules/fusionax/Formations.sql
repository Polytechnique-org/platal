-- Import complet des formations

DROP TABLE IF EXISTS `fusionax_formations`;

CREATE TABLE IF NOT EXISTS `fusionax_formations` (
  `FO` CHAR(2) NOT NULL COMMENT 'Vaut toujours FO pour cette table',
  `id_ancien` VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  `Intitule_formation` VARCHAR(60) collate utf8_general_ci NOT NULL COMMENT 'Intitulé de la formation',
  `Date_maj` DATE NOT NULL COMMENT 'Date de mise à jour de ces informations',
  PRIMARY KEY (`id_ancien`, `Intitule_formation`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

LOAD DATA LOCAL INFILE 'Formations.txt' INTO TABLE `fusionax_formations` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(FO, id_ancien, Intitule_formation, @StringDate_maj)
SET 
`Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));
