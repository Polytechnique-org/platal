-- This SQL table comes from the 'gapps-daemon' project.

-- Table `gapps_nicknames`
-- Holds the list of all nicknames registered in the Google Apps domain.
CREATE TABLE IF NOT EXISTS `gapps_nicknames` (
  -- Application-specific field.
  l_userid INT DEFAULT NULL,

  -- Shared fields.
  g_account_name VARCHAR(256) NOT NULL,
  g_nickname VARCHAR(256) NOT NULL,
  -- Indexes.
  PRIMARY KEY(g_nickname),
  INDEX g_account_name(g_account_name),
  INDEX l_userid(l_userid)
) CHARSET=utf8;

-- Add new task types.
ALTER TABLE gapps_queue MODIFY j_type ENUM('n_create', 'n_delete', 'n_resync', 'r_activity', 'r_accounts', 'u_create', 'u_delete', 'u_update', 'u_sync') NOT NULL;

-- vim:set syntax=mysql:
