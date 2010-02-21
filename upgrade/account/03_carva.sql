drop table if exists carvas;
create table carvas (
  uid int(6) not null,
  url varchar(255) not null,

  primary key uid (uid)
) ENGINE=InnoDB, CHARSET=utf8;

# vim:set syntax=mysql:
