alter table newsletter_ins add column last int not null default 0;
alter table newsletter_ins add column pref enum('text','html') not null default 'html';
alter table newsletter_ins add index (last);
