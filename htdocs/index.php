<?php
require("auto.prepend.inc.php");
if(logged())
    header("Location: login.php");
new_skinned_page('index.tpl', AUTH_PUBLIC);

$page->display();
?>
