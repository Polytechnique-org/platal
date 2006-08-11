alter table skins change date date date not null default 0; 
INSERT INTO skins (id, name, date, comment, auteur, skin_tpl, ext)
     VALUES (13, 'Keynote', '2006-08-09', 'Skin inspirée d\'un thème de Keynote',
             'Florent Bruneau', 'keynote.tpl', 'png');
