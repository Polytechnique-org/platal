DROP TABLE IF EXISTS tmp_profile_medals;
CREATE TEMPORARY TABLE tmp_profile_medals LIKE profile_medals;
INSERT INTO tmp_profile_medals SELECT * FROM profile_medals;
DROP TABLE profile_medals;
CREATE TABLE profile_medals (
  pid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  mid INT(11) NOT NULL DEFAULT 0,
  gid INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (pid, mid, gid),
  CONSTRAINT profile_medals_ibfk_1 FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO  profile_medals (pid, mid, gid)
     SELECT  pid, mid, gid
       FROM  tmp_profile_medals;
DROP TABLE IF EXISTS tmp_profile_medals;

-- vim:set syntax=mysql:
