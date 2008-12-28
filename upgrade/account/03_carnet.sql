create table watch (
  uid   int(6) not null auto_increment,
  flags set('contacts', 'mail') not null default 'contacts',
  last  timestamp not null default '0000-00-00',

  primary key uid (uid),
  key flags (flags)
);

# vim:set syntax=mysql:
