ALTER TABLE profiles ADD COLUMN title ENUM('M', 'MLLE', 'MME') NOT NULL DEFAULT 'M';

-- vim:set syntax=mysql:
