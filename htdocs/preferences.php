<?php
require("auto.prepend.inc.php");
new_skinned_page('preferences.tpl', AUTH_COOKIE);

$has_cookie = (isset($_COOKIE['ORGaccess']) ? 'cookie' : '');
$page->assign('has_cookie', $has_cookie);

$page->run($has_cookie);
?>
