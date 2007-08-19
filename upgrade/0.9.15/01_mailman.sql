CREATE TABLE ml_moderate (
    ml VARCHAR(64) NOT NULL,
    domain VARCHAR(64) NOT NULL,
    mid SMALLINT(5) UNSIGNED NOT NULL,
    uid SMALLINT(5) UNSIGNED NOT NULL,
    action ENUM('accept', 'refuse', 'delete') NOT NULL,
    ts TIMESTAMP NOT NULL,
    message TEXT,
    handler INT(8) UNSIGNED DEFAULT NULL,
    PRIMARY KEY(ml, domain, mid)
) CHARSET=utf8;

# vim:set syntax=mysql:
