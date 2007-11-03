-- Import complet des anciens

DROP TABLE IF EXISTS `fusionax_anciens`;

CREATE TABLE IF NOT EXISTS `fusionax_anciens` (
  `AN` varbinary(2) NOT NULL COMMENT 'Vaut toujours AN pour cette table',
  `id_ancien` varbinary(8) NOT NULL COMMENT 'Id unique de l''ancien',
  `Login` varbinary(15) NOT NULL COMMENT 'Login sur le site de l''AX',
  `Password` int(11) NOT NULL COMMENT 'Mot de passe sur le site AX',
  `promotion_etude` int(11) NOT NULL COMMENT 'Promotion avec laquelle il/elle a fait ses études',
  `Groupe_promo` enum('','A','B','C','N','S') character set binary NOT NULL COMMENT 'Groupe de promotion (code compris seulement par AX)',
  `Nom_patronymique` varchar(50) character set utf8 NOT NULL COMMENT 'Nom patronymique (nom de jeune fille) sans la particule',
  `partic_patro` varchar(5) character set utf8 NOT NULL COMMENT 'Particule du nom patronymique',
  `prenom` varchar(30) character set utf8 NOT NULL COMMENT 'Prénom',
  `Nom_usuel` varchar(50) character set utf8 NOT NULL COMMENT 'Nom usuel (nom marital par exemple) sans la particule',
  `partic_nom` varchar(5) character set utf8 NOT NULL COMMENT 'Particule du nom usuel',
  `Nom_complet` varchar(80) character set utf8 NOT NULL COMMENT 'Nom patronymique complet (avec la particule)',
  `Civilite` enum('','.','M','MME','MLLE') character set utf8 NOT NULL COMMENT 'Civilité',
  `Code_nationalite` varbinary(2) NOT NULL COMMENT 'Nationalité (code)',
  `Type_membre` enum('','*','F','FB','P','PB','T','TB','TA') character set binary NOT NULL COMMENT 'Type de membre (code compris seulement par AX)',
  `corps_sortie` varchar(10) character set utf8 NOT NULL COMMENT 'Corps de sortie (ou D si aucun)',
  `Date_deces` DATE COMMENT 'Date de décès',
  `grade` varbinary(20) NOT NULL COMMENT 'Grade actuel dans son corps',
  `Mel_usage` varbinary(150) NOT NULL COMMENT 'Adresse e-mail d''usage',
  `Mel_publiable` tinyint(4) NOT NULL COMMENT 'Autorisation d''utiliser le mail',
  `tel_mobile` varbinary(30) NOT NULL COMMENT 'Numéro de téléphone mobile',
  `annee_dernCot` int(11) NOT NULL COMMENT 'Année de dernière cotisation AX',
  `Representant` enum('','K','DE') character set binary NOT NULL COMMENT 'Représentant de promotion',
  `hash_adr_defaut` binary(5) NOT NULL COMMENT 'Hash de l''adresse par défaut',
  `Date_maj` DATE NOT NULL,
  PRIMARY KEY  (`id_ancien`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

LOAD DATA LOCAL INFILE 'Anciens.txt' INTO TABLE `fusionax_anciens` FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\r\n'
(`AN`, `id_ancien`, `Login`, `Password`, `promotion_etude`, `Groupe_promo`, `Nom_patronymique`, `partic_patro`, `prenom`, Nom_usuel, partic_nom,
    Nom_complet, Civilite, Code_nationalite, Type_membre, corps_sortie, @StringDate_deces, grade, Mel_usage, Mel_publiable,
    tel_mobile, annee_dernCot, Representant, @Type_adr_defaut, @AdrC_Ligne1, @AdrC_Ligne2, @AdrC_Ligne3, @AdrC_code_postal, @AdrC_ville,
    @AdrC_zip_cedex, @AdrC_etat_distr, @AdrC_pays, @tel, @fax, @StringDate_maj)
SET
    `hash_adr_defaut` = SUBSTRING( MD5( @Type_adr_defaut ), 1, 5),
    `Date_maj` = CONCAT(SUBSTRING(@StringDate_maj,7),'-',SUBSTRING(@StringDate_maj,4,2),'-',SUBSTRING(@StringDate_maj,1,2)),
    `Date_deces` = CONCAT(SUBSTRING(@StringDate_deces,7),'-',SUBSTRING(@StringDate_deces,4,2),'-',SUBSTRING(@StringDate_deces,1,2));

DROP TABLE IF EXISTS `fusionax_import`;
 
CREATE TABLE IF NOT EXISTS `fusionax_import` (
  `id_ancien` binary(8) NOT NULL COMMENT 'identifiant AX de l''ancien',
  `user_id` int(11) default NULL COMMENT 'identifiant x.org de l''ancien si on l''a trouvé',
  `date_match_id` timestamp NULL default NULL COMMENT 'date de mise en correspondance des identifiants',
  PRIMARY KEY  (`id_ancien`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `fusionax_import` ( SELECT `id_ancien`, NULL, NULL FROM `fusionax_anciens` );

DROP VIEW IF EXISTS `fusionax_deceased`;

CREATE VIEW `fusionax_deceased` AS 
SELECT u.user_id, a.id_ancien, u.nom, u.prenom, u.promo, u.deces AS deces_xorg, a.Date_deces AS deces_ax
FROM `auth_user_md5` AS u
INNER JOIN fusionax_anciens AS a ON ( a.id_ancien = u.matricule_ax )
WHERE u.deces != a.Date_deces;
