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

--------------------------------------------------------------------------------
-- X4DAT
--------------------------------------------------------------------------------
DROP TABLE x4dat.log
-- pas sur que je veuille le dropper pr le moment celui la ... a voir
--*-- ALTER TABLE x4dat.auth_user_md5 DROP COLUMN lastnewslogin;

-- lastlogin --> logger
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN lastlogin;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN host;
-- drop du soundex
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN nom_soundex;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN prenom_soundex;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN epouse_soundex;
INSERT INTO logger.actions SET text="connexion_auth_ext",description="connection via l'auth des groupes X";
-- drop des anciens alias --> aliases
--*-- ALTER TABLE x4dat.auth_user_md5 DROP COLUMN loginbis;
--*-- ALTER TABLE x4dat.auth_user_md5 DROP COLUMN alias;

--------------------------------------------------------------------------------

--------------------------------------------------------------------------------
-- SKINS v2
--------------------------------------------------------------------------------
DROP TABLE IF EXISTS skins;
CREATE TABLE skins (
  id tinyint(1) unsigned NOT NULL default '0',
  name varchar(32) NOT NULL default '',
  date varchar(10) NOT NULL default '',
  comment varchar(255) NOT NULL default '',
  auteur varchar(30) NOT NULL default '',
  skin_tpl varchar(32) NOT NULL default '',
  ext char(3) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM PACK_KEYS=1 COMMENT='liste des skins';

INSERT INTO skins VALUES (1,'Defaut','2000-03-07','Skin par défaut','Equipe X.org','default.tpl','png');
--------------------------------------------------------------------------------

