<?php
require("auto.prepend.inc.php");
new_admin_page('index.tpl');
$page->clear_all_cache();
header("Location: index.php");
?>
