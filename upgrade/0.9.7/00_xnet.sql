ALTER TABLE groupex.asso ADD `pub` ENUM( 'public', 'private' ) DEFAULT 'public' NOT NULL AFTER `ax` ;
ALTER TABLE evenements_participants ADD PRIMARY KEY (eid, item_id, uid);
ALTER TABLE evenements ADD `show_participants` TINYINT( 1 ) NOT NULL ;

