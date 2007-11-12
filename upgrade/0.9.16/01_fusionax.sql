CREATE TABLE IF NOT EXISTS `profile_names_display` (
  `user_id` int(11) NOT NULL COMMENT 'id of alumni',
  `display` varchar(100) NOT NULL COMMENT 'name to display',
  `sort` varchar(100) NOT NULL COMMENT 'name used for sorting',
  `firstname` varchar(50) NOT NULL COMMENT 'first name',
  `lastname` varchar(50) NOT NULL COMMENT 'last name',
  `yourself` varchar(100) NOT NULL COMMENT 'name we used to speak to him/her',
  PRIMARY KEY  (`user_id`),
  KEY `sort` (`sort`)
) CHARSET=utf8 COMMENT='Alumnis'' names to display';

INSERT INTO `profile_names_display` (
    SELECT 
        `user_id`, 
        CONCAT(`prenom`, ' ',IF(`nom_usage` != '',CONCAT(`nom_usage`,' (',`nom`,')'),`nom`)), 
        CONCAT(IF(`nom_usage` != '',`nom_usage`,`nom`),' ',`prenom`),
        `prenom`,
        IF(`nom_usage` != '',`nom_usage`,`nom`),
        `prenom`
        FROM `auth_user_md5`);

CREATE TABLE IF NOT EXISTS `profile_names_search` (
  `user_id` int(11) NOT NULL COMMENT 'id of alumni',
  `search_name` varchar(50) NOT NULL COMMENT 'name to search for',
  `name_type` enum('firstname','lastname','surname') NOT NULL default 'lastname' COMMENT 'type of name',
  `search_score` smallint(6) NOT NULL COMMENT 'used to sort search results',
  PRIMARY KEY  (`name_type`,`search_name`,`user_id`),
  KEY `user_id` (`user_id`)
) CHARSET=utf8 COMMENT='Names of alumni (search table)';

CREATE OR REPLACE ALGORITHM=MERGE VIEW fusionax_xorg_anciens AS 
    SELECT
        u.user_id, u.matricule_ax, u.promo,
        n.display AS display_name, n.sort AS sort_name,
        u.nom, u.prenom
    FROM `auth_user_md5` AS u
    INNER JOIN `profile_names_display` AS n ON ( n.user_id = u.user_id );

INSERT INTO `profile_names_search` ( SELECT `user_id`, `nom`, 'lastname', 10 FROM `auth_user_md5` WHERE `nom` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `nom_usage`, 'lastname', 10 FROM `auth_user_md5` WHERE `nom` != `nom_usage` AND `nom_usage` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `nom_ini`, 'lastname', 7 FROM `auth_user_md5` WHERE `nom` != `nom_ini` AND `nom_ini` != '' AND `nom_ini` != `nom_usage`);
INSERT INTO `profile_names_search` ( SELECT `user_id`, `prenom`, 'firstname', 8 FROM `auth_user_md5` WHERE `prenom` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `prenom_ini`, 'firstname', 5 FROM `auth_user_md5` WHERE `prenom_ini` != `prenom` AND `prenom_ini` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `profile_nick`, 'surname', 7 FROM `auth_user_quick` WHERE `profile_nick` != '');

-- vim:set syntax=mysql:
