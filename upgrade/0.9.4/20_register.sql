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

create table register_marketing (/*{{{*/
    uid         int          not null default 0,
    sender      int          not null default 0,
    email       varchar(255) not null default '',
    date        date         not null default '0000-00-00',
    last        date         not null default '0000-00-00',
    nb          tinyint      not null default 0,
    type        set('user', 'staff') not null default 'user',
    hash        char(32)     not null,
    PRIMARY KEY (uid, email),
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


--
-- envoidirect -> register_mstats
--

insert into register_mstats (uid, sender, success)
     select u.user_id, e.sender, u.date_ins
       from envoidirect   as e
 inner join auth_user_md5 as u using(matricule)
      where u.date_ins != 0;

insert into register_marketing (uid, sender, email, date, last, nb, type, hash)
     select u.user_id, m.expe, m.email, 0, 0, FIND_IN_SET('envoye', m.flags), IF(FIND_IN_SET('mail_perso', m.flags), 'user', 'staff'), ''
       from marketing     as m
 inner join auth_user_md5 as u on u.matricule = m.dest
      where date_ins = 0 and deces = 0
      group by user_id, m.email;

replace into register_marketing (uid, sender, email, date, last, nb, type, hash)
     select u.user_id, e.sender, e.email, date_envoi, date_envoi, 1, 'staff', e.uid
       from envoidirect   as e
 inner join auth_user_md5 as u using(matricule)
      where date_ins = 0 and deces = 0;

drop table envoidirect;
drop table marketing;/*}}}*/

insert into  register_pending (uid, forlife, bestalias, mailorg2, password, email, date, relance, naissance, hash)
     select  u.user_id, e.username,
             IF(e.loginbis, e.loginbis, REPLACE(REPLACE(e.username, '.19', ''), '.20', '')),
             IF(e.loginbis, REPLACE(REPLACE(e.username, '.19', ''), '.20', ''), NULL),
             e.password, email, e.date, relance, e.naissance, ins_id
       from  en_cours      AS e
 inner join  auth_user_md5 AS u USING( matricule )
      where  u.perms = 'pending';

drop table en_cours;
