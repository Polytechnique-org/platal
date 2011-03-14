DROP TABLE IF EXISTS url_shortener;

CREATE TABLE url_shortener (
  alias CHAR(6) NOT NULL DEFAULT '',
  url TEXT NOT NULL,
  PRIMARY KEY (alias)
) ENGINE=InnoDB, CHARSET=utf8;

-- vim:set syntax=mysql:
