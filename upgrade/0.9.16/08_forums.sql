USE forums;

CREATE TABLE IF NOT EXISTS `innd` (
  `id_innd` int(10) unsigned NOT NULL auto_increment,
  `ipmin` int(10) unsigned default '0' COMMENT 'ip_address',
  `ipmax` int(10) unsigned default '4294967295' COMMENT 'ip_address',
  `uid` smallint(5) unsigned default NULL,
  `read_perm` varchar(100) default '!*',
  `write_perm` varchar(100) default '!*',
  `priority` tinyint(1) unsigned default '255',
  `comment` text,
  PRIMARY KEY  (`id_innd`)
) DEFAULT CHARSET=utf8;

INSERT INTO `innd` VALUES (1,0,4294967295,NULL,'xorg.*,!xorg.prive.*','xorg.*,!xorg.prive.*,!xorg.pa.emploi',0,'par defaut pour tous');
