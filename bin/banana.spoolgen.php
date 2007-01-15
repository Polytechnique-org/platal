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
require_once dirname(__FILE__).'/../include/banana/ml.inc.php';

Banana::$nntp_host = "news://{$globals->banana->web_user}:{$globals->banana->web_pass}@{$globals->banana->server}:{$globals->banana->port}/";
Banana::createAllSpool(array('NNTP', 'MLArchive'));
system("chown -R www-data:www-data /var/spool/banana");
?>
