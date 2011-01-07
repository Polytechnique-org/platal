DROP TABLE IF EXISTS email_source_account;
DROP TABLE IF EXISTS email_source_other;
DROP TABLE IF EXISTS homonyms_list;
DROP TABLE IF EXISTS email_redirect_account;
DROP TABLE IF EXISTS email_redirect_other;
DROP TABLE IF EXISTS email_virtual;
DROP TABLE IF EXISTS email_virtual_domains;

CREATE TABLE email_source_account (
	email  VARCHAR(255) NOT NULL PRIMARY KEY,
	uid    INT(11) UNSIGNED NOT NULL,
	type   ENUM('forlife','alias') NOT NULL DEFAULT 'forlife',
	flags  SET('bestalias','usage','marital') NOT NULL DEFAULT '',
	expire DATE DEFAULT NULL,
	KEY (uid),
	KEY (type),
    FOREIGN KEY (uid) REFERENCES accounts (uid) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB,  CHARSET=utf8 ;

CREATE TABLE email_source_other (
	email VARCHAR(255) NOT NULL PRIMARY KEY,
	hrmid VARCHAR(255) NOT NULL,
	type  ENUM('homonym','ax','honeypot'),
	expire DATE NOT NULL DEFAULT '0000-00-00'
) ENGINE=InnoDB,  CHARSET=utf8 ;

CREATE TABLE homonyms_list (
	hrmid VARCHAR(255) NOT NULL,
	uid   INT(11) UNSIGNED NOT NULL,
	KEY(hrmid)
) ENGINE=InnoDB,  CHARSET=utf8 ;

CREATE TABLE email_redirect_account (
	uid           INT(11) UNSIGNED NOT NULL,
	redirect      VARCHAR(255) NOT NULL DEFAULT '',
	rewrite       VARCHAR(255) NOT NULL DEFAULT '',
	type          ENUM('smtp','googleapps','imap','homonym') NOT NULL DEFAULT 'smtp',
	action        ENUM('default',
	                   'drop_spams',
	                   'let_spams',
	                   'tag_and_drop_spams',
	                   'tag_spams',
	                   'imap_and_bounce',
	                   'homonym')
		NOT NULL DEFAULT 'default',
	broken        DATE NOT NULL DEFAULT '0000-00-00',
	broken_level  TINYINT(1) NOT NULL DEFAULT 0,
	last          DATE NOT NULL DEFAULT '0000-00-00',
	flags         ENUM('active','broken','disabled') NOT NULL,
	hash          VARCHAR(32) DEFAULT NULL,
	allow_rewrite TINYINT(1) DEFAULT 0,
	KEY (uid),
	KEY (redirect)
) ENGINE=InnoDB,  CHARSET=utf8 ;

CREATE TABLE email_redirect_other (
	hrmid         VARCHAR(255) NOT NULL DEFAULT '',
	redirect      VARCHAR(255) NOT NULL DEFAULT '',
	type          ENUM('smtp','googleapps','imap','homonym') NOT NULL DEFAULT 'smtp',
	action        ENUM('default',
	                   'drop_spams',
	                   'let_spams',
	                   'tag_and_drop_spams',
	                   'tag_spams',
	                   'imap_and_bounce',
	                   'homonym')
		NOT NULL DEFAULT 'default',
	KEY (hrmid),
	KEY (redirect)
) ENGINE=InnoDB,  CHARSET=utf8 ;

CREATE TABLE email_virtual (
	email    VARCHAR(255) NOT NULL,
	redirect VARCHAR(255) NOT NULL,
	type     ENUM('user','list','domain','event','admin','partner'),
	expire   DATE NOT NULL DEFAULT '0000-00-00',
	KEY (email)
) ENGINE=InnoDB,  CHARSET=utf8 ;

CREATE TABLE email_virtual_domains (
	domain VARCHAR(255) NOT NULL PRIMARY KEY
) ENGINE=InnoDB,  CHARSET=utf8;

-- vim:set syntax=mysql:
