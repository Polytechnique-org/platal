ALTER TABLE group_members ADD COLUMN flags SET('notify') NOT NULL DEFAULT '';
ALTER TABLE groups MODIFY COLUMN flags SET('wiki_desc','notif_unsub','has_ml', 'notify_all') NOT NULL DEFAULT 'notify_all';
UPDATE groups SET flags = CONCAT(flags, ',', 'notify_all') WHERE NOT FIND_IN_SET('notify_all', flags);

-- vim:set syntax=mysql:
