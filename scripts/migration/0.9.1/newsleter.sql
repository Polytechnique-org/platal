alter table newsletter change id id int not null;
alter table newsletter change `date` `date` date not null;
alter table newsletter add column bits enum('old','sent','new') default 'new' not null;
update newsletter set bits='old';

alter table newsletter_ins add column last int;
alter table newsletter_ins add index (last);

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

