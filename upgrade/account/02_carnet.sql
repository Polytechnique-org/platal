drop table if exists watch;
create table watch (
  uid   int(6) not null,
  flags set('contacts', 'mail') not null default 'contacts',
  actions set('profile', 'registration', 'death', 'birthday') not null default '',
  last  timestamp not null default '0000-00-00',

  primary key uid (uid),
  key flags (flags)
) ENGINE=InnoDB, CHARSET=utf8;

# vim:set syntax=mysql:
