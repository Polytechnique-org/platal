<?php
require("auto.prepend.inc.php");
new_skinned_page("docs/doc_nn.tpl",AUTH_PUBLIC, false, "docs/doc_nn.head.tpl");
$page->display(isset($_GET['doc']) ? $_GET['doc'] : "");
?>
