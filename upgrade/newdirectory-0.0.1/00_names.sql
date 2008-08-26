CREATE TABLE IF NOT EXISTS `profile_names_display` (
  `user_id` int(11) NOT NULL COMMENT 'id of alumni',
  `display` varchar(100) NOT NULL COMMENT 'name to display',
  `sort` varchar(100) NOT NULL COMMENT 'name used for sorting',
  `tooltip` varchar(150) NOT NULL COMMENT 'text to explain display name if needed',
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
        CONCAT(IF(`nom_usage` != '',`nom_usage`,`nom`),', ',`prenom`),
        '',
        `prenom`,
        IF(`nom_usage` != '',`nom_usage`,`nom`),
        `prenom`
        FROM `auth_user_md5`);

UPDATE `profile_names_display` AS n INNER JOIN `auth_user_md5` AS u ON n.lastname = u.prenom
SET n.tooltip = CONCAT('Prénom : ', n.firstname,' - Nom : ', n.lastname);

CREATE TABLE IF NOT EXISTS `profile_names_search` (
  `user_id` int(11) NOT NULL COMMENT 'id of alumni',
  `sn_id` smallint(6) NOT NULL COMMENT 'id of this search name in all alumni''s search names',
  `search_name` varchar(50) NOT NULL COMMENT 'name to search for',
  `name_type` enum('firstname','lastname','surname') NOT NULL default 'lastname' COMMENT 'type of name',
  `search_score` smallint(6) NOT NULL COMMENT 'used to sort search results',
  `pub` enum('always public','public','private') NOT NULL default 'private' COMMENT 'searchable on public site or only on private',
  PRIMARY KEY  (`name_type`,`search_name`,`user_id`),
  KEY `user_id` (`user_id`)
) CHARSET=utf8 COMMENT='Names of alumni (search table)';

INSERT INTO `profile_names_search` ( SELECT `user_id`, 0, `nom`, 'lastname', 10, 'always public' FROM `auth_user_md5` WHERE `nom` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, 1, `nom_usage`, 'lastname', 10, 'public' FROM `auth_user_md5` WHERE `nom` != `nom_usage` AND `nom_usage` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, 2, `nom_ini`, 'lastname', 7, 'private' FROM `auth_user_md5` WHERE `nom` != `nom_ini` AND `nom_ini` != '' AND `nom_ini` != `nom_usage`);
INSERT INTO `profile_names_search` ( SELECT `user_id`, 3, `prenom`, 'firstname', 8, 'always public' FROM `auth_user_md5` WHERE `prenom` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, 4, `prenom_ini`, 'firstname', 5, 'private' FROM `auth_user_md5` WHERE `prenom_ini` != `prenom` AND `prenom_ini` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, 5, `profile_nick`, 'surname', 7, 'private' FROM `auth_user_quick` WHERE `profile_nick` != '');

-- vim:set syntax=mysql:
