
CREATE TABLE newsletter_ins (
  user_id smallint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (user_id)
) TYPE=MyISAM COMMENT='liste des abonnés à la newsletter';
