<?php

require 'xnet.inc.php';

new_page('xnet/groupe/asso.tpl', AUTH_PUBLIC);
$page->useMenu();
$page->setType($globals->asso('cat'));
$page->assign('is_member', is_member());
$page->assign('logged', logged());

$page->assign('asso', $globals->asso());
if (!$globals->asso('id')) {
    redirect("../");
}

$page->run();

?>
