create table watch_profile (
  uid int(5) unsigned not null,
  ts timestamp not null default CURRENT_TIMESTAMP,
  field enum('nom', 'freetext', 'mobile', 'nationalite', 'nick', 'web', 'appli1', 'appli2',
             'addresses', 'section', 'binets', 'medals', 'cv', 'jobs', 'photo'),

  primary key field (uid, field)
) charset=utf8;

# vim:set syntax=mysql:
