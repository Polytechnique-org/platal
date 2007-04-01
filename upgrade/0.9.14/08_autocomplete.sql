CREATE TABLE `search_autocomplete` (
      `name` varchar(20) NOT NULL,
      `query` varchar(100) NOT NULL,
      `result` text NOT NULL,
      `generated` datetime NOT NULL,
      PRIMARY KEY  (`name`,`query`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
