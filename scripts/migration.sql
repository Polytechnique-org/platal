-- passage à diogenes
ALTER TABLE `logger.sessions` ADD `auth` ENUM( 'native' ) DEFAULT 'native' NOT NULL AFTER `id`;
ALTER TABLE `logger.sessions` ADD `sauth` ENUM( 'native' ) DEFAULT 'native' NOT NULL AFTER `host`;

-- gestion des skins
ALTER TABLE `x4dat.skins` ADD COLUMN `skin_tpl` VARCHAR(32) AFTER `id`;


-- modifs diogenes/logger
USE logger;
ALTER TABLE `events` DROP `id` ;
ALTER TABLE `sessions` ADD INDEX ( `uid` );
ALTER TABLE `sessions` ADD INDEX ( `uid` );
ALTER TABLE `events` ADD INDEX ( `session` );
USE diogenes;
ALTER TABLE `diogenes_logevents` DROP `id` ;
ALTER TABLE `diogenes_logsessions` ADD INDEX ( `uid` );
ALTER TABLE `diogenes_logsessions` ADD INDEX ( `start` );
ALTER TABLE `diogenes_logevents` ADD INDEX ( `session` );

-- optim trackers
drop trackers;
