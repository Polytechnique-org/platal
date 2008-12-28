create table email_options (
  uid int(6) not null,
  storage set('imap', 'googleapps') not null default '',

  primary key uid (uid)
);

# vim:set syntax=mysql:
