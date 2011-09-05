ALTER TABLE group_auth
ADD COLUMN group_id SMALLINT(5) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE group_auth
ADD INDEX (group_id);
ALTER TABLE group_auth
ADD FOREIGN KEY (group_id) REFERENCES groups (id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE group_auth
ADD COLUMN group_strict BOOL DEFAULT FALSE COMMENT 'Whether to only accept member of the groups';
