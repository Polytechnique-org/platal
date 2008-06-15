use groupex;

alter table asso change column flags flags set('wiki_desc', 'notif_unsub') not null;

use x4dat;

# vim:set syntax=mysql:
