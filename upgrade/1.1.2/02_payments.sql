ALTER TABLE payments MODIFY COLUMN flags SET('unique', 'old', 'donation') NOT NULL DEFAULT '';

-- vim:set syntax=mysql:
