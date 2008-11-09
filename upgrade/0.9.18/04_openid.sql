# Creates a new table to store websites trusted by each user
CREATE TABLE IF NOT EXISTS `openid_trusted` (
  id       INTEGER UNSIGNED NOT NULL auto_increment,
  # user_id == NULL means a globally trusted website
  user_id  INTEGER,
  url      VARCHAR(256) NOT NULL,
  PRIMARY KEY id_pk(id),
  UNIQUE INDEX user_id_url_index(user_id, url)
) CHARSET=utf8;

# vim:set syntax=mysql:
