use groupex;

CREATE TABLE `announces_photo` (
  `eid` smallint(4) unsigned NOT NULL,
  `attachmime` enum('jpeg','png','gif') NOT NULL default 'jpeg',
  `attach` blob NOT NULL,
  `x` smallint(5) unsigned NOT NULL default '0',
  `y` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`eid`)
) CHARSET=utf8;

alter table groupex.announces change column flags flags set('public', 'photo') not null;

use x4dat;

# vim:set syntax=mysql:
