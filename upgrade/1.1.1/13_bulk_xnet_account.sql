ALTER TABLE register_pending_xnet ADD COLUMN sender_name VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE register_pending_xnet ADD COLUMN group_name VARCHAR(255) NOT NULL DEFAULT '';

-- vim:set syntax=mysql:
