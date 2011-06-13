ALTER TABLE profile_addresses CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public', 'none') NOT NULL DEFAULT 'private';
ALTER TABLE profile_corps CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public', 'none') NOT NULL DEFAULT 'private';
ALTER TABLE profile_job CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public', 'none') NOT NULL DEFAULT 'private';
ALTER TABLE profile_job CHANGE COLUMN email_pub email_pub ENUM('hidden', 'private', 'ax', 'public', 'none') NOT NULL DEFAULT 'private';
ALTER TABLE profile_phone CHANGE COLUMN pub pub ENUM('hidden', 'private', 'ax', 'public', 'none') NOT NULL DEFAULT 'private';
