-- Adds a new "domain" field to the primary Google Apps table, to allow users
-- to be created accross multiple organizations (Google Apps's name for multiple
-- domains with each their own users, all tied to the same primary domain).
ALTER TABLE  gapps_accounts
 ADD COLUMN  g_domain VARCHAR(40) DEFAULT NULL AFTER g_account_name;

-- vim:set syntax=mysql:
