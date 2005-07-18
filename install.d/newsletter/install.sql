insert into admin_h2 VALUES (2,6,'Newsletter',30);

INSERT INTO admin_a VALUES (6,'Liste','admin/newsletter.php',0);
INSERT INTO admin_a VALUES (6,'Catégories','admin/newsletter_cats.php',10);
INSERT INTO admin_a VALUES (6,'Adresses en panne','admin/newsletter_pattecassee.php',20);

--
-- Table structure for table `newsletter`
--

CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `titre` varchar(255) NOT NULL default '',
  `bits` enum('sent','new') NOT NULL default 'new',
  `head` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='liste des NL envoyes';

--
-- Table structure for table `newsletter_art`
--

CREATE TABLE `newsletter_art` (
  `id` int(10) unsigned NOT NULL default '0',
  `aid` smallint(5) unsigned NOT NULL default '0',
  `cid` smallint(5) unsigned NOT NULL default '0',
  `pos` tinyint(3) unsigned NOT NULL default '0',
  `title` mediumtext NOT NULL,
  `body` mediumtext NOT NULL,
  `append` mediumtext NOT NULL,
  PRIMARY KEY  (`id`,`aid`)
) TYPE=MyISAM;

--
-- Table structure for table `newsletter_cat`
--

CREATE TABLE `newsletter_cat` (
  `cid` tinyint(3) unsigned NOT NULL auto_increment,
  `pos` tinyint(3) unsigned NOT NULL default '0',
  `titre` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`cid`),
  KEY `pos` (`pos`)
) TYPE=MyISAM;

--
-- Table structure for table `newsletter_ins`
--

CREATE TABLE `newsletter_ins` (
  `user_id` smallint(4) unsigned NOT NULL default '0',
  `last` int(11) NOT NULL default '0',
  `pref` enum('text','html') NOT NULL default 'html',
  PRIMARY KEY  (`user_id`),
  KEY `last` (`last`)
) TYPE=MyISAM COMMENT='liste des abonnés à la newsletter';

