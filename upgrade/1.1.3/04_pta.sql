CREATE TABLE IF NOT EXISTS profile_partnersharing_enum (
  id int(6) unsigned NOT NULL,
  api_uid int(11) unsigned NULL,
  shortname varchar(64) NOT NULL DEFAULT '',
  name varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  default_sharing_level enum('admin', 'private', 'ax', 'public', 'none') DEFAULT 'none',
  has_directory int(1) unsigned NOT NULL DEFAULT 0,
  has_bulkmail int(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  FOREIGN KEY (api_uid) REFERENCES accounts (uid) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO profile_partnersharing_enum
        SET shortname = 'pta', name = 'ParisTech Alumni', url = 'http://www.paristech-alumni.org', default_sharing_level = 'public', has_directory = 1, has_bulkmail = 1;

CREATE TABLE IF NOT EXISTS profile_partnersharing_settings (
  pid int(11) unsigned NOT NULL,
  partner_id int(6) unsigned NOT NULL,
  exposed_uid varchar(255) NOT NULL,
  sharing_level enum('admin', 'private', 'ax', 'public', 'none') DEFAULT 'none',
  allow_email enum('none', 'digest', 'direct') DEFAULT 'direct',
  last_connection datetime NULL,
  PRIMARY KEY (pid, partner_id),
  KEY (partner_id, exposed_uid),
  FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (partner_id) REFERENCES profile_partnersharing_enum (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS profile_photo_tokens (
  pid int(11) unsigned NOT NULL,
  token varchar(255) NOT NULL,
  expires datetime NOT NULL,
  PRIMARY KEY (pid),
  KEY (token),
  FOREIGN KEY (pid) REFERENCES profiles (pid) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
