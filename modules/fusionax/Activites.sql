-- Import complet des activités professionnelles

DROP TABLE IF EXISTS `fusionax_activites`;

CREATE TABLE IF NOT EXISTS `fusionax_activites` (
  `AC` CHAR(2) NOT NULL COMMENT 'Vaut toujours AC pour cette table',
  `id_ancien` VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  `hash_activite` BINARY(5) NOT NULL COMMENT 'Hash de cette ligne pour la lier à l''adresse',
  `Code_etab` INT(10) NOT NULL COMMENT 'Code de l''établissement',
  `Raison_sociale` VARCHAR(255) collate utf8_general_ci NOT NULL COMMENT 'Raison sociale de l''établissement',
  `Libelle_fonctio` VARCHAR(255) collate utf8_general_ci NOT NULL COMMENT 'Libéllé de la fonction',
  `Annuaire` BOOLEAN NOT NULL COMMENT 'publiable dans l''annuaire papier',
  `Date_maj` DATE NOT NULL COMMENT 'Date de mise à jour de ces informations',
  PRIMARY KEY( `id_ancien` , `hash_activite` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

LOAD DATA LOCAL INFILE 'Activites.txt' INTO TABLE `fusionax_activites` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(AC, id_ancien, Code_etab, Raison_sociale, Libelle_fonctio, Annuaire, 
@Ligne1, @Ligne2, @Ligne3, @code_postal, @ville, @zip_cedex, @etat_distr, @pays, @tel, @fax, @StringDate_maj)
SET 
`hash_activite` = SUBSTRING( MD5( CONCAT(Code_etab, Libelle_fonctio) ), 1, 5),
`Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));
