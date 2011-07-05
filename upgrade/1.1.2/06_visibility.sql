ALTER TABLE profile_addresses CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profile_corps CHANGE COLUMN corps_pub corps_pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profile_job CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profile_job CHANGE COLUMN email_pub email_pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profile_networking CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profile_photos CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profile_phones CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profiles CHANGE COLUMN freetext_pub freetext_pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profiles CHANGE COLUMN medals_pub medals_pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';
ALTER TABLE profiles CHANGE COLUMN alias_pub alias_pub ENUM('hidden', 'private', 'ax', 'public') NOT NULL DEFAULT 'private';

DROP TABLE IF EXISTS profile_visibility_enum;
CREATE TABLE profile_visibility_enum (
  access_level ENUM('admin', 'private', 'ax', 'public', 'none'),
  best_display_level ENUM('hidden', 'private', 'ax', 'public') NULL,
  display_levels SET('hidden', 'private', 'ax', 'public')
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO profile_visibility_enum (access_level, best_display_level, display_levels) VALUES
  ('admin', 'hidden', 'hidden,private,ax,public'),
  ('private', 'private', 'private,ax,public'),
  ('ax', 'ax', 'ax,public'),
  ('public', 'public', 'public'),
  ('none', NULL, '');
