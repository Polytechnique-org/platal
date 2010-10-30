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
) ENGINE=InnoDB,  CHARSET=utf8 ;


INSERT INTO email_source_account (uid,email,type,flags,expire)
	SELECT a.uid,CONCAT(a.alias,'@polytechnique.org'),IF(a.type='a_vie','forlife','alias'),REPLACE(a.flags,'epouse','marital'),a.expire
	FROM aliases AS a
	WHERE a.type = 'a_vie'
	 OR a.type = 'alias';
INSERT INTO email_source_account (uid,email,type,flags,expire)
	SELECT a.uid,CONCAT(a.alias,'@m4x.org'),IF(a.type='a_vie','forlife','alias'),REPLACE(a.flags,'epouse','marital'),a.expire
	FROM aliases AS a
	WHERE a.type = 'a_vie'
	 OR a.type = 'alias';
INSERT INTO email_source_account (uid,email,type,flags,expire)
	SELECT a.uid,CONCAT(a.alias,'@m4x.net'),IF(a.type='a_vie','forlife','alias'),REPLACE(a.flags,'epouse','marital'),a.expire
	FROM aliases AS a
	WHERE a.type = 'a_vie'
	 OR a.type = 'alias';
INSERT INTO email_source_account (uid,email,type)
	SELECT a.uid,v.alias,'alias'
	FROM virtual AS v
	LEFT JOIN virtual_redirect AS vr ON (v.vid=vr.vid)
	LEFT JOIN accounts AS a ON ( a.hruid=LEFT(vr.redirect,LOCATE('@',vr.redirect)-1) )
	WHERE v.type='user'
	 AND v.alias LIKE "%@melix.net"
	 AND a.uid IS NOT NULL;
INSERT INTO email_source_account (uid,email,type)
	SELECT a.uid,REPLACE(v.alias,'@melix.net','@melix.org'),'alias'
	FROM virtual AS v
	LEFT JOIN virtual_redirect AS vr ON (v.vid=vr.vid)
	LEFT JOIN accounts AS a ON ( a.hruid=LEFT(vr.redirect,LOCATE('@',vr.redirect)-1) )
	WHERE v.type='user'
	 AND v.alias LIKE "%@melix.net"
	 AND a.uid IS NOT NULL;

INSERT INTO email_source_other (hrmid,email,type)
	SELECT CONCAT(CONCAT('h.',a.alias),'.polytechnique.org'),
	       CONCAT(a.alias,'@polytechnique.org'),'homonym'
	FROM aliases AS a
	WHERE a.type = 'homonyme'
	GROUP BY (a.alias);
INSERT INTO email_source_other (hrmid,email,type)
	SELECT CONCAT(CONCAT('h.',a.alias),'.polytechnique.org'),
	       CONCAT(a.alias,'@m4x.org'),'homonym'
	FROM aliases AS a
	WHERE a.type = 'homonyme'
	GROUP BY (a.alias);
INSERT INTO email_source_other (hrmid,email,type)
	SELECT CONCAT(CONCAT('h.',a.alias),'.polytechnique.org'),
	       CONCAT(a.alias,'@m4x.net'),'homonym'
	FROM aliases AS a
	WHERE a.type = 'homonyme'
	GROUP BY (a.alias);

INSERT INTO homonyms_list (hrmid,uid)
	SELECT CONCAT(CONCAT('h.',a.alias),'.polytechnique.org'), h.uid
	FROM homonyms AS h
	INNER JOIN aliases AS a ON (a.uid=h.homonyme_id)
	WHERE a.type = 'homonyme';

INSERT INTO email_redirect_account (uid,redirect,rewrite,type,action,
	                    broken,broken_level,last,flags,hash,allow_rewrite)
	SELECT a.uid,e.email,e.rewrite,'smtp',ef.email,
	       e.panne,e.panne_level,e.last,IF(e.flags='','disabled',IF(e.flags='disable','disabled',IF(e.flags='panne','broken',e.flags))),e.hash,e.allow_rewrite
	FROM emails AS e
	LEFT JOIN emails AS ef ON (e.uid=ef.uid)
	LEFT JOIN accounts AS a ON (e.uid=a.uid)
	WHERE e.flags != 'filter'
	 AND  ef.flags = 'filter';
INSERT INTO email_redirect_account (uid,redirect,type,action,flags)
	SELECT a.uid,CONCAT(a.hruid,"@g.polytechnique.org"),'googleapps',ef.email,'active'
	FROM email_options AS eo
	LEFT JOIN accounts AS a ON (a.uid=eo.uid)
	LEFT JOIN emails AS ef ON (eo.uid=ef.uid)
	WHERE FIND_IN_SET('googleapps',eo.storage)
	 AND ef.flags="filter";
INSERT INTO email_redirect_account (uid,redirect,type,action,flags)
	SELECT a.uid,CONCAT(a.hruid,"@imap.polytechnique.org"),'imap','let_spams','active'
	FROM email_options AS eo
	LEFT JOIN accounts AS a ON (a.uid=eo.uid)
	WHERE FIND_IN_SET('imap',eo.storage);

INSERT INTO email_redirect_other (hrmid,type,action)
	SELECT eso.hrmid,'homonym','homonym'
	FROM email_source_other AS eso
	WHERE eso.type = 'homonym'
	GROUP BY (eso.hrmid);

INSERT INTO email_virtual (email,redirect,type)
	SELECT v.alias,vr.redirect,IF(v.type='dom','domain',IF(v.type='evt','event',v.type))
	FROM virtual AS v
	LEFT JOIN virtual_redirect AS vr ON (vr.vid=v.vid)
	WHERE v.alias NOT LIKE "%@melix.net"
	 AND vr.vid IS NOT NULL
	 AND v.alias != '@melix.org';
INSERT INTO email_virtual (email,redirect,type)
	SELECT CONCAT(a.alias,"@polytechnique.org"),
	       CONCAT(
	         'polytechnique.org_',
	         REPLACE(
	           REPLACE(
	             REPLACE(
	               CONCAT(a.alias, '+post@listes.polytechnique.org'),
	               '-admin+post', '+admin'
	             ),
	             '-owner+post', '+owner'
	           ),
	           '-bounces+post', '+bounces'
	         )
	       ),
	       'list'
	FROM aliases AS a
	WHERE type='liste';
INSERT INTO email_virtual (email,redirect,type)
	SELECT CONCAT(a.alias,"@m4x.org"),
	       CONCAT(
	         'polytechnique.org_',
	         REPLACE(
	           REPLACE(
	             REPLACE(
	               CONCAT(a.alias, '+post@listes.polytechnique.org'),
	               '-admin+post', '+admin'
	             ),
	             '-owner+post', '+owner'
	           ),
	           '-bounces+post', '+bounces'
	         )
	       ),
	       'list'
	FROM aliases AS a
	WHERE type='liste';
INSERT INTO email_virtual (email,redirect,type)
	SELECT CONCAT(a.alias,"@m4x.net"),
	       CONCAT(
	         'polytechnique.org_',
	         REPLACE(
	           REPLACE(
	             REPLACE(
	               CONCAT(a.alias, '+post@listes.polytechnique.org'),
	               '-admin+post', '+admin'
	             ),
	             '-owner+post', '+owner'
	           ),
	           '-bounces+post', '+bounces'
	         )
	       ),
	       'list'
	FROM aliases AS a
	WHERE type='liste';
INSERT INTO email_virtual (email,redirect,type)
	SELECT vr.redirect,v.alias,'user'
	FROM virtual AS v
	LEFT JOIN virtual_redirect AS vr ON (v.vid=vr.vid)
	LEFT JOIN accounts AS a ON ( a.hruid=LEFT(vr.redirect,LOCATE('@',vr.redirect)-1) )
	WHERE v.type='user'
	 AND v.alias LIKE "%@melix.net"
	 AND vr.vid IS NOT NULL
	 AND a.uid IS NULL;
INSERT INTO email_virtual (email,redirect,type)
	SELECT vr.redirect,REPLACE(v.alias,'@melix.net','@melix.org'),'user'
	FROM virtual AS v
	LEFT JOIN virtual_redirect AS vr ON (v.vid=vr.vid)
	LEFT JOIN accounts AS a ON ( a.hruid=LEFT(vr.redirect,LOCATE('@',vr.redirect)-1) )
	WHERE v.type='user'
	 AND v.alias LIKE "%@melix.net"
	 AND vr.vid IS NOT NULL
	 AND a.uid IS NULL;
