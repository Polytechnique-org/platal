--
-- PERMS
--
alter table auth_user_md5 change column perms perms enum('user','admin','non-inscrit','pending','disabled');
update auth_user_md5 set perms='pending' where perms='non-inscrit';
alter table auth_user_md5 change column perms perms enum('user','admin','pending','disabled');
alter table auth_user_md5 change column comment comment varchar(64) not null;

--
-- be naissance live as a date
--

alter table auth_user_md5 add column foo int;
update auth_user_md5 set foo=naissance ;
alter table auth_user_md5 change column naissance naissance date not null;
update auth_user_md5 set naissance = concat(foo % 10000, '-', (foo / 10000) % 100, '-', foo / 1000000);
alter table auth_user_md5 drop column foo;

alter table en_cours add column foo int;
update en_cours set foo=naissance ;
alter table en_cours change column naissance naissance date not null;
update en_cours set naissance = concat(foo % 10000, '-', (foo / 10000) % 100, '-', foo / 1000000);
alter table en_cours drop column foo;

--
-- webredirect
--

alter table auth_user_quick add column redirecturl varchar(255) not null;
replace into auth_user_quick (user_id,lastnewslogin,skin,watch_flags,watch_last,redirecturl)
      select q.user_id,q.lastnewslogin,q.skin,q.watch_flags,q.watch_last,u.redirecturl
        from auth_user_md5   as u
  inner join auth_user_quick as q USING(user_id)
       where u.redirecturl!='';
alter table auth_user_md5 drop column redirecturl;
