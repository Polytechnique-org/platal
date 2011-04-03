ALTER TABLE account_types MODIFY COLUMN perms SET('groups','mail','directory_ax','directory_private','edit_directory','forums','lists','payment','api_user_readonly','gapps') NOT NULL DEFAULT '';

UPDATE  account_types
   SET  perms = 'groups,mail,directory_private,forums,lists,payment,gapps'
 WHERE  type = 'x';
UPDATE  account_types
   SET  perms = 'groups,mail,directory_private,forums,lists,payment'
 WHERE  type = 'master' OR type = 'phd';

-- vim:set syntax=mysql:
