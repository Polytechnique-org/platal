alter table auth_user_quick drop column skin;

--
-- Table structure for table `skins`
--

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
INSERT INTO skins VALUES (10,'NBviolet','2000-03-08','Purple Rain en noir et blanc','Equipe X.org','nbviolet.tpl','png');
INSERT INTO skins VALUES (11,'LiteSkin','','Un thème visuellement léger et doux','Alexandre HÔ (Xandrex)','liteskin.tpl','png');

