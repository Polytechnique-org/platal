<?php
require("auto.prepend.inc.php");
new_skinned_page('index.tpl',AUTH_COOKIE);

//adresse de redirection par defaut
if (isset($_REQUEST['dest'])) $redirect=$_REQUEST['dest'];
else $redirect="/";

header("Location: ".$redirect);
?>
