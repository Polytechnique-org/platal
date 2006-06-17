CREATE TABLE `tels` (
  `uid` smallint(5) unsigned NOT NULL default '0',
  `adrid` tinyint(1) unsigned NOT NULL default '0',
  `telid` tinyint(1) unsigned NOT NULL default '0',
  `tel_type` varchar(20) NOT NULL default '',
  `tel_pub` enum('private','ax','public') NOT NULL default 'private',
  `tel` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`uid`,`adrid`,`telid`)
  );
INSERT INTO tels SELECT uid, adrid, 0, 'Tél.', tel_pub, tel FROM adresses;
INSERT INTO tels SELECT uid, adrid, 1, 'Fax', tel_pub, fax FROM adresses;
DELETE FROM tels WHERE tel = "";
ALTER TABLE `adresses` DROP `tel`, DROP `tel_pub`;
