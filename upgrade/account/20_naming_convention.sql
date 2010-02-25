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

# profile
  ALTER TABLE  profile_photos
CHANGE COLUMN  uid pid INT(6) not null;
  ALTER TABLE  profile_skills
CHANGE COLUMN  uid pid INT(11) not null;
  ALTER TABLE  profile_langskills
CHANGE COLUMN  uid pid INT(11) not null;
  ALTER TABLE  profile_binets
CHANGE COLUMN  user_id pid INT(11) not null;
  ALTER TABLE  profile_medals
CHANGE COLUMN  uid pid INT(11) not null;

# vim:set ft=mysql:
