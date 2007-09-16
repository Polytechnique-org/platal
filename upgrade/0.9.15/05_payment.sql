use groupex;
alter table evenements_participants add column flags set('notify_payment') not null after nb;
use x4dat;
# vim:set syntax=mysql:
