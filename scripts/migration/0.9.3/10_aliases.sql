alter table aliases add column flags set('epouse','bestalias') not null after type;
update aliases set flags='epouse' where type='epouse';
update aliases set type='epouse' where type='epouse';
alter table aliases change column type type enum('a_vie','alias','homonyme','liste') not null;
alter table aliases add index (flags);
