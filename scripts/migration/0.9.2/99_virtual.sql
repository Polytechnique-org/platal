--------------------------------------------------------------------------------
-- alters
--------------------------------------------------------------------------------
alter table virtual change column vid vid int not null auto_increment;
alter table virtual change column alias alias varchar(255) not null;
alter table virtual change column type type enum('user','list','dom') not null default 'user';
alter table virtual add unique (alias) ;
alter table virtual_redirect change column vid vid int not null auto_increment;
alter table virtual_redirect add index (redirect);

--------------------------------------------------------------------------------
-- copies melix.net
--------------------------------------------------------------------------------
insert into virtual
    select null,domain,'user'
    from   groupex.aliases
    where  domain like '%@melix.net';

insert into  virtual_redirect
     select  vid,CONCAT(email,'@m4x.org')
       from  virtual         AS v
 inner join  groupex.aliases as d ON(v.alias=d.domain);

delete from  groupex.aliases where domain like '%@melix.net';

--------------------------------------------------------------------------------
-- establish @melix.org -> @melix.net
--------------------------------------------------------------------------------
insert into  virtual (alias,type) VALUES('@melix.org','dom');
insert into  virtual_redirect
     select  vid,'@melix.net'
       from  virtual
      where  alias='@melix.org';

delete from  groupex.aliases where domain = '@melix.org';

