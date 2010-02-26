# forums
  ALTER TABLE  forums
CHANGE COLUMN  nom name VARCHAR(64) NOT NULL;

  ALTER TABLE  forum_profiles
CHANGE COLUMN  nom name VARCHAR(64) NOT NULL,
   ADD COLUMN  last_seen TIMESTAMP NOT NULL DEFAULT '0000-00-00';

# payment
  ALTER TABLE  payments
CHANGE COLUMN  montant_def amount_def DECIMAL(10,2) NOT NULL DEFAULT 0.00,
CHANGE COLUMN  montant_min amount_min DECIMAL(10,2) NOT NULL DEFAULT 0.00,
CHANGE COLUMN  montant_max amount_max DECIMAL(10,2) NOT NULL DEFAULT 0.00;

  ALTER TABLE  payment_transactions
CHANGE COLUMN  montant amount VARCHAR(15) NOT NULL DEFAULT '0.00',
CHANGE COLUMN  cle pkey VARCHAR(5) NOT NULL;

# emails
  ALTER TABLE  aliases
CHANGE COLUMN  id uid INT(11) not null;
  ALTER TABLE  newsletter_ins
CHANGE COLUMN  user_id uid INT(11) not null;
  ALTER TABLE  axletter_ins
CHANGE COLUMN  user_id uid INT(11) not null;
  ALTER TABLE  axletter_rights
CHANGE COLUMN  user_id uid INT(11) not null;

# announces
  ALTER TABLE  announces
CHANGE COLUMN  user_id uid INT(11) not null;
  ALTER TABLE  announce_read
CHANGE COLUMN  user_id uid INT(11) not null;

# profile
  ALTER TABLE  profile_photos
CHANGE COLUMN  uid pid INT(11) not null;
  ALTER TABLE  profile_skills
CHANGE COLUMN  uid pid INT(11) not null;
  ALTER TABLE  profile_langskills
CHANGE COLUMN  uid pid INT(11) not null;
  ALTER TABLE  profile_binets
CHANGE COLUMN  user_id pid INT(11) not null;
  ALTER TABLE  profile_medals
CHANGE COLUMN  uid pid INT(11) not null;

# vim:set ft=mysql:
