CREATE TABLE axletter (
    id int(11) unsigned NOT NULL auto_increment,
    shortname varchar(16) default NULL default '',
    subject varchar(255) NOT NULL default '',
    title varchar(255) NOT NULL default '',
    body mediumtext NOT NULL default '',
    signature mediumtext NOT NULL default '',
    promo_min smallint(4) NOT NULL default 0,
    promo_max smallint(4) NOT NULL default 0,
    echeance datetime NOT NULL default 0,
    date date NOT NULL default 0,
    bits set('new', 'sent', 'invalid') NOT NULL default 'new',

    PRIMARY KEY(id),
    UNIQUE KEY(shortname)
);
CREATE TABLE axletter_ins (
    user_id smallint(4) NOT NULL default 0,
    email varchar(255) default NULL,
    flag set('femme') default '',
    prenom varchar(32) default NULL,
    nom varchar(255) default NULL,
    promo smallint(4) default 0,
    last int(11) NOT NULL default 0,
    hash varchar(32) default NULL,

    PRIMARY KEY(user_id, email),
    KEY(last)
);
CREATE TABLE axletter_rights (
    user_id smallint(4) NOT NULL default 0,
    PRIMARY KEY(user_id)
);
# vim:set syntax=mysql:
