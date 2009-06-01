DROP TABLE IF EXISTS reminder_type;

CREATE TABLE IF NOT EXISTS reminder_type (
  type_id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  weight INT NOT NULL,
  remind_delay_yes INT NOT NULL DEFAULT 0,
  remind_delay_no INT NOT NULL DEFAULT 0,
  remind_delay_dismiss INT NOT NULL DEFAULT 0,
  PRIMARY KEY(type_id),
  UNIQUE KEY(name)
) CHARSET=utf8;

INSERT INTO  reminder_type (name, weight, remind_delay_yes, remind_delay_no, remind_delay_dismiss)
     VALUES  ('email_warning', 100, 0, 0,   7),
             ('no_redirection', 100, 0, 0,  1),
             ('profile_update', 90, 0, 0,   2),
             ('nl',             80, 0, 365, 7),
             ('promotion_ml',   70, 0, 365, 7),
             ('email_backup',   60, 0, 365, 7),
             ('gapps',          50, 0, 365, 7),
             ('ax_letter',      50, 0, 365, 14);

DROP TABLE IF EXISTS reminder;

CREATE TABLE IF NOT EXISTS reminder (
  uid INT NOT NULL,
  type_id INT NOT NULL,
  status ENUM('yes', 'no', 'dismissed') NOT NULL,
  remind_last TIMESTAMP NOT NULL,
  remind_next TIMESTAMP NULL,
  PRIMARY KEY(uid, type_id)
) CHARSET=utf8;

-- vim:set syntax=mysql:
