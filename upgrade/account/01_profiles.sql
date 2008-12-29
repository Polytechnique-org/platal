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
  deathdate date default null,

  primary key pid (pid),
  unique key hrpid (hrpid),
  unique key xorg_id (xorg_id),
  key ax_id (ax_id)
);

# vim:set syntax=mysql:
