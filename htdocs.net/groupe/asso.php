<?php

require 'xnet.inc.php';

new_page('xnet/groupe/asso.tpl', AUTH_PUBLIC);
$page->useMenu();
$page->setType($globals->asso('cat'));

$page->assign('asso', $globals->asso());
if (!$globals->asso('id')) {
    header("Location: ../");
}

$page->run();

?>
