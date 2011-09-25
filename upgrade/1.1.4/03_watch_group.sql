CREATE TABLE watch_group (
  uid int(11) unsigned NOT NULL DEFAULT '0',
  groupid smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (uid, groupid),
  CONSTRAINT FOREIGN KEY (uid) REFERENCES accounts (uid) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT FOREIGN KEY (groupid) REFERENCES groups (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- vim:set syntax=mysql:
