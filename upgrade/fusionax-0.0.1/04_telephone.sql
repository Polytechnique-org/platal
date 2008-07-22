CREATE TABLE IF NOT EXISTS `profile_phones` (
    `uid` smallint unsigned NOT NULL,
    `link_type` enum('address', 'pro', 'user') NOT NULL DEFAULT 'user' COMMENT 'type of parent element',
    `link_id` tinyint unsigned NOT NULL COMMENT 'id of his parent element',
    `tel_id` tinyint unsigned NOT NULL COMMENT 'index of this number for the couple (user, parent element)',
    `tel_type` enum('fixed', 'mobile', 'fax') NOT NULL DEFAULT 'fixed',
    `search_tel` varchar(25) NOT NULL COMMENT 'search number in an international format with only digits and the initial +',
    `display_tel` varchar(30) NOT NULL COMMENT 'display number',
    `pub` enum('private', 'ax', 'public') NOT NULL DEFAULT 'private',
    `comment` varchar(80) NOT NULL,
    PRIMARY KEY(`uid`, `link_type`, `link_id`, `tel_id`),
    INDEX (`search_tel`)
);


-- Adds a temporary column to convert phone prefixes from varchar to int
ALTER TABLE `geoloc_pays` ADD COLUMN `tmp_phoneprf` smallint unsigned NULL;

-- Adds phone format column
ALTER TABLE `geoloc_pays` ADD COLUMN `phoneformat` varchar(25) NOT NULL AFTER `nat`;

