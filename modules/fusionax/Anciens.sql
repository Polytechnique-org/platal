-- Import complet des anciens

DROP TABLE IF EXISTS fusionax_anciens;

CREATE TABLE IF NOT EXISTS fusionax_anciens (
  AN CHAR(2) NOT NULL COMMENT 'Vaut toujours AN pour cette table',
  ax_id VARCHAR(8) NOT NULL COMMENT 'Id unique de l''ancien',
  promotion_etude SMALLINT(4) NOT NULL COMMENT 'Promotion avec laquelle il/elle a fait ses études',
  groupe_promo CHAR(1) NOT NULL COMMENT '0, M ou D',
  Nom_patronymique VARCHAR(255) NOT NULL COMMENT 'Nom patronymique (nom de jeune fille) sans la particule',
  partic_patro VARCHAR(5) NOT NULL COMMENT 'Particule du nom patronymique',
  prenom VARCHAR(30) NOT NULL COMMENT 'Prénom',
  Nom_usuel VARCHAR(255) NOT NULL COMMENT 'Nom usuel (nom marital par exemple) sans la particule',
  partic_nom VARCHAR(5) NOT NULL COMMENT 'Particule du nom usuel',
  Nom_complet VARCHAR(255) NOT NULL COMMENT 'Nom patronymique complet (avec la particule)',
  Civilité ENUM('M', 'MLLE', 'MME') NOT NULL DEFAULT 'M',
  Code_nationalite CHAR(4) NOT NULL COMMENT 'Nationalité (code)',
  corps_sortie VARCHAR(50) NOT NULL COMMENT 'Corps de sortie (ou D si aucun)',
  Date_deces DATE COMMENT 'Date de décès',
  grade VARCHAR(50) NOT NULL COMMENT 'Grade actuel dans son corps',
  Mel_usage VARCHAR(255) NOT NULL COMMENT 'Adresse e-mail d''usage',
  Mel_publiable TINYINT(4) NOT NULL COMMENT 'Autorisation d''utiliser le mail',
  Mob_publiable TINYINT(4) NOT NULL COMMENT 'Autorisation d''utiliser le mobile',
  tel_mobile VARCHAR(30) NOT NULL COMMENT 'Numéro de téléphone mobile',
  Date_maj DATE NOT NULL COMMENT 'Date de mise à jour de ces informations',
  pid INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY  (ax_id),
  INDEX (pid)
) ENGINE=InnoDB, CHARSET=utf8;

LOAD DATA LOCAL INFILE '{?}Anciens.txt' INTO TABLE `fusionax_anciens` CHARACTER SET utf8 FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(AN, ax_id, @login, @password, promotion_etude, groupe_promo, Nom_patronymique, partic_patro, prenom, Nom_usuel, partic_nom,
  Nom_complet, Civilite, Code_nationalite, @type, corps_sortie, @StringDate_deces, grade, Mel_usage, Mel_publiable, @xxx, Mob_publiable,
  tel_mobile, @xxx, @xxx, @xxx, @xxx, @xxx, @xxx, @xxx, @X_M_D, @xxx, @xxx, @xxx, @xxx, @xxx, @xxx, @Type_adr,
  @Ligne1, @Ligne2, @Ligne3, @code_postal, @ville, @zip_cedex, @etat_distr, @pays, @tel, @fax, @StringDate_maj)
SET
    Date_deces = CONCAT(SUBSTRING(@StringDate_deces,7),'-',SUBSTRING(@StringDate_deces,4,2),'-',SUBSTRING(@StringDate_deces,1,2)),
    Date_maj = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2));
-- Mel_publiable is not certain yet :/

ALTER TABLE fusionax_anciens ADD INDEX (ax_id);
UPDATE fusionax_anciens SET corps_sortie = TRIM(corps_sortie), grade = TRIM(grade);
UPDATE fusionax_anciens SET groupe_promo = 'X' WHERE groupe_promo NOT IN ('M', 'D');
UPDATE fusionax_anciens SET promotion_etude = SUBSTRING(ax_id, 1, 4) WHERE groupe_promo != 'X';

-- Correspondances entre fiches X.org et fiches AX
DROP TABLE IF EXISTS `fusionax_import`;

CREATE TABLE IF NOT EXISTS `fusionax_import` (
  `ax_id` VARCHAR(8) NOT NULL COMMENT 'identifiant AX de l''ancien',
  `pid` INT(11) DEFAULT NULL COMMENT 'identifiant du profil x.org de l''ancien si on l''a trouvé',
  `date_match_id` TIMESTAMP NULL DEFAULT NULL COMMENT 'date de mise en correspondance des identifiants',
  PRIMARY KEY  (`ax_id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB, CHARSET=utf8;

INSERT INTO `fusionax_import` ( SELECT `ax_id`, NULL, NULL FROM `fusionax_anciens` );
REPLACE INTO `fusionax_import` ( SELECT `ax_id`, `pid`, NOW() FROM `profiles` );

CREATE TEMPORARY TABLE IF NOT EXISTS tmp_update_fusionax_anciens (
  good CHAR(4) DEFAULT NULL,
  bad CHAR(4) DEFAULT NULL,
  PRIMARY KEY(bad),
  UNIQUE KEY(good)
) CHARSET=utf8;

INSERT IGNORE INTO  tmp_update_fusionax_anciens (bad, good)
            VALUES  ('TC', 'CAM'),
                    ('SH', 'CN'),
                    ('R', 'RO'),
                    ('TW', 'RC'),
                    ('TG', 'RT'),
                    ('U', 'ROU'),
                    ('KP', 'ROK'),
                    ('CRO', 'HR'),
                    ('UKR', 'UA'),
                    ('AM', 'ARM'),
                    ('CS', 'CZ'),
                    ('SU', 'RUS'),
                    ('LET', 'LV'),
                    ('MDA', 'MD');

    UPDATE  fusionax_anciens            AS f
INNER JOIN  tmp_update_fusionax_anciens AS t ON (f.Code_nationalite = t.bad)
       SET  f.Code_nationalite = t.good;
