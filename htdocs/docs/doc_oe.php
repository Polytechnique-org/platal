<?php
require("auto.prepend.inc.php");
new_skinned_page("docs/doc_oe.tpl",AUTH_PUBLIC);
$page->display(isset($_GET['doc']) ? $_GET['doc'] : "");
?>
