<?php
require("auto.prepend.inc.php");
new_skinned_page('index.tpl', AUTH_COOKIE);

setcookie('ORGaccess','',(time()+1),'/','',0);
$_SESSION['log']->log("cookie_off");

// si on a le cookie, et qu'on est pas identifié,
// s'enlever le cookie revient à se déconnecter
if(!identified()) {
    session_destroy();
    $_SESSION = array();
}

$page->display();
?>
