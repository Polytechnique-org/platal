DROP TABLE IF EXISTS tmp_url_shortener;
CREATE TEMPORARY TABLE tmp_url_shortener LIKE url_shortener;
INSERT INTO tmp_url_shortener SELECT * FROM url_shortener;
DROP TABLE url_shortener;
CREATE TABLE url_shortener (
  alias VARCHAR(255) NOT NULL DEFAULT '',
  url TEXT NOT NULL,
  PRIMARY KEY (alias)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT INTO  url_shortener (alias, url)
     SELECT  alias, url
       FROM  tmp_url_shortener;
DROP TABLE IF EXISTS tmp_url_shortener;

-- vim:set syntax=mysql:
