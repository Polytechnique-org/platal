-- Import complet des adresses

DROP TABLE IF EXISTS `fusionax_adresses`;

CREATE TABLE IF NOT EXISTS `fusionax_adresses` (
  `provenance` varbinary(2) NOT NULL COMMENT 'Vaut AC, AD ou AN selon la provenance de l''info',
  `id_ancien` varbinary(8) NOT NULL COMMENT 'Id unique de l''ancien',
  `Type_adr` enum('E', 'P') character set binary NOT NULL DEFAULT 'P' COMMENT 'Type d''adresse : E pour Entreprise, P pour Personnelle',
  `hash_adresse` binary(5) NOT NULL COMMENT 'Hash pour différencier les diverses adresses',
  `Ligne1` varchar(60) character set utf8 NOT NULL,
  `Ligne2` varchar(60) character set utf8 NOT NULL,
  `Ligne3` varchar(60) character set utf8 NOT NULL,
  `code_postal` varchar(20) character set utf8 NOT NULL,
  `ville` varchar(40) character set utf8 NOT NULL,
  `zip_cedex` varchar(20) character set utf8 NOT NULL,
  `etat_distr` varchar(20) character set utf8 NOT NULL,
  `pays` varchar(50) character set utf8 NOT NULL,
  `tel` varbinary(30) NOT NULL,
  `fax` varbinary(30) NOT NULL,
  `Date_maj` date NOT NULL,
  PRIMARY KEY  (`id_ancien`, `hash_adresse`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOAD DATA LOCAL INFILE 'Adresses.txt' INTO TABLE `fusionax_adresses` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(provenance, id_ancien, @Type_adr, Ligne1, Ligne2, Ligne3, code_postal, ville, zip_cedex, etat_distr, pays, tel, fax, @StringDate_maj)
SET 
`Type_adr` = IF(@Type_adr = 'E', 'E', IF(@Type_adr = '', '', 'P')),
`hash_adresse` = SUBSTRING( MD5( @Type_adr ), 1, 5),
`Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));

LOAD DATA LOCAL INFILE 'Anciens.txt' INTO TABLE `fusionax_adresses` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(provenance, id_ancien, @Login, @Password, @promotion_etude, @Groupe_promo, @Nom_patronymique, @partic_patro, @prenom, @Nom_usuel, @partic_nom,
    @Nom_complet, @Civilite, @Code_nationalite, @Type_membre, @corps_sortie, @StringDate_deces, @grade, @Mel_usage, @Mel_publiable,
    @tel_mobile, @annee_dernCot, @Representant, @Type_adr, Ligne1, Ligne2, Ligne3, code_postal, ville,
    zip_cedex, etat_distr, pays, tel, fax, @StringDate_maj)
SET 
`Type_adr` = IF(@Type_adr = 'E', 'E', IF(@Type_adr = '', '', 'P')),
`hash_adresse` = SUBSTRING( MD5( @Type_adr ), 1, 5),
`Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));

LOAD DATA LOCAL INFILE 'Activites.txt' INTO TABLE `fusionax_adresses` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(provenance, id_ancien, @Code_etab, @Raison_sociale, @Libelle_fonctio, @Annuaire, 
Ligne1, Ligne2, Ligne3, code_postal, ville, zip_cedex, etat_distr, pays, tel, fax, @StringDate_maj)
SET 
`Type_adr` = 'E',
`hash_adresse` = SUBSTRING( MD5( CONCAT(@Code_etab, @Libelle_fonctio) ), 1, 5),
`Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));
