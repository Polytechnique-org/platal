CREATE TABLE profile_manageurs (
    pid INT(11) UNSIGNED NOT NULL DEFAULT 0,
    title VARCHAR(255) NOT NULL DEFAULT '',
    entry_year INT(4) NULL DEFAULT NULL,
    project MEDIUMTEXT NULL DEFAULT NULL,
    anonymity INT(1) UNSIGNED NOT NULL DEFAULT 0,
    visibility ENUM('visible', 'visible_exceptions', 'blocked') NOT NULL DEFAULT 'blocked',
    communication SET('novelties', 'nl', 'survey') NOT NULL DEFAULT '',
    push ENUM('unique', 'weekly', 'never') NOT NULL DEFAULT 'never',
    email VARCHAR(255) NOT NULL DEFAULT '',
    network INT(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (pid),
    CONSTRAINT FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
