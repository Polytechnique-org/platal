<?php
require("auto.prepend.inc.php");
new_skinned_page("docs/doc_carva.tpl",AUTH_COOKIE, true);
$page->display(isset($_SESSION['alias']) ? 'alias' : '');
?>
