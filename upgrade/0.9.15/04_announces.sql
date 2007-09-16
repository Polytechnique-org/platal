alter table evenements change flags flags set('valide','archive','important','wiki') not null;
use groupex
alter table asso add column flags set('wiki_desc') not null;
use x4dat

# vim:set syntax=mysql:
