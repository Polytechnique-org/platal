<?php
require("auto.prepend.inc.php");
new_skinned_page('index.tpl', AUTH_PUBLIC);
if(logged())
    header("Location: login.php");
$page->display();
?>
