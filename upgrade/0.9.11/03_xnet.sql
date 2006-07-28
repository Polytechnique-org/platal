use groupex
alter table evenements drop column advertise;
alter table evenements drop column membres_only;
alter table membres add column sexe bool default null;
use x4dat

