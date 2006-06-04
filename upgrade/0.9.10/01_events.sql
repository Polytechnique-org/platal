CREATE TABLE `evenements_vus` (
  `evt_id` smallint(4) unsigned NOT NULL default '0',
  `user_id` smallint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`evt_id`)
);
