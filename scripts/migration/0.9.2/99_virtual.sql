alter table virtual change column vid vid int not null auto_increment;
alter table virtual change column alias alias varchar(255) not null;
alter table virtual add unique (alias) ;
alter table virtual_redirect change column vid vid int not null auto_increment;
alter table virtual_redirect add index (redirect);
