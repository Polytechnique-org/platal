CREATE TABLE `emails_watch` (
    `email` CHAR(60) NOT NULL,
    `state` ENUM('pending', 'safe', 'unsafe', 'dangerous') NOT NULL DEFAULT 'pending',
    `detection` DATE DEFAULT 0,
    `last` TIMESTAMP DEFAULT 0,
    `uid`  SMALLINT(5) DEFAULT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY(`email`)
);
