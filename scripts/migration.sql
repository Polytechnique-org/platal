-- passage à diogenes
ALTER TABLE logger.sessions ADD `auth` ENUM( 'native' ) DEFAULT 'native' NOT NULL AFTER `id`;
ALTER TABLE logger.sessions ADD `sauth` ENUM( 'native' ) DEFAULT 'native' NOT NULL AFTER `host`;
INSERT INTO logger.actions SET text="connexion_auth_ext",description="connection via l'auth des groupes X";

-- modifs diogenes/logger
ALTER TABLE logger.events DROP `id` ;
ALTER TABLE logger.sessions ADD INDEX ( `uid` );
ALTER TABLE logger.sessions ADD INDEX ( `uid` );
ALTER TABLE logger.events ADD INDEX ( `session` );
DROP TABLE x4dat.log;

drop trackers;
--------------------------------------------------------------------------------


--------------------------------------------------------------------------------
-- X4DAT
--------------------------------------------------------------------------------
-- pas sur que je veuille le dropper pr le moment celui la ... a voir
--*-- ALTER TABLE x4dat.auth_user_md5 DROP COLUMN lastnewslogin;

-- lastlogin --> logger
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN lastlogin;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN host;
-- drop du soundex
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN nom_soundex;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN prenom_soundex;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN epouse_soundex;
-- drop des anciens alias --> aliases
alter table x4dat.aliases add column expire date;
ALTER TABLE x4dat.aliases CHANGE `type` `type` ENUM( 'a_vie', 'epouse', 'alias', 'homonyme', 'liste' ) DEFAULT 'alias' NOT NULL
delete from x4dat.aliases where type='';

-- auth_user_md5
update x4dat.aliases as a inner join x4dat.auth_user_md5 as u ON(u.loginbis=a.alias)
    set a.expire=ADDDATE(u.date_mise_alias_temp,INTERVAL 1 MONTH)
    WHERE u.date_mise_alias_temp!='' AND u.date_mise_alias_temp!='0000-00-00';
update x4dat.aliases as a inner join x4dat.auth_user_md5 as u ON(u.loginbis=a.alias) set a.id=u.user_id;
insert into homonymes select a.id,u.user_id from aliases as a inner join auth_user_md5 as u ON(u.loginbis=a.alias)
-- drop des colones inutiles
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN date_mise_alias_temp;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN loginbis;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN username;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN alias;
ALTER TABLE x4dat.auth_user_md5 DROP COLUMN username_sasl;
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

