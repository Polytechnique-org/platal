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

-- cgt de type pour les ML
ALTER TABLE x4dat.requests CHANGE `type` `type` ENUM('alias','epouse','liste','photo','sondage','emploi','evts' ) DEFAULT 'alias' NOT NULL
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
-- MELIX
--------------------------------------------------------------------------------
update groupex.aliases       AS gx
    inner join x4dat.aliases AS a ON (gx.email = a.alias)
    inner join x4dat.aliases AS b ON (a.id=b.id AND b.type='a_vie')
    set gx.email = b.alias
    where gx.id = 12

--------------------------------------------------------------------------------
-- DROP des LISTES
--------------------------------------------------------------------------------
drop table x4dat.listes_def;
drop table x4dat.listes_ins;
drop table x4dat.listes_mod;
--------------------------------------------------------------------------------

