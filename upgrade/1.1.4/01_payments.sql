ALTER TABLE payments MODIFY COLUMN flags SET('unique', 'old') NOT NULL DEFAULT '';
ALTER TABLE payments MODIFY COLUMN flags SET('unique', 'old', 'public') NOT NULL DEFAULT '';

-- vim:set syntax=mysql:
