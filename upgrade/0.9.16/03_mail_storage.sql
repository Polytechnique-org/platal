ALTER TABLE auth_user_md5 ADD COLUMN mail_storage SET('imap', 'googleapps') DEFAULT 'imap' NOT NULL AFTER smtppass;

# vim:set syntax=mysql:
