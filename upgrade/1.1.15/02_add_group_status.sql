-- A group can be active, inactive (still appear in the list of groups) or dead (put in another page)
-- The order matters, as it is used to order groups
ALTER TABLE groups ADD COLUMN status SET('active', 'inactive', 'dead') NOT NULL DEFAULT 'active';

-- vim:set syntax=mysql:
