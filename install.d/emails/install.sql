--
-- Table structure for table `emails`
--

CREATE TABLE `emails` (
  `uid` smallint(5) unsigned NOT NULL default '0',
  `email` char(60) NOT NULL default '',
  `rewrite` char(128) NOT NULL default '',
  `panne` date NOT NULL default '0000-00-00',
  `flags` set('active','filter','mtic') NOT NULL default '',
  PRIMARY KEY  (`uid`,`email`),
  KEY `emails` (`email`(20)),
  KEY `uid` (`uid`)
) TYPE=MyISAM PACK_KEYS=1 COMMENT='listes des emails';

--
-- Table structure for table `virtual`
--

CREATE TABLE `virtual` (
  `vid` int(11) NOT NULL auto_increment,
  `alias` varchar(255) NOT NULL default '',
  `type` enum('user','list','dom') NOT NULL default 'user',
  PRIMARY KEY  (`vid`),
  UNIQUE KEY `alias` (`alias`)
) TYPE=MyISAM;

--
-- Table structure for table `virtual_domains`
--

CREATE TABLE `virtual_domains` (
  `domain` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`domain`)
) TYPE=MyISAM;

--
-- Table structure for table `virtual_redirect`
--

CREATE TABLE `virtual_redirect` (
  `vid` int(11) NOT NULL auto_increment,
  `redirect` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`vid`,`redirect`),
  KEY `redirect` (`redirect`)
) TYPE=MyISAM;

