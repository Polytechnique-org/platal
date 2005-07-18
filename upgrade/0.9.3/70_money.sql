-- MySQL dump 9.11
--
-- Host: localhost    Database: trezo
-- ------------------------------------------------------
-- Server version	4.0.22-log

--
-- Table structure for table `operations`
--

CREATE TABLE `money_trezo` (
  `id` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `label` varchar(80) NOT NULL default '',
  `credit` decimal(10,2) NOT NULL default '0.00',
  `debit` decimal(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Table des comptes de l''assoce';

insert into money_trezo
   select * from trezo.operations;

drop table trezo.operations;
drop database trezo;

