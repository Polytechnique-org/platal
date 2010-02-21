drop table if exists profiles;
create table profiles (
  # Profile identifiers
  pid int(6) not null auto_increment,
  hrpid varchar(255) not null,

  # Who is this?
  xorg_id int(8) not null,
  ax_id varchar(8) default null,

  # Some singletons
  # birthdate and birthdate given by reference directory (library, school...)
  birthdate date default null,
  birthdate_ref date default null,
  next_birthday date default null,
  deathdate date default null,
  deathdate_rec date default null,

  sex enum('female', 'male') not null default 'male',
  section tinyint(2) unsigned default null,
  cv text default null,
  freetext mediumtext default null,
  freetext_pub enum('private', 'public') not null default 'private',
  medals_pub enum('private', 'public') not null default 'private',
  alias_pub enum('private', 'public') not null default 'private',

  nationality1 char(2) default null,
  nationality2 char(2) default null,
  nationality3 char(2) default null,

  # Last modification date (for notifications)
  last_change date not null,

  primary key pid (pid),
  unique key hrpid (hrpid),
  unique key xorg_id (xorg_id),
  key ax_id (ax_id),
  key nationality1 (nationality1),
  key nationality2 (nationality2),
  key nationality3 (nationality3)
) ENGINE=InnoDB, CHARSET=utf8;

# vim:set syntax=mysql:
