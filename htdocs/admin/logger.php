<?php
require("auto.prepend.inc.php");
new_admin_page('logger-view.tpl');
require('diogenes.logger-view.inc.php');

$logview = new DiogenesLoggerView;
$logview->run($page,'logger-view.tpl',false);

$page->run();
?>
