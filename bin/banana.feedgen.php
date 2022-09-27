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
require_once dirname(__FILE__) . '/../include/banana/forum.inc.php';
ini_set('memory_limit', '128M');

Banana::$mbox_helper = $globals->spoolroot . '/banana/mbox-helper/mbox-helper';
Banana::$spool_root = $globals->spoolroot . '/spool/banana/';
Banana::$nntp_host =  ForumsBanana::buildURL();
Banana::refreshAllFeeds(array('MLArchive',));

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker fenc=utf-8:
?>
