DROP TABLE IF EXISTS account_xnet_lost_passwords;

CREATE TABLE account_xnet_lost_passwords (
  uid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  date DATETIME NULL DEFAULT NULL,
  hash CHAR(32) NOT NULL DEFAULT '',
  PRIMARY KEY (uid),
  KEY hash (hash),
  FOREIGN KEY (uid) REFERENCES accounts (uid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARSET=utf8;

-- vim:set syntax=mysql:
