ALTER TABLE group_auth
ADD COLUMN group_id SMALLINT(5) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE group_auth
ADD INDEX (group_id);
ALTER TABLE group_auth
ADD FOREIGN KEY (group_id) REFERENCES groups (id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE group_auth
ADD COLUMN flags SET('allow_xnet', 'group_only') DEFAULT '';

UPDATE group_auth SET flags = 'allow_xnet' WHERE name = 'Polytechnique.net';
