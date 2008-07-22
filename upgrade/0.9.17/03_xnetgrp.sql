use groupex;

alter table asso change column flags flags set('wiki_desc', 'notif_unsub') not null;

create table membres_sub_requests (
  asso_id smallint(5) unsigned not null,
  uid int(11) not null,
  ts timestamp not null default NOW(),
  reason text default null,

  primary key sub (asso_id, uid)
) charset=utf8;

use x4dat;

# vim:set syntax=mysql:
