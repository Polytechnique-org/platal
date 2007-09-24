#!/usr/bin/php5
<?php
/********************************************************************************
 * spoolgen.php : spool generation
 * --------------
 *
 * This file is part of the banana distribution
 * Copyright: See COPYING files that comes with this distribution
 ********************************************************************************/

require_once 'connect.db.inc.php';
require_once dirname(__FILE__).'/../../include/banana/ml.inc.php';

Banana::$mbox_helper = $globals->banana->mbox_helper;
Banana::$spool_root = $globals->banana->spool_root;
Banana::$nntp_host = "news://{$globals->banana->web_user}:{$globals->banana->web_pass}@{$globals->banana->server}:{$globals->banana->port}/";
Banana::refreshAllFeeds(array('NNTP', 'MLArchive'));

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
