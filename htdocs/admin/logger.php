<?php
require("auto.prepend.inc.php");
new_admin_page('logger-view.tpl');
require('diogenes.logger-view.inc.php');

if(empty($_REQUEST['logauth'])) $_REQUEST['logauth']='native';

$logview = new DiogenesLoggerView;
$logview->run($page);

$page->run();
?>
