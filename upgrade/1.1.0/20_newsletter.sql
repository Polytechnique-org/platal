DROP TABLE IF EXISTS newsletter_issues;
DROP TABLE IF EXISTS newsletters;

-----------------
-- newsletters --
-----------------

CREATE TABLE newsletters (
  id int(11) unsigned NOT NULL auto_increment,
  group_id smallint(5) UNSIGNED NOT NULL,
  name varchar(255) NOT NULL,
  custom_css BOOL NOT NULL DEFAULT FALSE,
  criteria SET('axid', 'promo', 'geo') DEFAUL NULL,
  PRIMARY KEY (id),
  UNIQUE KEY  (group_id),
  FOREIGN KEY (group_id) REFERENCES groups (id)
                         ON UPDATE CASCADE
                         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lists all newsletters';

-- Filling it with default values for X.org / AX / Ecole
INSERT INTO newsletters (group_id, name, custom_css)
      ( SELECT  groups.id, CONCAT("Lettre de ", groups.nom), TRUE
          FROM  groups
         WHERE  groups.diminutif IN ('Polytechnique.org', 'Ecole', 'AX')
      );

-- Set variables for simpler queries later on
SET @idnl_xorg = (SELECT nls.id FROM newsletters AS nls LEFT JOIN groups AS g ON (g.id = nls.group_id) WHERE g.diminutif = 'Polytechnique.org');
SET @idnl_ax = (SELECT nls.id FROM newsletters AS nls LEFT JOIN groups AS g ON (g.id = nls.group_id) WHERE g.diminutif = 'AX');
SET @idnl_ecole = (SELECT nls.id FROM newsletters AS nls LEFT JOIN groups AS g ON (g.id = nls.group_id) WHERE g.diminutif = 'Ecole');

UPDATE newsletters SET name = "Lettre de l'AX", criteria = 'promo,axid' WHERE id = @idnl_ax;
UPDATE newsletters SET name = "Lettre mensuelle de Polytechnique.org", criteria = 'promo' WHERE id = @idnl_xorg;
UPDATE newsletters SET name = "DiXit, lettre de l'École polytechnique", criteria = 'promo' WHERE id = @idnl_ecole;

-----------------------
-- newsletter_issues --
-----------------------

CREATE TABLE newsletter_issues (
  nlid int(11) unsigned NOT NULL,
  id int(11) unsigned NOT NULL auto_increment,
  date date NOT NULL default '0000-00-00',
  send_before date default NULL,
  state enum('sent','new','pending') NOT NULL default 'new',
  sufb_json text default NULL,
  title varchar(255) NOT NULL default '',
  head mediumtext NOT NULL,
  signature mediumtext NOT NULL,
  short_name varchar(16) default NULL,
  mail_title varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  UNIQUE KEY  (nlid, short_name),
  FOREIGN KEY (nlid) REFERENCES newsletters (id)
                     ON UPDATE CASCADE
                     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lists issues of all newsletters';

-- Fill with all X.org nls
INSERT INTO newsletter_issues (nlid, date, title, state, head, short_name, mail_title)
        (
          SELECT  @idnl_xorg, n.date, n.titre, n.bits, n.head, n.short_name, n.titre_mail
            FROM  newsletter AS n
        );

-- Fill with all AX nls
INSERT INTO newsletter_issues (nlid, date, title, state, head, signature, short_name, mail_title)
        (
          SELECT  @idnl_ax, a.date, a.title, a.bits, CONCAT("<cher> <prenom>,\n\n", a.body), a.signature, a.short_name, a.subject
            FROM  axletter AS a
           WHERE  bits != 'invalid'
        );

--------------------
-- newsletter_cat --
--------------------

-- Fix newsletter_cat: add nlid, add FK, rename title
ALTER TABLE newsletter_cat ADD COLUMN nlid INT(11) UNSIGNED NOT NULL AFTER cid;

UPDATE newsletter_cat SET nlid = @idnl_xorg;

ALTER TABLE newsletter_cat ADD FOREIGN KEY (nlid) REFERENCES newsletters (id)
                                                  ON UPDATE CASCADE
                                                  ON DELETE CASCADE;
ALTER TABLE newsletter_cat CHANGE titre title varchar(128) NOT NULL DEFAULT '';

-- Final state:
--
-- CREATE TABLE `newsletter_cat` (
--   `cid` tinyint(3) unsigned NOT NULL auto_increment,
--   `nlid` int(11) unsigned NOT NULL,
--   `pos` tinyint(3) unsigned NOT NULL default '0',
--   `title` varchar(128) NOT NULL default '',
--   PRIMARY KEY  (`cid`),
--   KEY `pos` (`pos`),
--   KEY `nlid` (`nlid`),
--   CONSTRAINT `newsletter_cat_ibfk_1` FOREIGN KEY (`nlid`) REFERENCES `newsletters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
-- ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8


--------------------
-- newsletter_art --
--------------------

-- Fix newsletter_cat: add nlid, add FK, rename title
ALTER TABLE newsletter_art DROP FOREIGN KEY newsletter_art_ibfk_1;
   UPDATE  newsletter_art AS na
LEFT JOIN  newsletter AS n ON na.id = n.id
LEFT JOIN  newsletter_issues AS ni ON (ni.nlid = @idnl_xorg AND ni.short_name = n.short_name)
      SET  na.id = ni.id;

ALTER TABLE newsletter_art ADD FOREIGN KEY (id) REFERENCES newsletter_issues (id)
                                                ON UPDATE CASCADE
                                                ON DELETE CASCADE

--------------------
-- newsletter_ins --
--------------------

-- Fix newsletter_ins: add nlid column, fix FK
ALTER TABLE newsletter_ins ADD COLUMN nlid INT(11) UNSIGNED NOT NULL AFTER uid;

UPDATE  newsletter_ins SET nlid = @idnl_xorg;

-- We have to drop all FKs in order to update 'last' indexes.
ALTER TABLE newsletter_ins DROP FOREIGN KEY newsletter_ins_ibfk_1;
ALTER TABLE newsletter_ins DROP FOREIGN KEY newsletter_ins_ibfk_2;
ALTER TABLE newsletter_ins DROP PRIMARY KEY;

   UPDATE  newsletter_ins AS ni
LEFT JOIN  newsletter AS n ON (ni.last = n.id)
LEFT JOIN  newsletter_issues AS ns ON (n.short_name = ns.short_name)
      SET  ni.last = ns.id;

ALTER TABLE newsletter_ins ADD PRIMARY KEY (uid, nlid);
ALTER TABLE newsletter_ins ADD FOREIGN KEY (uid) REFERENCES accounts (uid)
                                                  ON UPDATE CASCADE
                                                  ON DELETE CASCADE;
ALTER TABLE newsletter_ins ADD FOREIGN KEY (last) REFERENCES newsletter_issues (id)
                                                  ON UPDATE CASCADE
                                                  ON DELETE CASCADE;
ALTER TABLE newsletter_ins ADD FOREIGN KEY (nlid) REFERENCES newsletters (id)
                                                  ON UPDATE CASCADE
                                                  ON DELETE CASCADE;

-- Add AXletter subscribers.
INSERT INTO newsletter_ins  (nlid, uid, last, hash)
        (
          SELECT  @idnl_ax, ai.uid, MAX(ni.id), ai.hash
            FROM  axletter_ins AS ai
       LEFT JOIN  axletter AS a ON (ai.last = a.id)
       LEFT JOIN  newsletter_issues AS ni ON (ni.nlid = @idnl_ax AND ni.short_name = a.short_name)
        GROUP BY  ai.uid
    );

-- Final state:
--
-- CREATE TABLE `newsletter_ins` (
--   `uid` int(11) unsigned NOT NULL default '0',
--   `nlid` int(11) unsigned NOT NULL,
--   `last` int(11) unsigned default NULL,
--   `hash` varchar(32) default NULL,
--   PRIMARY KEY  (`uid`,`nlid`),
--   KEY `last` (`last`),
--   KEY `nlid` (`nlid`),
--   CONSTRAINT `newsletter_ins_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `accounts` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
--   CONSTRAINT `newsletter_ins_ibfk_2` FOREIGN KEY (`last`) REFERENCES `newsletter_issues` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
--   CONSTRAINT `newsletter_ins_ibfk_3` FOREIGN KEY (`nlid`) REFERENCES `newsletters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='liste des abonnés à la newsletter'
