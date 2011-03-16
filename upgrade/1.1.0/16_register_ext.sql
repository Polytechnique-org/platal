DROP TABLE IF EXISTS register_pending_xnet;

CREATE TABLE register_pending_xnet (
  uid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  hruid VARCHAR(255) NOT NULL DEFAULT '',
  email VARCHAR(255) NOT NULL DEFAULT '',
  date DATE NOT NULL DEFAULT '0000-00-00',
  hash VARCHAR(12) NOT NULL DEFAULT '',
  PRIMARY KEY (uid),
  UNIQUE KEY hruid (hruid),
  KEY hash (hash),
  FOREIGN KEY (uid) REFERENCES accounts (uid) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (hruid) REFERENCES accounts (hruid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARSET=utf8;

-- vim:set syntax=mysql:
