-- Switches to SHA1 password storage for register_pending.
ALTER TABLE register_pending CHANGE COLUMN password password VARCHAR(40) NOT NULL;

-- Disables MD5(""), SHA1("") and LEFT(SHA1(""), 32) as password.
UPDATE register_pending SET password = "" WHERE password IN ("d41d8cd98f00b204e9800998ecf8427e", "da39a3ee5e6b4b0d3255bfef95601890afd80709", "da39a3ee5e6b4b0d3255bfef95601890");
UPDATE auth_user_md5 SET password = "" WHERE password IN ("d41d8cd98f00b204e9800998ecf8427e", "da39a3ee5e6b4b0d3255bfef95601890afd80709", "da39a3ee5e6b4b0d3255bfef95601890");

-- vim:set syntax=mysql:
