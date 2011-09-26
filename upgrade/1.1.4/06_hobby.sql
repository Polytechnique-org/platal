CREATE TABLE profile_hobby (
  pid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  id TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'number of the hobby for the user',
  type ENUM('Sport', 'Loisir', 'Hobby') NOT NULL DEFAULT 'Hobby',
  text VARCHAR(255) NOT NULL DEFAULT '',
  pub ENUM('hidden','private','ax','public') NOT NULL DEFAULT 'private',
  PRIMARY KEY (pid, id),
  KEY pid (pid),
  CONSTRAINT FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- vim:set syntax=mysql:
