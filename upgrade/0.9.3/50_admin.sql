--
-- Table structure for table `admin_h1`
--

CREATE TABLE `admin_h1` (
  `h1id` tinyint(4) NOT NULL default '0',
  `h1` varchar(64) NOT NULL default '',
  `prio` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (h1id),
  KEY `prio` (`prio`)
) TYPE=MyISAM;

insert into admin_h1 values(1, 'Système',               0);
insert into admin_h1 values(2, 'Contenu du site',      10);
insert into admin_h1 values(3, 'Gestion et entretien', 30);

--
-- Table structure for table `admin_h2`
--

CREATE TABLE `admin_h2` (
  `h1id` tinyint(4) NOT NULL default '0',
  `h2id` tinyint(4) NOT NULL default '0',
  `h2` varchar(64) NOT NULL default '',
  `prio` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (h2id),
  KEY `prio` (`prio`)
) TYPE=MyISAM;

insert into admin_h2 values(1,  1, 'Postfix',                 0);
insert into admin_h2 values(1,  2, 'Sécurité',               20);

insert into admin_h2 values(2,  3, 'Utilisateurs',            0);
insert into admin_h2 values(2,  4, 'Infos dynamiques',       10);
insert into admin_h2 values(2,  5, 'Champs profil',          20);
insert into admin_h2 values(2,  6, 'Newsletter',             30);
insert into admin_h2 values(2,  7, 'Administrer',            40);
insert into admin_h2 values(2,  8, 'Valider demandes',       50);
insert into admin_h2 values(2,  9, 'Trésorerie',             60);

insert into admin_h2 values(3, 10, 'Reformatage Prenom NOM',  0);

--
-- Table structure for table `admin_a`
--

CREATE TABLE `admin_a` (
  `h2id` tinyint(4) NOT NULL default '0',
  `texte` varchar(64) NOT NULL default '',
  `url` varchar(64) NOT NULL default '',
  `prio` tinyint(4) NOT NULL default '0',
  KEY `prio` (`prio`)

) TYPE=MyISAM;

insert into admin_a values(1, 'Blacklist', 'admin/postfix_blacklist.php',  0);
insert into admin_a values(1, 'Whitelist', 'admin/postfix_whitelist.php', 10);
insert into admin_a values(1, 'Retardés',  'admin/postfix_delayed.php',   20);

insert into admin_a values(2, 'Logs des sessions', 'admin/logger.php', 0);
insert into admin_a values(2, 'Actions', 'admin/logger_actions.php', 10);

insert into admin_a values(3, 'Edit/Su/Logs', 'admin/utilisateurs.php',  0);
insert into admin_a values(3, 'Homonymes', 'admin/homonymes.php', 10);
insert into admin_a values(3, 'AX/X.org', 'admin/ax-xorg.php', 20);
insert into admin_a values(3, 'Décès', 'admin/deces_promo.php', 30);

insert into admin_a values(4, 'Coupures', 'admin/gerer_coupure.php',  0);
insert into admin_a values(4, 'Événements', 'admin/evenements.php', 10);

insert into admin_a values(5, 'Formations', 'admin/gerer_applis.php',  0);
insert into admin_a values(5, 'Binets', 'admin/gerer_binets.php', 10);
insert into admin_a values(5, 'Groupes X', 'admin/gerer_groupesx.php', 20);
insert into admin_a values(5, 'Skins', 'admin/gerer_skins.php', 30);

insert into admin_a values(6, 'Liste', 'admin/newsletter.php',  0);
insert into admin_a values(6, 'Catégories', 'admin/newsletter_cats.php', 10);
insert into admin_a values(6, 'Adresses en panne', 'admin/newsletter_pattecassee.php', 20);

insert into admin_a values(7, 'Auth Groupes X', 'admin/gerer_auth-groupex.php',  0);
insert into admin_a values(7, 'Listes', 'admin/lists.php', 10);

insert into admin_a values(8, 'Valider', 'admin/valider.php',  0);
insert into admin_a values(8, 'Événements', 'admin/evenements.php', 10);

insert into admin_a values(9, 'Comptes', 'admin/gere_operations.php', 0);
insert into admin_a values(9, 'Paiements', 'admin/gerer_paiement.php', 10);

insert into admin_a values(10, 'Table auth_user_md5', 'admin/FormatePrenomNOM.php', 0);

