ALTER TABLE groupex.asso ADD `pub` ENUM( 'public', 'private' ) DEFAULT 'public' NOT NULL AFTER `ax` ;
ALTER TABLE groupex.evenements_participants ADD PRIMARY KEY (eid, item_id, uid);
ALTER TABLE groupex.evenements_participants ADD paid FLOAT DEFAULT 0 NOT NULL;
ALTER TABLE virtual CHANGE `type` `type` ENUM('user', 'list', 'dom', 'evt') DEFAULT 'user' NOT NULL;
DROP TABLE groupex.evenements;
CREATE TABLE groupex.evenements (
	`eid` int(8) unsigned NOT NULL auto_increment,
	`asso_id` int(8) unsigned NOT NULL default '0',
	`organisateur_uid` int(8) unsigned NOT NULL default '0',
	`intitule` varchar(100) NOT NULL default '',
	`short_name` varchar(30) NOT NULL default '',
	`paiement_id` int(8) unsigned default NULL,
	`descriptif` text NOT NULL,
	`debut` datetime NOT NULL default '0000-00-00 00:00:00',
	`fin` datetime default NULL,
	`membres_only` tinyint(1) NOT NULL default '0',
	`advertise` tinyint(1) NOT NULL default '0',
	`show_participants` tinyint(1) NOT NULL default '0',
	PRIMARY KEY  (`eid`)
) TYPE=MyISAM;
INSERT INTO virtual_domains VALUES ('evts.polytechnique.org'); 

