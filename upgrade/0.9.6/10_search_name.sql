
CREATE TABLE `search_name` (
  `uid` int(11) NOT NULL default '0',
  `token` char(255) NOT NULL default '',
  `score` int(11) NOT NULL default '0',
  PRIMARY KEY (token,uid),
  INDEX (uid)
);


drop table recherche;

