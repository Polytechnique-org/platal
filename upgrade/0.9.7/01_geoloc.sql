ALTER TABLE adresses
CHANGE ville city VARCHAR(80) DEFAULT '' NOT NULL,
CHANGE cp postcode VARCHAR(20) DEFAULT '' NOT NULL,
CHANGE pays country VARCHAR(2) DEFAULT '00' NOT NULL;
ALTER TABLE entreprises
CHANGE ville city VARCHAR(80) DEFAULT '' NOT NULL,
CHANGE cp postcode VARCHAR(20) DEFAULT '' NOT NULL,
CHANGE pays country VARCHAR(2) DEFAULT '00' NOT NULL;

ALTER TABLE adresses ADD cityid INT DEFAULT NULL AFTER city;

ALTER TABLE adresses ADD INDEX(cityid);

CREATE TABLE `geoloc_city` (
	`id` int(11) NOT NULL default '0',
	`alias` smallint(6) NOT NULL default '0',
	`name` varchar(200) NOT NULL default '',
	`search_name` varchar(200) NOT NULL default '',
	`prefix` varchar(30) NOT NULL default '',
	`lat` int(11) NOT NULL default '0',
	`lon` int(11) NOT NULL default '0',
	`country` char(2) NOT NULL default '',
	`region` char(2) NOT NULL default '',
	`subregion` varchar(200) NOT NULL default '',
	`postcode` varchar(8) NOT NULL default '',
	`level` tinyint(4) NOT NULL default '5',
	`population` int(11) NOT NULL default '0',
	`x` float NOT NULL default '0',
	`y` float NOT NULL default '0',
	`z` float NOT NULL default '0',
	PRIMARY KEY  (`id`,`alias`),
	KEY `famous` (`level`,`search_name`),
	KEY `name` (`country`,`search_name`),
	KEY `postcode` (`country`,`postcode`),
	KEY `lat` (`lat`),
	KEY `lon` (`lon`)
);
