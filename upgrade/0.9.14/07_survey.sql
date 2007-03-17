CREATE TABLE `survey_answers` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `survey_id` smallint(4) unsigned NOT NULL,
  `vote_id` smallint(5) unsigned NOT NULL,
  `question_id` smallint(3) unsigned NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `vote` (`survey_id`,`vote_id`,`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `survey_questions` (
  `survey_id` smallint(4) unsigned NOT NULL auto_increment,
  `questions` mediumtext NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `author_id` smallint(5) unsigned NOT NULL,
  `end` date NOT NULL default '0000-00-00',
  `promos` varchar(255) NOT NULL,
  `valid` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`survey_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `survey_votes` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `survey_id` smallint(4) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `voter` (`survey_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# vim: set syntax=mysql:
