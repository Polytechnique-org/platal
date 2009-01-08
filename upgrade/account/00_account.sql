CREATE TABLE accounts (
  # Account identifier and type
  uid int(6) not null auto_increment,
  hruid varchar(255) not NULL,

  # Account type and state
  type varchar(16) default null,
  is_admin bool default false,
  state enum('pending', 'active', 'disabled') not null default 'pending',

  # Access
  password char(40) default null,
  token varchar(32) default null,
  weak_password varchar(256) default null,
  registration_date datetime not null,

  # Administrative tools
  flags set('watch') not null default '',
  comment varchar(255) default null,

  # User settings
  email varchar(255) default null,
  full_name varchar(255) default null,
  display_name varchar(255) default null,
  sex enum('female', 'male') not null default 'male',
  email_format enum('text', 'html') not null default 'html',
  skin varchar(32) default null,
  last_version varchar(16) not null,

  primary key uid (uid),
  unique key hruid (hruid),
  key full_name (full_name),
  key state (state),
  key type (type)
);

CREATE TABLE account_types (
  type varchar(16) not null,
  perms set('mail', 'groups', 'forums', 'list', 'search', 'portal') default '',

  primary key type (type)
);

CREATE TABLE account_profiles (
  uid int(6) not null,
  pid int(6) not null,
  perms set('owner') not null default '',

  primary key id (uid, pid),
  key uid (uid),
  key pid (pid)
);

# vim:set syntax=mysql:
