<?php

ini_set("include_path", "../../:./simpletest/:".ini_get('include_path'));
define('PATH', dirname(__FILE__));

require_once('unit_tester.php');
require_once('mock_objects.php');
require_once('reporter.php');
require_once('xorg.globals.inc.php');
XorgGlobals::init();

$reporter = new HtmlReporter();
?>
