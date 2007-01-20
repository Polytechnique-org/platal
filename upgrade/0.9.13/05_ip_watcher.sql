CREATE TABLE `ip_watch` (
    `ip` CHAR(16) NOT NULL,
    `state` ENUM('safe', 'unsafe', 'dangerous', 'ban') NOT NULL DEFAULT 'unsafe',
    `detection` DATE DEFAULT 0,
    `last` TIMESTAMP DEFAULT 0,
    `uid`  SMALLINT(5) DEFAULT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY(`ip`)
);
use logger;
alter table sessions add key(ip);
insert into actions (text, description) values ('view_page', 'Consultation d\'une page');
use x4dat;

# vim:set syntax=mysql:
