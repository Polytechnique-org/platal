CREATE TABLE group_former_members (
  asso_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  uid INT(11) UNSIGNED NOT NULL DEFAULT 0,
  unsubsciption_date DATE NOT NULL,
  PRIMARY KEY (asso_id, uid),
  KEY uid (uid),
  CONSTRAINT group_former_members_ibfk_1 FOREIGN KEY (uid) REFERENCES accounts (uid) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT group_former_members_ibfk_2 FOREIGN KEY (asso_id) REFERENCES groups (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- vim:set syntax=mysql:
