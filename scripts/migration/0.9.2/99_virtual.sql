alter table virtual change column vid vid int not null auto_increment;
alter table virtual change column alias alias varchar(255) not null;
alter table virtual add unique (alias) ;
alter table virtual_redirect change column vid vid int not null auto_increment;
alter table virtual_redirect add index (redirect);

insert into virtual
    select null,domain,'user'
    from   groupex.aliases
    where  domain like '%@melix.net';

insert into  virtual_redirect
     select  vid,CONCAT(email,'@m4x.org')
       from  virtual         AS v
 inner join  groupex.aliases as d ON(v.alias=d.domain);

delete from groupex.aliases where domain like '%@melix.net';
