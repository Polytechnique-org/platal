-- MySQL dump 9.11
--
-- Host: localhost    Database: x4dat
-- ------------------------------------------------------
-- Server version	4.0.21-log

--
-- Table structure for table `skins`
--

use x4dat;

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

--
-- Dumping data for table `skins`
--

INSERT INTO skins VALUES (1,'Defaut','2000-03-07','Skin par défaut','Equipe X.org','default.tpl','png');
INSERT INTO skins VALUES (2,'Sharky','2003-04-01','Une skin aquatique','Jeremy Lainé','sharky.tpl','png');
INSERT INTO skins VALUES (3,'Spectral','2000-03-21','Un peu plus haut en couleurs :))','Equipe X.org','spectral.tpl','png');
INSERT INTO skins VALUES (4,'Espace','2000-03-25','Thème spatial','Equipe X.org','espace.tpl','png');
INSERT INTO skins VALUES (5,'Linux','2000-03-23','Une skin sérieuse pour polytechniciens linuxiens','Equipe X.org','linux.tpl','png');
INSERT INTO skins VALUES (6,'HumLinux','2000-03-29','Une skin sexy inspirée d\'un thème WindowMaker...','Equipe X.org','humlinux.tpl','png');
INSERT INTO skins VALUES (7,'Trapped','2002-12-21','La légende continue...','Thierry Bonnefous','trapped.tpl','png');
INSERT INTO skins VALUES (8,'NewXorg','2001-06-02','Skin avec le logo officiel de l\'association','Equipe X.org','newxorg.tpl','png');
INSERT INTO skins VALUES (9,'OldTimes','2000-03-08','La skin historique','Equipe X.org','oldtimes.tpl','png');

update auth_user_md5 set skin=skin+100;

update auth_user_md5 set skin=9 where skin=101;
update auth_user_md5 set skin=8 where skin=106;
update auth_user_md5 set skin=7 where skin=112;
update auth_user_md5 set skin=6 where skin=107;
update auth_user_md5 set skin=5 where skin=104;
update auth_user_md5 set skin=4 where skin=105;
update auth_user_md5 set skin=3 where skin=103;
update auth_user_md5 set skin=2 where skin=115;
update auth_user_md5 set skin=1 where skin>=100;
