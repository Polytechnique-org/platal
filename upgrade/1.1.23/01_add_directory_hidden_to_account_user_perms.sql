ALTER TABLE accounts CHANGE COLUMN user_perms user_perms
    SET('groups', 'mail', 'directory_ax', 'directory_private', 'directory_hidden',
        'edit_directory', 'forums', 'lists', 'payment', 'api_user_readonly', 'gapps', 'user') NOT NULL;

-- vim:set syntax=mysql:
