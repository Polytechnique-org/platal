USE logger;

ALTER TABLE sessions
ADD COLUMN ip2 INT(11) UNSIGNED NOT NULL,
ADD COLUMN forward_ip2 INT(11) UNSIGNED DEFAULT NULL;

UPDATE sessions
   SET ip2 = SUBSTRING_INDEX(ip, ".", 1) * 256 * 256 * 256
           + SUBSTRING_INDEX(SUBSTRING_INDEX(ip, ".", 2), ".", -1) * 256 * 256
           + SUBSTRING_INDEX(SUBSTRING_INDEX(ip, ".", 3), ".", -1) * 256
           + SUBSTRING_INDEX(ip, ".", -1),
       forward_ip2 = SUBSTRING_INDEX(forward_ip, ".", 1) * 256 * 256 * 256
                   + SUBSTRING_INDEX(SUBSTRING_INDEX(forward_ip, ".", 2), ".", -1) * 256 * 256
                   + SUBSTRING_INDEX(SUBSTRING_INDEX(forward_ip, ".", 3), ".", -1) * 256
                   + SUBSTRING_INDEX(forward_ip, ".", -1);

ALTER TABLE sessions
DROP COLUMN ip,
DROP COLUMN forward_ip,
CHANGE COLUMN ip2 ip INT(11) UNSIGNED NOT NULL,
CHANGE COLUMN forward_ip2 forward_ip INT(11) UNSIGNED DEFAULT NULL,
ADD INDEX ip (ip),
ADD INDEX forward_ip (forward_ip);

USE x4dat;

ALTER TABLE ip_watch
ADD COLUMN ip2 INT(11) UNSIGNED NOT NULL;

UPDATE ip_watch
   SET ip2 = SUBSTRING_INDEX(ip, ".", 1) * 256 * 256 * 256
           + SUBSTRING_INDEX(SUBSTRING_INDEX(ip, ".", 2), ".", -1) * 256 * 256
           + SUBSTRING_INDEX(SUBSTRING_INDEX(ip, ".", 3), ".", -1) * 256
           + SUBSTRING_INDEX(ip, ".", -1);

ALTER TABLE ip_watch
DROP PRIMARY KEY,
DROP COLUMN ip,
CHANGE COLUMN ip2 ip INT(11) UNSIGNED NOT NULL,
ADD COLUMN mask int(11) unsigned NOT NULL DEFAULT 4294967295,
ADD PRIMARY KEY ip (ip),
ADD INDEX network (ip, mask);

# vim:set syntax=mysql:
