ALTER TABLE groups ADD COLUMN disable_mails int(1) NOT NULL DEFAULT 1;
UPDATE groups SET disable_mails = 0;

-- vim:set syntax=mysql:
