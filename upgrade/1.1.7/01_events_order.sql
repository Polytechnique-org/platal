ALTER TABLE groups ADD COLUMN event_order SET('asc','desc') NOT NULL DEFAULT 'desc';

-- vim:set syntax=mysql:
