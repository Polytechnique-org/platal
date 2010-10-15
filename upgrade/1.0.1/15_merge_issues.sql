DROP TABLE IF EXISTS profile_merge_issues;
CREATE TABLE IF NOT EXISTS profile_merge_issues (
  pid INT(11) UNSIGNED DEFAULT NULL,
  issues SET('name', 'job', 'address', 'promo', 'deathdate', 'phone', 'education') DEFAULT NULL,
  entry_year_ax INT(4) DEFAULT NULL,
  deathdate_ax DATE DEFAULT NULL,
  name VARCHAR(255) DEFAULT NULL,
  name_type TINYINT(2) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (pid),
  FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB, CHARSET=utf8;

-- vim:set syntax=mysql:
