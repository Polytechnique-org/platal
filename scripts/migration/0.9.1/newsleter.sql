-- -----------------------------------------------------------------------------
-- update old table
--
alter table newsletter change `date` `date` date not null;
alter table newsletter add column head mediumtext default '' not null;
alter table newsletter add column bits enum('old','sent','new') default 'new' not null;
update newsletter set bits='old';

alter table newsletter_ins add column last int not null default 0;
alter table newsletter_ins add index (last);
--
-- -----------------------------------------------------------------------------
-- newsletter categories
--
create table newsletter_cat (
    cid tinyint unsigned not null auto_increment,
    pos tinyint unsigned not null,
    titre varchar(128) not null,
    PRIMARY KEY (cid),
    INDEX (pos)
);
insert into newsletter_cat values(1,1,'Flash Info');
insert into newsletter_cat values(2,2,'Communications institutionnelles');
insert into newsletter_cat values(3,3,'Groupes X');
insert into newsletter_cat values(4,4,'Activités polytechniciennes');
--
-- -----------------------------------------------------------------------------
-- newsletter articles
--
create table newsletter_art (
    id int unsigned not null,
    aid smallint unsigned not null,
    cid smallint unsigned not null,
    pos tinyint unsigned not null,
    title mediumtext not null,
    body mediumtext not null,
    append mediumtext not null,
    PRIMARY KEY (id,aid)
);
--
-- -----------------------------------------------------------------------------
