DROP TABLE IF EXISTS tmp_profile_modifications;
CREATE TEMPORARY TABLE tmp_profile_modifications LIKE profile_modifications;
INSERT INTO tmp_profile_modifications SELECT * FROM profile_modifications;
DROP TABLE profile_modifications;
CREATE TABLE profile_modifications (
  pid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  uid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  field VARCHAR(60) NOT NULL,
  oldText TEXT NOT NULL,
  newText TEXT NOT NULL,
  type ENUM('self', 'third_party') NOT NULL DEFAULT 'self',
  timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (pid, field),
  KEY uid (uid),
  FOREIGN KEY (uid) REFERENCES accounts (uid) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO  profile_modifications (pid, uid, field, oldText, newText, type)
     SELECT  pid, uid, field, oldText, newText, 'third_party'
       FROM  tmp_profile_modifications;
DROP TABLE IF EXISTS tmp_profile_modifications;

-- vim:set syntax=mysql:
