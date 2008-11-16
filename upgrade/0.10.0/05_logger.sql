use logger;

create table last_sessions (
  uid int(6) not null,
  id int(6) not null,
  primary key uid (uid)
);

insert into last_sessions
     select s.uid AS uid, MAX(s.id) AS id
       from logger.sessions as s
      where s.suid = 0
   GROUP BY s.uid;

use x4dat;

# vim:set syntax=mysql:
