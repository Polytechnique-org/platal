ALTER TABLE account_types CHANGE COLUMN perms perms
    SET('groups', 'mail', 'directory_ax', 'directory_private', 'directory_hidden',
        'edit_directory', 'forums', 'lists', 'payment', 'api_user_readonly', 'gapps', 'user') NOT NULL;

-- AX needs directory_ax to access some pages and directory_hidden for admin visibility level
UPDATE account_types SET perms = 'groups,directory_ax,directory_hidden,edit_directory,user' WHERE type = 'ax';

-- vim:set syntax=mysql:
