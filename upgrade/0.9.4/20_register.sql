-- MySQL dump 9.11

--
-- Table structure for table register_pending
--

CREATE TABLE register_pending (
    uid         int          NOT NULL default '0',
    forlife     varchar(255) NOT NULL default '',
    bestalias   varchar(255) NOT NULL default '',
    mailorg2    varchar(255)          default null,
    password    varchar(32)  NOT NULL default '',
    email       varchar(255) NOT NULL default '',
    date        date         NOT NULL default '0000-00-00',
    relance     date         NOT NULL default '0000-00-00',
    naissance   date         NOT NULL default '0000-00-00',
    hash        varchar(12)  NOT NULL default '',
    PRIMARY KEY (uid),
    UNIQUE KEY  (forlife),
    UNIQUE KEY  (bestalias),
    INDEX       (hash)
);

create table register_marketing (
    uid         int          not null default 0,
    sender      int          not null default 0,
    email       varchar(255) not null default '',
    date        date         not null default '0000-00-00',
    last        date         not null default '0000-00-00',
    nb          tinyint      not null default 0,
    type        set('user', 'staff') not null default 'user',
    INDEX (uid),
    INDEX (date),
    INDEX (last),
    INDEX (nb)
);

create table register_mstats (
    uid         int          not null default 0,
    sender      int          not null default 0,
    success     date         not null default '0000-00-00',
    PRIMARY KEY (uid)
);
    
