ALTER TABLE account_types
CHANGE perms perms SET('groups','mail','directory_ax','directory_private','edit_directory','forums','lists','payment','api_user_readonly','gapps', 'user') NOT NULL DEFAULT '';

UPDATE account_types SET perms = CONCAT(perms, ',user') WHERE type IN ('ax', 'fx', 'master', 'phd', 'pi', 'school', 'x');
