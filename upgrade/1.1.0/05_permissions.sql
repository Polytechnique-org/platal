ALTER TABLE accounts CHANGE COLUMN user_perms user_perms
    SET('groups', 'mail', 'directory_ax', 'directory_private', 'edit_directory',
        'forums', 'lists', 'payment', 'api_user_readonly') DEFAULT NULL;

ALTER TABLE account_types CHANGE COLUMN perms perms
    SET('groups', 'mail', 'directory_ax', 'directory_private', 'edit_directory',
        'forums', 'lists', 'payment', 'api_user_readonly') NOT NULL;

-- vim:set syntax=mysql:
