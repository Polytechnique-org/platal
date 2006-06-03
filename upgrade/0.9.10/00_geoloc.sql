CREATE TABLE `geoloc_city_in_maps` (
  `city_id` int(11) NOT NULL default '0',
  `map_id` int(11) NOT NULL default '0',
  `infos` set('smallest') NOT NULL default '',
   PRIMARY KEY  (`map_id`,`infos`,`city_id`)
);
insert into admin_a VALUES (11, "Dynamap", "admin/geoloc_dynamap.php", 10);
UPDATE `adresses` AS a, `geoloc_city` AS c SET a.glng = c.lon /100000, a.glat = c.lat /100000 WHERE a.cityid = c.id;
CREATE TABLE `geoloc_maps` (
	`map_id` int(11) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `x` double NOT NULL default '0',
  `y` double NOT NULL default '0',
  `width` double NOT NULL default '0',
  `height` double NOT NULL default '0',
  `xclip` float NOT NULL default '0',
  `yclip` float NOT NULL default '0',
  `scale` int(11) NOT NULL default '1',
  `parent` int(11) default NULL,
  `path` varchar(65) NOT NULL default '',
  PRIMARY KEY  (`map_id`),
  KEY `parent` (`parent`),
  KEY `path` (`path`)
	);
