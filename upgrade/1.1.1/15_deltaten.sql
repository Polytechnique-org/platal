DROP TABLE IF EXISTS profile_deltaten;
CREATE TABLE `profile_deltaten` (
  `pid` int(11) unsigned NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  PRIMARY KEY (`pid`),
  CONSTRAINT FOREIGN KEY (`pid`) REFERENCES `profiles` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
