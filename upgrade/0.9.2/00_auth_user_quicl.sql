create table auth_user_quick (
    user_id smallint not null,
    lastnewslogin timestamp not null,
    skin tinyint not null,
    primary key(user_id)
);

insert into  auth_user_quick (
     select  user_id,lastnewslogin,skin
       from  auth_user_md5
);

alter table auth_user_md5 drop column lastnewslogin;
alter table auth_user_md5 drop column skin;
