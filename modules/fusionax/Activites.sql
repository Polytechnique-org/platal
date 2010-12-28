-- Import complet des activités professionnelles

DROP TABLE IF EXISTS `fusionax_activites`;

CREATE TABLE IF NOT EXISTS `fusionax_activites` (
  `AC` CHAR(2) NOT NULL COMMENT 'Vaut toujours AC pour cette table',
  `ax_id` VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  `Code_etab` BIGINT(10) NOT NULL COMMENT 'Code de l''établissement',
  `Raison_sociale` VARCHAR(255) NOT NULL COMMENT 'Raison sociale de l''établissement',
  `Libelle_fonctio` VARCHAR(255) NOT NULL COMMENT 'Libéllé de la fonction',
  `Annuaire` BOOLEAN NOT NULL COMMENT 'publiable dans l''annuaire papier',
  `Date_maj` DATE NOT NULL COMMENT 'Date de mise à jour de ces informations',
  pid INT(11) UNSIGNED DEFAULT NULL,
  jobid INT(6) UNSIGNED DEFAULT NULL,
  description VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`ax_id`, `Code_etab`),
  INDEX (Code_etab),
  INDEX (pid),
  INDEX (jobid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOAD DATA LOCAL INFILE '{?}Activites.txt' INTO TABLE `fusionax_activites` CHARACTER SET utf8 FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(AC, ax_id, Code_etab, Raison_sociale, Libelle_fonctio, Annuaire,
@Ligne1, @Ligne2, @Ligne3, @code_postal, @ville, @zip_cedex, @etat_distr, @pays, @tel, @fax, @StringDate_maj)
SET
`Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));


UPDATE fusionax_activites SET Raison_sociale = TRIM(Raison_sociale), Libelle_fonctio = TRIM(Libelle_fonctio);
UPDATE  fusionax_activites
   SET  description = IF(Raison_sociale,
                         IF(Libelle_fonctio, CONCAT(Raison_sociale, ', ', Libelle_fonctio), Raison_sociale),
                         Libelle_fonctio);
