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

-- optim trackers
drop trackers;

--
-- X4DAT
--

-- lastlogin --> logger
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN lastnewslogin;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN lastlogin;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN host;
INSERT INTO logger.actions SET text="connexion_auth_ext",description="connection via l'auth des groupes X";
