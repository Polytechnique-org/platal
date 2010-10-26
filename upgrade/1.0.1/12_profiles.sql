DROP TABLE IF EXISTS profile_modifications;

CREATE TABLE profile_modifications (
  pid INT(11) UNSIGNED DEFAULT NULL,
  uid INT(11) UNSIGNED DEFAULT NULL,
  field VARCHAR(60) NOT NULL,
  oldText TEXT NOT NULL,
  newText TEXT NOT NULL,
  pub ENUM('private', 'ax', 'public') NOT NULL DEFAULT 'private',
  PRIMARY KEY(pid, uid, field),
  FOREIGN KEY (uid) REFERENCES accounts (uid) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARSET=utf8;

-- vim:set syntax=mysql:
