ALTER TABLE payments MODIFY COLUMN flags SET('unique', 'old', 'donation') NOT NULL DEFAULT '';
ALTER TABLE payment_transactions ADD COLUMN display BOOL NOT NULL DEFAULT FALSE;

-- vim:set syntax=mysql:
