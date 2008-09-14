-- Import complet des entreprises

DROP TABLE IF EXISTS `fusionax_entreprises`;

CREATE TABLE IF NOT EXISTS `fusionax_entreprises` (
  `EN` CHAR(2) NOT NULL COMMENT 'Vaut toujours EN pour cette table',
  `Code_etab` INT(10) NOT NULL COMMENT 'Code de l''établissement',
  `Raison_sociale` VARCHAR(255) collate utf8_general_ci NOT NULL COMMENT 'Raison sociale de l''établissement',
  `Sigle` VARCHAR(50) collate utf8_general_ci NOT NULL COMMENT 'Sigle de l''établissement',
  `Date_maj` DATE NOT NULL COMMENT 'Date de mise à jour de ces informations',
  PRIMARY KEY(`Code_etab`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

LOAD DATA LOCAL INFILE 'Entreprises.txt' INTO TABLE `fusionax_entreprises` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(EN, Code_etab, Raison_sociale, Sigle, @Inconnu, @StringDate_maj)
SET
`Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));
