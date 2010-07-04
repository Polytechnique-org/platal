DROP TABLE IF EXISTS profile_modifications;

CREATE TABLE profile_modifications (
  pid INT(11) NOT NULL,
  uid INT(11) NOT NULL,
  field VARCHAR(60) NOT NULL,
  oldText TEXT NOT NULL,
  newText TEXT NOT NULL,
  pub ENUM('private', 'ax', 'public') NOT NULL DEFAULT 'private',
  PRIMARY KEY(pid, uid, field)
) ENGINE=InnoDB, CHARSET=utf8;

-- vim:set syntax=mysql:
