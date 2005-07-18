alter table auth_user_quick add column banana_last timestamp not null;

--
-- Table structure for table `abos`
--

CREATE TABLE banana_abos (
  fid tinyint(3) unsigned NOT NULL default '0',
  uid smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (fid,uid)
) TYPE=MyISAM COMMENT='abonnements aux forums';

--
-- Table structure for table `list`
--

CREATE TABLE banana_list (
  fid tinyint(3) unsigned NOT NULL auto_increment,
  nom varchar(40) NOT NULL default '',
  PRIMARY KEY  (fid)
) TYPE=MyISAM COMMENT='liste des fora';

--
-- Table structure for table `profils`
--

CREATE TABLE banana_profils (
  uid smallint(5) unsigned NOT NULL default '0',
  nom varchar(50) NOT NULL default '',
  mail varchar(70) NOT NULL default '',
  sig tinytext NOT NULL,
  flags set('threads','automaj') NOT NULL default '',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

--
-- Table structure for table `respofaqs`
--

CREATE TABLE banana_respofaqs (
  fid tinyint(3) unsigned NOT NULL default '0',
  uid smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (fid,uid)
) TYPE=MyISAM COMMENT='responsables des FAQs';

