-- Import complet des entreprises

DROP TABLE IF EXISTS `fusionax_entreprises`;

CREATE TABLE IF NOT EXISTS `fusionax_entreprises` (
  `EN` CHAR(2) NOT NULL COMMENT 'Vaut toujours EN pour cette table',
  `Code_etab` BIGINT(10) NOT NULL COMMENT 'Code de l''établissement',
  `Raison_sociale` VARCHAR(255) NOT NULL COMMENT 'Raison sociale de l''établissement',
  `Sigle` VARCHAR(50) NOT NULL COMMENT 'Sigle de l''établissement',
  PRIMARY KEY(`Code_etab`),
  INDEX (Raison_sociale(20))
) ENGINE=InnoDB, CHARSET=utf8;

LOAD DATA LOCAL INFILE '{?}Entreprises.txt' INTO TABLE `fusionax_entreprises` CHARACTER SET utf8 FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(EN, Code_etab, Raison_sociale, Sigle, @Inconnu, @StringDate_maj);

UPDATE fusionax_entreprises SET Raison_sociale = TRIM(Raison_sociale), Sigle = TRIM(Sigle);