/*
Note: There are some adresses on virtual that have no match on the virtual_redirect. The adresses
      in this situation are dropped.
 */

INSERT INTO email_virtual_domains (domain)
	VALUES ("polytechnique.org"),
	       ("m4x.org"),
	       ("m4x.net");
INSERT INTO email_virtual_domains (domain)
	SELECT domain
	FROM virtual_domains;

/* From aliases file */
INSERT INTO email_virtual (email,redirect,type) VALUES
	("otrs.platal@polytechnique.org","otrs@svoboda.polytechnique.org","admin"),
	("otrs.platal@m4x.org","otrs.platal@polytechnique.org","admin"),
	("otrs.platal@m4x.net","otrs.platal@polytechnique.org","admin"),
	("validation@polytechnique.org","hotliners@staff.polytechnique.org","admin"),
	("validation@m4x.org","validation@polytechnique.org","admin"),
	("validation@m4x.net","validation@polytechnique.org","admin"),
	("listes+admin@polytechnique.org","br@staff.polytechnique.org","admin"),
	("listes+admin@m4x.org","listes+admin@polytechnique.org","admin"),
	("listes+admin@m4x.net","listes+admin@polytechnique.org","admin"),
	("listes@polytechnique.org","otrs.platal+listes@polytechnique.org","admin"),
	("listes@m4x.org","listes@polytechnique.org","admin"),
	("listes@m4x.net","listes@polytechnique.org","admin"),
	("gld@polytechnique.org","listes@polytechnique.org","admin"),
	("gld@m4x.org","gld@polytechnique.org","admin"),
	("gld@m4x.net","gld@polytechnique.org","admin"),
	("support@polytechnique.org","otrs.platal+support@polytechnique.org","admin"),
	("support@m4x.org","support@polytechnique.org","admin"),
	("support@m4x.net","support@polytechnique.org","admin"),
	("contact@polytechnique.org","otrs.platal+contact@polytechnique.org","admin"),
	("contact@m4x.org","contact@polytechnique.org","admin"),
	("contact@m4x.net","contact@polytechnique.org","admin"),
	("register@polytechnique.org","otrs.platal+register@polytechnique.org","admin"),
	("register@m4x.org","register@polytechnique.org","admin"),
	("register@m4x.net","register@polytechnique.org","admin"),
	("info@polytechnique.org","otrs.platal+info@polytechnique.org","admin"),
	("info@m4x.org","info@polytechnique.org","admin"),
	("info@m4x.net","info@polytechnique.org","admin"),
	("bug@polytechnique.org","otrs.platal+bug@polytechnique.org","admin"),
	("bug@m4x.org","bug@polytechnique.org","admin"),
	("bug@m4x.net","bug@polytechnique.org","admin"),
	("resetpass@polytechnique.org","otrs.platal+resetpass@polytechnique.org","admin"),
	("resetpass@m4x.org","resetpass@polytechnique.org","admin"),
	("resetpass@m4x.net","resetpass@polytechnique.org","admin"),
	("association@polytechnique.org","otrs.platal+association@polytechnique.org","admin"),
	("association@m4x.org","association@polytechnique.org","admin"),
	("association@m4x.net","association@polytechnique.org","admin"),
	("x-org@polytechnique.org","association@polytechnique.org","admin"),
	("x-org@m4x.org","x-org@polytechnique.org","admin"),
	("x-org@m4x.net","x-org@polytechnique.org","admin"),
	("manageurs@polytechnique.org","otrs@support.manageurs.com","partner"),
	("manageurs@m4x.org","manageurs@polytechnique.org","partner"),
	("manageurs@m4x.net","manageurs@polytechnique.org","partner"),
	("fondation@polytechnique.org","fondation@fondationx.org","partner"),
	("fondation@m4x.org","fondation@polytechnique.org","partner"),
	("fondation@m4x.net","fondation@polytechnique.org","partner"),
	("ax@polytechnique.org","ax@wanadoo.fr","partner"),
	("ax@m4x.org","ax@polytechnique.org","partner"),
	("ax@m4x.net","ax@polytechnique.org","partner"),
	("annuaire-ax@polytechnique.org","annuaire-ax@wanadoo.fr","partner"),
	("annuaire-ax@m4x.org","annuaire-ax@polytechnique.org","partner"),
	("annuaire-ax@m4x.net","annuaire-ax@polytechnique.org","partner"),
	("ax-bdc@polytechnique.org","ax-bdc@wanadoo.fr","partner"),
	("ax-bdc@m4x.org","ax-bdc@polytechnique.org","partner"),
	("ax-bdc@m4x.net","ax-bdc@polytechnique.org","partner"),
	("jaune@polytechnique.org","null@hruid.polytechnique.org","partner"),
	("jaune@m4x.org","jaune@polytechnique.org","partner"),
	("jaune@m4x.net","jaune@polytechnique.org","partner"),
	("jaune+rouge@polytechnique.org","jaune_rouge@wanadoo.fr","partner"),
	("jaune+rouge@m4x.org","jaune+rouge@polytechnique.org","partner"),
	("jaune+rouge@m4x.net","jaune+rouge@polytechnique.org","partner"),
	("xcourseaularge@polytechnique.org","info@xcourseaularge.polytechnique.org","partner"),
	("xcourseaularge@m4x.org","xcourseaularge@polytechnique.org","partner"),
	("xcourseaularge@m4x.net","xcourseaularge@polytechnique.org","partner"),
	("xim@polytechnique.org","membres@x-internet.polytechnique.org","partner"),
	("xim@m4x.org","xim@polytechnique.org","partner"),
	("xim@m4x.net","xim@polytechnique.org","partner"),
	("x-consult@polytechnique.org","info@x-consult.polytechnique.org","partner"),
	("x-consult@m4x.org","x-consult@polytechnique.org","partner"),
	("x-consult@m4x.net","x-consult@polytechnique.org","partner"),
	("xmcb@polytechnique.org","xmcb@x-consult.polytechnique.org","partner"),
	("xmcb@m4x.org","xmcb@polytechnique.org","partner"),
	("xmcb@m4x.net","xmcb@polytechnique.org","partner"),
	("x-maroc@polytechnique.org","allam@mtpnet.gov.ma","partner"),
	("x-maroc@m4x.org","x-maroc@polytechnique.org","partner"),
	("x-maroc@m4x.net","x-maroc@polytechnique.org","partner"),
	("x-musique@polytechnique.org","xmusique@free.fr","partner"),
	("x-musique@m4x.org","x-musique@polytechnique.org","partner"),
	("x-musique@m4x.net","x-musique@polytechnique.org","partner"),
	("x-resistance@polytechnique.org","info@xresistance.org","partner"),
	("x-resistance@m4x.org","x-resistance@polytechnique.org","partner"),
	("x-resistance@m4x.net","x-resistance@polytechnique.org","partner"),
	("x-israel@polytechnique.org","info@x-israel.polytechnique.org","partner"),
	("x-israel@m4x.org","x-israel@polytechnique.org","partner"),
	("x-israel@m4x.net","x-israel@polytechnique.org","partner"),
	("gpx@polytechnique.org","g.p.x@infonie.fr","partner"),
	("gpx@m4x.org","gpx@polytechnique.org","partner"),
	("gpx@m4x.net","gpx@polytechnique.org","partner"),
	("g.p.x@polytechnique.org","gpx@polytechnique.org","partner"),
	("g.p.x@m4x.org","g.p.x@polytechnique.org","partner"),
	("g.p.x@m4x.net","g.p.x@polytechnique.org","partner"),
	("pointgamma@polytechnique.org","gamma@frankiz.polytechnique.fr","partner"),
	("pointgamma@m4x.org","pointgamma@polytechnique.org","partner"),
	("pointgamma@m4x.net","pointgamma@polytechnique.org","partner"),
	("xmpentrepreneur@polytechnique.org","xmp.entrepreneur@gmail.com","partner"),
	("xmpentrepreneur@m4x.org","xmpentrepreneur@polytechnique.org","partner"),
	("xmpentrepreneur@m4x.net","xmpentrepreneur@polytechnique.org","partner"),
	("xmp-entrepreneur@polytechnique.org","xmp.entrepreneur@gmail.com","partner"),
	("xmp-entrepreneur@m4x.org","xmp-entrepreneur@polytechnique.org","partner"),
	("xmp-entrepreneur@m4x.net","xmp-entrepreneur@polytechnique.org","partner"),
	("xmpangels@polytechnique.org","xmpangels@xmp-ba.m4x.org","partner"),
	("xmpangels@m4x.org","xmpangels@polytechnique.org","partner"),
	("xmpangels@m4x.net","xmpangels@polytechnique.org","partner"),
	("xmp-angels@polytechnique.org","xmpangels@xmp-ba.m4x.org","partner"),
	("xmp-angels@m4x.org","xmp-angels@polytechnique.org","partner"),
	("xmp-angels@m4x.net","xmp-angels@polytechnique.org","partner"),
	("relex@polytechnique.org","relex@staff.polytechnique.org","admin"),
	("relex@m4x.org","relex@polytechnique.org","admin"),
	("relex@m4x.net","relex@polytechnique.org","admin"),
	("tresorier@polytechnique.org","tresorier@staff.polytechnique.org","admin"),
	("tresorier@m4x.org","tresorier@polytechnique.org","admin"),
	("tresorier@m4x.net","tresorier@polytechnique.org","admin"),
	("aaege-sso@polytechnique.org","aaege-sso@staff.polytechnique.org","admin"),
	("aaege-sso@m4x.org","aaege-sso@polytechnique.org","admin"),
	("aaege-sso@m4x.net","aaege-sso@polytechnique.org","admin"),
	("innovation@polytechnique.org","innovation@staff.polytechnique.org","admin"),
	("innovation@m4x.org","innovation@polytechnique.org","admin"),
	("innovation@m4x.net","innovation@polytechnique.org","admin"),
	("groupes@polytechnique.org","groupes@staff.polytechnique.org","admin"),
	("groupes@m4x.org","groupes@polytechnique.org","admin"),
	("groupes@m4x.net","groupes@polytechnique.org","admin"),
	("br@polytechnique.org","br@staff.polytechnique.org","admin"),
	("br@m4x.org","br@polytechnique.org","admin"),
	("br@m4x.net","br@polytechnique.org","admin"),
	("ca@polytechnique.org","ca@staff.polytechnique.org","admin"),
	("ca@m4x.org","ca@polytechnique.org","admin"),
	("ca@m4x.net","ca@polytechnique.org","admin"),
	("personnel@polytechnique.org","br@staff.polytechnique.org","admin"),
	("personnel@m4x.org","personnel@polytechnique.org","admin"),
	("personnel@m4x.net","personnel@polytechnique.org","admin"),
	("cil@polytechnique.org","cil@staff.polytechnique.org","admin"),
	("cil@m4x.org","cil@polytechnique.org","admin"),
	("cil@m4x.net","cil@polytechnique.org","admin"),
	("opensource@polytechnique.org","contact@polytechnique.org","admin"),
	("opensource@m4x.org","opensource@polytechnique.org","admin"),
	("opensource@m4x.net","opensource@polytechnique.org","admin"),
	("forums@polytechnique.org","forums@staff.m4x.org","admin"),
	("forums@m4x.org","forums@polytechnique.org","admin"),
	("forums@m4x.net","forums@polytechnique.org","admin"),
	("telepaiement@polytechnique.org","telepaiement@staff.m4x.org","admin"),
	("telepaiement@m4x.org","telepaiement@polytechnique.org","admin"),
	("telepaiement@m4x.net","telepaiement@polytechnique.org","admin"),
	("hotliners@polytechnique.org","hotliners@staff.m4x.org","admin"),
	("hotliners@m4x.org","hotliners@polytechnique.org","admin"),
	("hotliners@m4x.net","hotliners@polytechnique.org","admin"),
	("kes@polytechnique.org","kes@frankiz.polytechnique.fr","partner"),
	("kes@m4x.org","kes@polytechnique.org","partner"),
	("kes@m4x.net","kes@polytechnique.org","partner"),
	("kes1999@polytechnique.org","cariokes@polytechnique.org","partner"),
	("kes1999@m4x.org","kes1999@polytechnique.org","partner"),
	("kes1999@m4x.net","kes1999@polytechnique.org","partner"),
	("kes2000@polytechnique.org","kestinpowers@polytechnique.org","partner"),
	("kes2000@m4x.org","kes2000@polytechnique.org","partner"),
	("kes2000@m4x.net","kes2000@polytechnique.org","partner");

