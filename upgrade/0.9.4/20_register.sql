-- MySQL dump 9.11

--
-- Table structure for table register_pending
--

CREATE TABLE register_pending (
  uid        varchar(32)  NOT NULL default '',
  forlife    varchar(255) NOT NULL default '',
  bestalias  varchar(255) NOT NULL default '',
  mailorg2   varchar(255)          default null,
  password   varchar(32)  NOT NULL default '',
  email      varchar(255) NOT NULL default '',
  date       date         NOT NULL default '0000-00-00',
  relance    date         NOT NULL default '0000-00-00',
  naissance  date         NOT NULL default '0000-00-00',
  hash       varchar(12)  NOT NULL default '',
  PRIMARY KEY (uid),
  UNIQUE KEY  (forlife),
  UNIQUE KEY  (bestalias),
  INDEX       (hash)
);

