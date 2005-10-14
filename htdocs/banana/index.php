<?php
/********************************************************************************
* index.php : main page (newsgroups list)
* -----------
*
* This file is part of the banana distribution
* Copyright: See COPYING files that comes with this distribution
********************************************************************************/

require_once('xorg.inc.php');
new_skinned_page('banana/index.tpl', AUTH_COOKIE);
$page->addCssLink('banana/css/style.css');
$page->assign('xorg_title','Polytechnique.org - Forums & PA');
require_once('banana.inc.php');

$res = PlatalBanana::run();
$page->assign_by_ref('banana', $banana);
$page->assign('banana_res', $res);
$page->run();
?>
