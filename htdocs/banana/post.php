<?php
/********************************************************************************
* index.php : main page (newsgroups list)
* -----------
*
* This file is part of the banana distribution
* Copyright: See COPYING files that comes with this distribution
********************************************************************************/

require_once('xorg.inc.php');
new_skinned_page('banana.tpl', AUTH_COOKIE, 'banana.head.tpl');
$page->caching = false;
require_once('hack.inc.php');

$page->register_function('banana', 'banana', false);

$page->run();
?>