INSERT INTO email_source_other (hrmid,email,type) VALUES
	("ax.test.polytechnique.org","AX-test@polytechnique.org","ax"),
	("ax.test.polytechnique.org","AX-test@m4x.org","ax"),
	("ax.test.polytechnique.org","AX-test@m4x.net","ax"),
	("ax.nicolas.zarpas.polytechnique.org","AX-nicolas.zarpas@polytechnique.org","ax"),
	("ax.nicolas.zarpas.polytechnique.org","AX-nicolas.zarpas@m4x.org","ax"),
	("ax.nicolas.zarpas.polytechnique.org","AX-nicolas.zarpas@m4x.net","ax"),
	("ax.carrieres.polytechnique.org","AX-carrieres@polytechnique.org","ax"),
	("ax.carrieres.polytechnique.org","AX-carrieres@m4x.org","ax"),
	("ax.carrieres.polytechnique.org","AX-carrieres@m4x.net","ax"),
	("ax.info1.polytechnique.org","AX-info1@polytechnique.org","ax"),
	("ax.info1.polytechnique.org","AX-info1@m4x.org","ax"),
	("ax.info1.polytechnique.org","AX-info1@m4x.net","ax"),
	("ax.info2.polytechnique.org","AX-info2@polytechnique.org","ax"),
	("ax.info2.polytechnique.org","AX-info2@m4x.org","ax"),
	("ax.info2.polytechnique.org","AX-info2@m4x.net","ax"),
	("ax.bal.polytechnique.org","AX-bal@polytechnique.org","ax"),
	("ax.bal.polytechnique.org","AX-bal@m4x.org","ax"),
	("ax.bal.polytechnique.org","AX-bal@m4x.net","ax"),
	("ax.annuaire.polytechnique.org","AX-annuaire@polytechnique.org","ax"),
	("ax.annuaire.polytechnique.org","AX-annuaire@m4x.org","ax"),
	("ax.annuaire.polytechnique.org","AX-annuaire@m4x.net","ax"),
	("ax.jaune-rouge.polytechnique.org","AX-jaune-rouge@polytechnique.org","ax"),
	("ax.jaune-rouge.polytechnique.org","AX-jaune-rouge@m4x.org","ax"),
	("ax.jaune-rouge.polytechnique.org","AX-jaune-rouge@m4x.net","ax"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","jean-pierre.bilah.1980@polytechnique.org","honeypot"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","jean-pierre.bilah.1980@m4x.org","honeypot"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","jean-pierre.bilah.1980@m4x.net","honeypot"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","jean-pierre.blah.1980@polytechnique.org","honeypot"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","jean-pierre.blah.1980@m4x.org","honeypot"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","jean-pierre.blah.1980@m4x.net","honeypot");

