CREATE TABLE requests_hidden (
  user_id SMALLINT UNSIGNED NOT NULL,
  hidden_requests TEXT NOT NULL,
  PRIMARY KEY(user_id)
) CHARSET=utf8;

# vim:set syntax=mysql:
