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