INSERT INTO email_redirect_other (hrmid,redirect,type,action) VALUES
	("ax.nicolas.zarpas.polytechnique.org","nicolas.zarpas-ax@wanadoo.fr","smtp","tag_spams"),
	("ax.carrieres.polytechnique.org","manuela.brasseur-bdc@wanadoo.fr","smtp","tag_spams"),
	("ax.info1.polytechnique.org","sylvie.clairefond-ax@wanadoo.fr","smtp","tag_spams"),
	("ax.info2.polytechnique.org","catherine.perot-ax@wanadoo.fr","smtp","tag_spams"),
	("ax.bal.polytechnique.org","baldelx-ax@wanadoo.fr","smtp","tag_spams"),
	("ax.annuaire.polytechnique.org","annuaire-ax@wanadoo.fr","smtp","tag_spams"),
	("ax.jaune-rouge.polytechnique.org","jaune_rouge@wanadoo.fr","smtp","tag_spams"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","jean-pierre.bilah.1980.mbox@murphy.m4x.org","smtp","let_spams"),
	("honey.jean-pierre.bilah.1980.polytechnique.org","raphael.barrois.2006@polytechnique.org","smtp","let_spams");

/* Drop renamed list */
DELETE FROM email_virtual WHERE email LIKE "tech-email%@polytechnique.org";
DELETE FROM email_virtual WHERE email LIKE "tech-email%@m4x.org";
DELETE FROM email_virtual WHERE email LIKE "tech-email%@m4x.net";

/*********************************************************************************************************************************/
DROP TABLE aliases;
DROP TABLE virtual;
DROP TABLE virtual_redirect;
DROP TABLE homonyms;
DROP TABLE emails;
DROP TABLE email_options;
DROP TABLE domain;


-- vim:set syntax=mysql:
