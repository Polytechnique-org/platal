CREATE TABLE `ip_watch` (
    `ip` CHAR(16) NOT NULL,
    `state` ENUM('safe', 'unsafe', 'dangerous', 'ban') NOT NULL DEFAULT 'unsafe',
    `detection` DATE DEFAULT 0,
    `last` TIMESTAMP DEFAULT 0,
    `uid`  SMALLINT(5) DEFAULT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY(`ip`)
);

# vim:set syntax=mysql:
