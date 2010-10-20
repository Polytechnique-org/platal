-- Import complet des activités professionnelles

DROP TABLE IF EXISTS `fusionax_activites`;

CREATE TABLE IF NOT EXISTS `fusionax_activites` (
  `AC` CHAR(2) NOT NULL COMMENT 'Vaut toujours AC pour cette table',
  `ax_id` VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  `Code_etab` BIGINT(10) NOT NULL COMMENT 'Code de l''établissement',
  `Raison_sociale` VARCHAR(255) NOT NULL COMMENT 'Raison sociale de l''établissement',
  `Libelle_fonctio` VARCHAR(255) NOT NULL COMMENT 'Libéllé de la fonction',
  `Annuaire` BOOLEAN NOT NULL COMMENT 'publiable dans l''annuaire papier',
  PRIMARY KEY( `ax_id` , `Code_etab` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOAD DATA LOCAL INFILE '{?}Activites.txt' INTO TABLE `fusionax_activites` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(AC, ax_id, Code_etab, Raison_sociale, Libelle_fonctio, Annuaire,
@Ligne1, @Ligne2, @Ligne3, @code_postal, @ville, @zip_cedex, @etat_distr, @pays, @tel, @fax, @StringDate_maj);


UPDATE fusionax_activites SET Raison_sociale = TRIM(Raison_sociale), Libelle_fonctio = TRIM(Libelle_fonctio);
