-- MySQL dump 9.11
--
-- Host: localhost    Database: x4dat
-- ------------------------------------------------------
-- Server version	4.0.21-log

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE `newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `titre` varchar(255) NOT NULL default '',
  `bits` enum('sent','new') NOT NULL default 'new',
  `head` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='liste des NL envoyes';

--
-- Dumping data for table `newsletter`
--

INSERT INTO `newsletter` VALUES (3,'2001-11-04','Lettre n°3 de Polytechnique.org : novembre 2001','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 3ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (4,'2001-12-22','Lettre n°4 de Polytechnique.org : décembre 2001','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 4ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (5,'2002-03-01','Lettre n°5 de Polytechnique.org : mars 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 5ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (6,'2002-04-01','Lettre n°6 de Polytechnique.org : avril 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 6ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (7,'2002-05-02','Lettre n°7 de Polytechnique.org : mai 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 7ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (9,'2002-07-01','Lettre n°9 de Polytechnique.org : juillet 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 9ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (8,'2002-06-02','Lettre n°8 de Polytechnique.org : juin 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 8ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (13,'2002-11-01','Lettre n°13 de Polytechnique.org : novembre 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 13ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (14,'2002-12-02','Lettre n°14 de Polytechnique.org : décembre 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 14ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (15,'2003-01-02','Lettre n°15 de Polytechnique.org : janvier 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 15ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (21,'2003-07-10','Lettre n°21 de Polytechnique.org : juillet 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 21ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (20,'2003-06-01','Lettre n°20 de Polytechnique.org : juin 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 20ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (19,'2003-04-27','Lettre n°19 de Polytechnique.org : mai 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 19ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (18,'2003-04-01','Lettre n°18 de Polytechnique.org : avril 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 18ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (35,'2004-09-01','Lettre n°34 de Polytechnique.org : septembre 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 34ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (29,'2004-03-01','Lettre n°29 de Polytechnique.org : mars 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 29ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (36,'2004-10-01','Lettre n°35 de Polytechnique.org : octobre 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 35ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (26,'2003-12-30','Lettre n°26 de Polytechnique.org : décembre 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 26ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (27,'2004-01-01','Lettre n°27 de Polytechnique.org : janvier 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 27ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (25,'2003-10-25','Lettre n°25 de Polytechnique.org : novembre 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 25ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (22,'2003-09-01','Lettre n°22 de Polytechnique.org : septembre 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 22ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (24,'2003-10-01','Lettre n°24 de Polytechnique.org : octobre 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 24ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (30,'2004-03-08','Lettre exceptionnelle \"speciale virus\"','','');
INSERT INTO `newsletter` VALUES (31,'2004-04-01','Lettre n°30 de Polytechnique.org : avril 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 30ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (32,'2004-05-01','Lettre n°31 de Polytechnique.org : mai 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 31ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (33,'2004-06-01','Lettre n°32 de Polytechnique.org : juin 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 32ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (34,'2004-07-01','Lettre n°33 de Polytechnique.org : juillet 2004','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 33ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (28,'2004-02-01','Lettre n°28 de Polytechnique.org : février 2004','','<cher> <prenom>,\r\n \r\nL\'équipe de Polytechnique.org est fière de te présenter la 28ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (23,'2003-09-24','Réseaux polytechniciens, réseau Internet : même combat ?','','<cher> <prenom>,\r\n\r\nVoici une lettre d\'information exceptionnelle du site Polytechnique.org.\r\n');
INSERT INTO `newsletter` VALUES (1,'2001-04-24','Lettre n°1 de Polytechnique.org : avril 2001','','<cher> <prenom>,\r\n \r\n L\'équipe de Polytechnique.org est fière de te présenter la 1ère lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (2,'2001-09-28','Lettre n°2 de Polytechnique.org : octobre 2001','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 2ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (12,'2002-10-01','Lettre n°12 de Polytechnique.org : octobre 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 12ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (16,'2003-02-01','Lettre n°16 de Polytechnique.org : février 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 16ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (10,'2002-08-01','Lettre n°10 de Polytechnique.org : août 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 10ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (11,'2002-09-02','Lettre n°11 de Polytechnique.org : septembre 2002','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 11ème lettre d\'information des polytechniciens sur le net.');
INSERT INTO `newsletter` VALUES (17,'2003-03-01','Lettre n°17 de Polytechnique.org : mars 2003','','<cher> <prenom>,\n\nL\'équipe de Polytechnique.org est fière de te présenter la 17ème lettre d\'information des polytechniciens sur le net.');

