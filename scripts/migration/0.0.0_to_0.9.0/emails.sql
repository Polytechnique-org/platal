--------------------------------------------------------------------------------
-- emails
--------------------------------------------------------------------------------
update x4dat.emails set flags=CONCAT_WS(',',flags,'active') where find_in_set('filtre',flags);
alter table x4dat.emails add column rewrite CHAR(128) NOT NULL after email;
update x4dat.emails as e inner join x4dat.aliases as a ON(a.id=e.uid AND a.type='a_vie') set e.rewrite=CONCAT(a.alias,'@m4x.org') where find_in_set('m4x',flags);
update x4dat.emails as e inner join x4dat.aliases as a ON(a.id=e.uid AND a.type='a_vie') set e.rewrite=CONCAT(a.alias,'@polytechnique.org') where find_in_set('rewrite',flags) AND rewrite='';
alter table x4dat.emails change flags flags set('active','filter','mtic');
update x4dat.emails set flags='filter' WHERE num=0;
alter table x4dat.emails drop primary key;
alter table x4dat.emails add index uid (uid);
alter table x4dat.emails add primary key (uid,email);
alter table x4dat.emails drop column num;

