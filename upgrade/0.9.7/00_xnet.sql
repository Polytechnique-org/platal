ALTER TABLE virtual CHANGE `type` `type` ENUM('user', 'list', 'dom', 'evt') DEFAULT 'user' NOT NULL;

ALTER TABLE groupex.asso ADD `pub` ENUM( 'public', 'private' ) DEFAULT 'public' NOT NULL AFTER `ax` ;
INSERT INTO virtual_domains VALUES ('evts.polytechnique.org'); 

use groupex;

DROP TABLE IF EXISTS groupex.evenements;
CREATE TABLE groupex.evenements (
	`eid` int unsigned NOT NULL auto_increment,
	`asso_id` int unsigned NOT NULL default '0',
	`organisateur_uid` int unsigned NOT NULL default '0',
	`intitule` varchar(100) NOT NULL default '',
	`short_name` varchar(30) NOT NULL default '',
	`paiement_id` int unsigned default NULL,
	`descriptif` text NOT NULL,
	`debut` datetime NOT NULL default '0000-00-00 00:00:00',
	`fin` datetime default NULL,
	`membres_only` tinyint(1) NOT NULL default '0',
	`advertise` tinyint(1) NOT NULL default '0',
	`show_participants` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`eid`)
);

drop table IF EXISTS evenements_items;
CREATE TABLE evenements_items (
        `eid` int unsigned NOT NULL default '0',
        `item_id` int unsigned NOT NULL default '0',
        `titre` varchar(100) NOT NULL default '',
        `details` text NOT NULL,
        `montant` decimal(10,2) NOT NULL default '0.00',
        PRIMARY KEY  (`eid`,`item_id`)
);

drop table IF EXISTS evenements_participants;
CREATE TABLE evenements_participants (
        `eid` int unsigned NOT NULL default '0',
        `uid` int unsigned NOT NULL default '0',
        `item_id` int NOT NULL default '0',
        `nb` int NOT NULL default '0',
        `paid` float NOT NULL default '0',
        PRIMARY KEY  (`eid`,`item_id`,`uid`)
);

