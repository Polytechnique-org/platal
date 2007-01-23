CREATE TABLE `mx_watch` (
    `host` VARCHAR(64) NOT NULL,
    `state` ENUM('ok', 'warning', 'broken') DEFAULT 'ok',
    `text` MEDIUMTEXT NOT NULL,
    PRIMARY KEY(`host`)
);

# vim:set syntax=mysql:
