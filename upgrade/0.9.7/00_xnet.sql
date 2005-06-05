ALTER TABLE groupex.asso ADD `pub` ENUM( 'public', 'private' ) DEFAULT 'public' NOT NULL AFTER `ax` ;
ALTER TABLE groupex.evenements_participants ADD PRIMARY KEY (eid, item_id, uid);
ALTER TABLE groupex.evenements ADD `show_participants` TINYINT( 1 ) NOT NULL ;
ALTER TABLE groupex.evenements_participants ADD paid FLOAT DEFAULT 0 NOT NULL;

