alter table newsletter change id id int not null;
alter table newsletter change `date` `date` date not null;
alter table newsletter add column bits enum('old','sent','new') default 'new' not null;

alter table newsletter_ins add column last int;
alter table newsletter_ins add index (last);
