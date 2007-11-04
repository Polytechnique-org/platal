CREATE TABLE IF NOT EXISTS `profile_names_display` (
  `user_id` int(11) NOT NULL COMMENT 'id of alumni',
  `display` varchar(100) NOT NULL COMMENT 'name to display',
  `sort` varchar(100) NOT NULL COMMENT 'name used for sorting',
  `reason` set('default','yourself','profile','list') NOT NULL COMMENT 'where to display it',
  `firstname` varchar(50) NOT NULL COMMENT 'first name',
  `lastname` varchar(50) NOT NULL COMMENT 'last name',
  PRIMARY KEY  (`user_id`,`display`),
  KEY `sort` (`sort`)
) CHARSET=utf8 COMMENT='Alumnis'' names to display';

INSERT INTO `profile_names_display` (
    SELECT 
        `user_id`, 
        CONCAT(`prenom`, ' ',IF(`nom_usage` != '',CONCAT(`nom_usage`,' (',`nom`,')'),`nom`)), 
        CONCAT(IF(`nom_usage` != '',`nom_usage`,`nom`),' ',`prenom`),
        'default',
        `prenom`,
        IF(`nom_usage` != '',`nom_usage`,`nom`)
        FROM `auth_user_md5`);
INSERT INTO `profile_names_display` (SELECT `user_id`, `prenom`, `prenom`, 'yourself', `prenom`, `nom` FROM `auth_user_md5`); 

CREATE TABLE IF NOT EXISTS `profile_names_search` (
  `user_id` int(11) NOT NULL COMMENT 'id of alumni',
  `search_name` varchar(50) NOT NULL COMMENT 'name to search for',
  `name_type` enum('firstname','lastname','surname') NOT NULL default 'lastname' COMMENT 'type of name',
  `search_score` smallint(6) NOT NULL COMMENT 'used to sort search results',
  PRIMARY KEY  (`nametype`,`name`,`user_id`),
  KEY `user_id` (`user_id`)
) CHARSET=utf8 COMMENT='Names of alumni (search table)';

INSERT INTO `profile_names_search` ( SELECT `user_id`, `nom`, 'lastname', 10 FROM `auth_user_md5` WHERE `nom` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `nom_usage`, 'lastname', 10 FROM `auth_user_md5` WHERE `nom` != `nom_usage` AND `nom_usage` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `nom_ini`, 'lastname', 7 FROM `auth_user_md5` WHERE `nom` != `nom_ini` AND `nom_ini` != '' AND `nom_ini` != `nom_usage`);
INSERT INTO `profile_names_search` ( SELECT `user_id`, `prenom`, 'firstname', 8 FROM `auth_user_md5` WHERE `prenom` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `prenom_ini`, 'firstname', 5 FROM `auth_user_md5` WHERE `prenom_ini` != `prenom` AND `prenom_ini` != '');
INSERT INTO `profile_names_search` ( SELECT `user_id`, `profile_nick`, 'surname', 7 FROM `auth_user_quick` WHERE `profile_nick` != '');

-- vim:set syntax=mysql:
