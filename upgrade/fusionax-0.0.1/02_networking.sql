CREATE TABLE IF NOT EXISTS `profile_networking_enum` (
    `network_type` tinyint unsigned NOT NULL,
    `name` varchar(30) NOT NULL,
    `icon` varchar(50) NOT NULL COMMENT 'icon filename',
    `filter` enum('email','web','none') NOT NULL DEFAULT 'none' COMMENT 'filter type for addresses',
    PRIMARY KEY (`network_type`)
) CHARSET=utf8 COMMENT='types of networking addresses';

CREATE TABLE IF NOT EXISTS `profile_networking` (
    `uid` smallint unsigned NOT NULL COMMENT 'user id',
    `nwid` tinyint unsigned NOT NULL COMMENT 'number of the address for the user',
    `network_type` tinyint unsigned NOT NULL,
    `address` varchar(255) NOT NULL,
    `pub` enum('private','public') NOT NULL DEFAULT 'private',
    PRIMARY KEY (`uid`, `nwid`)
) CHARSET=utf8 COMMENT='networking addresses';


INSERT INTO `profile_networking_enum` (`network_type`, `name`, `icon`, `filter`)
     VALUES (0, 'Page web', 'web.gif', 'web');

INSERT INTO `profile_networking` (`uid`, `nwid`, `network_type`, `address`, `pub`)
     SELECT `user_id`, 0, 0, `profile_web`, `profile_web_pub`
       FROM `auth_user_quick`
      WHERE `profile_web` <> "";

