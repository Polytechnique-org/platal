<?php
require("auto.prepend.inc.php");
new_skinned_page("docs/contacts.tpl",AUTH_PUBLIC);
$page->display(isset($_REQUEST['topic']) ? $_REQUEST['topic'] : '');
?>
