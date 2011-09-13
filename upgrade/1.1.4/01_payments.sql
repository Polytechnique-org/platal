ALTER TABLE payments MODIFY COLUMN flags SET('unique', 'old') NOT NULL DEFAULT '';

-- vim:set syntax=mysql:
