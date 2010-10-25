-- Import complet des adresses

DROP TABLE IF EXISTS fusionax_adresses;

CREATE TABLE IF NOT EXISTS fusionax_adresses (
  provenance CHAR(2) NOT NULL COMMENT 'Vaut AC, AD ou AN selon la provenance de l''info',
  ax_id VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  Type_adr ENUM('E', 'P') character set binary NOT NULL DEFAULT 'P' COMMENT 'Type d''adresse : E pour Entreprise, P pour Personnelle',
  Ligne1 VARCHAR(90) NOT NULL,
  Ligne2 VARCHAR(90) NOT NULL,
  Ligne3 VARCHAR(90) NOT NULL,
  code_postal VARCHAR(20) NOT NULL,
  ville VARCHAR(80) NOT NULL,
  zip_cedex VARCHAR(20) NOT NULL,
  etat_distr VARCHAR(20) NOT NULL,
  pays VARCHAR(50) NOT NULL,
  tel VARCHAR(30) NOT NULL,
  fax VARCHAR(30) NOT NULL,
  Code_etab BIGINT(10) DEFAULT NULL,
  text TEXT DEFAULT NULL,
  PRIMARY KEY (ax_id, Type_adr)
) ENGINE=InnoDB, CHARSET=utf8;

LOAD DATA LOCAL INFILE '{?}Adresses.txt' INTO TABLE `fusionax_adresses` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(provenance, ax_id, @Type_adr, Ligne1, Ligne2, Ligne3, code_postal, ville, zip_cedex, etat_distr, pays, tel, fax, @StringDate_maj)
SET
`Type_adr` = IF(@Type_adr = 'E', 'E', IF(@Type_adr = '', '', 'P'));

LOAD DATA LOCAL INFILE '{?}Anciens.txt' INTO TABLE `fusionax_adresses` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(provenance, ax_id, @login, @password, @promotion_etude, @gpe_promo, @Nom_patronymique, @partic_patro, @prenom, @Nom_usuel, @partic_nom,
  @Nom_complet, @civilite, @Code_nationalite, @type, @corps_sortie, @StringDate_deces, @grade, @Mel_usage, @Mel_publiable, @xxx, @xxx,
  @tel_mobile, @xxx, @xxx, @xxx, @xxx, @xxx, @xxx, @xxx, @X_M_D, @xxx, @xxx, @xxx, @xxx, @xxx, @xxx, @Type_adr,
  Ligne1, Ligne2, Ligne3, code_postal, ville, zip_cedex, etat_distr, pays, tel, fax, @date_MAJ)
SET
Type_adr = IF(@Type_adr = 'E', 'E', IF(@Type_adr = '', '', 'P'));

LOAD DATA LOCAL INFILE '{?}Activites.txt' INTO TABLE `fusionax_adresses` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(provenance, ax_id, Code_etab, @Raison_sociale, @Libelle_fonctio, @Annuaire,
Ligne1, Ligne2, Ligne3, code_postal, ville, zip_cedex, etat_distr, pays, tel, fax, @StringDate_maj)
SET
`Type_adr` = 'E';

UPDATE fusionax_adresses SET Ligne1 = TRIM(Ligne1), Ligne2 = TRIM(Ligne2), Ligne3 = TRIM(Ligne3), pays = TRIM(pays),
                             code_postal = TRIM(code_postal), ville = TRIM(ville), zip_cedex = TRIM(zip_cedex),
                             tel = TRIM(tel), fax = TRIM(fax);
UPDATE fusionax_adresses SET ville = '' WHERE ville = '.';
DELETE FROM fusionax_adresses WHERE ville = '' AND tel = '' AND fax = '';
UPDATE fusionax_adresses SET text = CONCAT(IF(Ligne1 != '', CONCAT(Ligne1, '\n'), ''),
                                           IF(Ligne2 != '', CONCAT(Ligne2, '\n'), ''),
                                           IF(Ligne3 != '', CONCAT(Ligne3, '\n'), ''),
                                           IF(code_postal != '', code_postal, zip_cedex), ' ', ville,
                                           IF(pays != '', CONCAT('\n', pays), ''))
                         WHERE ville != '';
ALTER TABLE fusionax_adresses ADD INDEX (text(20));
