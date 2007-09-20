create table email_send_save (
  uid smallint(5) unsigned not null,
  data blob not null,

  primary key uid (uid)
) CHARSET=utf8;

# vim:set syntax=mysql:
