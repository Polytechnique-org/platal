-- This SQL table comes from the 'gapps-daemon' project.

-- Table `gapps_reporting`.
-- The table contains daily statistics of the Google Apps domain. They are
-- obtained with the Summary and Actvity reports of the Google Apps Reorting
-- API.
CREATE TABLE IF NOT EXISTS `gapps_reporting` (
  date DATE NOT NULL,
  num_accounts INTEGER  UNSIGNED DEFAULT NULL,
  count_1_day_actives INTEGER UNSIGNED DEFAULT NULL,
  count_7_day_actives INTEGER UNSIGNED DEFAULT NULL,
  count_14_day_actives INTEGER UNSIGNED DEFAULT NULL,
  count_30_day_actives INTEGER UNSIGNED DEFAULT NULL,
  count_30_day_idle INTEGER UNSIGNED DEFAULT NULL,
  count_60_day_idle INTEGER UNSIGNED DEFAULT NULL,
  count_90_day_idle INTEGER UNSIGNED DEFAULT NULL,
  usage_in_bytes BIGINT UNSIGNED DEFAULT NULL,
  quota_in_mb INTEGER  UNSIGNED DEFAULT NULL,
  PRIMARY KEY(date)
) CHARSET=utf8;

-- Table `gapps_accounts`.
-- Holds the Google Apps account list, ie. a list of all registered accounts on
-- the Google Apps domain.
CREATE TABLE IF NOT EXISTS `gapps_accounts` (
  -- Application-specific fields.
  l_userid SMALLINT UNSIGNED DEFAULT NULL,
  l_sync_password BOOL DEFAULT TRUE,
  l_activate_mail_redirection BOOL DEFAULT TRUE,

  -- Shared fields.
  g_account_id CHAR(16) DEFAULT NULL,
  g_account_name VARCHAR(256) NOT NULL,
  g_first_name VARCHAR(40) NOT NULL,
  g_last_name VARCHAR(40) NOT NULL,
  g_status ENUM('unprovisioned', 'disabled', 'active') DEFAULT 'unprovisioned',
  g_admin BOOL DEFAULT NULL,
  g_suspension VARCHAR(256) DEFAULT NULL,

  -- Google-owned fields.
  r_disk_usage BIGINT DEFAULT NULL,
  r_creation DATE DEFAULT NULL,
  r_last_login DATE DEFAULT NULL,
  r_last_webmail DATE DEFAULT NULL,

  -- Indexes.
  PRIMARY KEY(g_account_name),
  INDEX l_userid(l_userid)
) CHARSET=utf8;

-- Table `gapps_queue`.
-- Holds queued API requests that are to be processed by the gappsd. It also
-- includes processed but not yet acknowledged requests.
CREATE TABLE IF NOT EXISTS `gapps_queue` (
  -- Queue identification fields.
  q_id INT NOT NULL AUTO_INCREMENT,
  q_owner_id SMALLINT DEFAULT NULL,
  q_recipient_id SMALLINT DEFAULT NULL,

  -- Queue management information.
  p_entry_date DATETIME NOT NULL,
  p_notbefore_date DATETIME NOT NULL,
  p_start_date DATETIME DEFAULT NULL,
  p_end_date DATETIME DEFAULT NULL,
  p_status ENUM('idle', 'active', 'success', 'hardfail', 'softfail') DEFAULT 'idle' NOT NULL,
  p_priority ENUM('immediate', 'normal', 'offline') DEFAULT 'offline' NOT NULL,
  p_admin_request BOOLEAN DEFAULT false NOT NULL,

  -- Job content fields.
  j_type ENUM('r_activity', 'r_accounts', 'u_create', 'u_delete', 'u_update', 'u_sync') NOT NULL,
  j_parameters TEXT DEFAULT NULL,

  -- Job execution result fields.
  r_softfail_date DATETIME DEFAULT NULL,
  r_softfail_count SMALLINT DEFAULT 0 NOT NULL,
  r_result VARCHAR(256) DEFAULT NULL,

  -- Indexes.
  PRIMARY KEY(q_id),
  INDEX q_owner_id(q_owner_id),
  INDEX q_recipient_id(q_recipient_id),
  INDEX p_status(p_status),
  INDEX p_priority(p_priority)
) CHARSET=utf8;

-- vim:set syntax=mysql:
