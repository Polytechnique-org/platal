alter table evenements change flags flags set('valide', 'archive', 'important') not null;
alter table evenements add post_id smallint(4) unsigned default NULL;
create table evenements_photo (
    eid smallint(4) unsigned not null,
    attachmime enum('jpeg', 'png', 'gif') not null default 'jpeg',
    attach blob not null,
    x smallint(5) unsigned not null default 0,
    y smallint(5) unsigned not null default 0,
    primary key eid (eid)
) charset=utf8;

use groupex;
alter table announces add post_id smallint(4) unsigned default NULL;
use x4dat;

# vim:set syntax=mysql:
