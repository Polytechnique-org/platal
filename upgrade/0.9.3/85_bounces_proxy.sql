create table emails_bounces_re (
    id   int not null auto_increment,
    pos  smallint unsigned not null default 0,
    lvl  tinyint unsigned not null default 0,
    re   text not null,
    text varchar(255) not null,
    primary key (id),
    index (lvl),
    index (pos)
);

insert into admin_a values(1,'Regexps Bounces', 'admin/emails_bounces_re.php', 30);
