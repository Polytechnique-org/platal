ALTER TABLE accounts ADD COLUMN from_email VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE accounts ADD COLUMN from_format ENUM('text','html') NOT NULL DEFAULT 'html';

-- vim:set syntax=mysql:
