<?php
require("auto.prepend.inc.php");
new_skinned_page("docs/doc_carva.tpl",AUTH_COOKIE, true);
$page->run(isset($_SESSION['alias']) ? 'alias' : '');
?>
