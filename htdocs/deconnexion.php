<?php
require("auto.prepend.inc.php");

if (isset($_SESSION['log'])) {
    if (isset($_SERVER['HTTP_REFERER']))
        $ref = $_SERVER['HTTP_REFERER'];
    else
        $ref = "";
    $_SESSION['log']->log("deconnexion",$ref);
}

session_destroy();
$has_cookie = (isset($_COOKIE['ORGaccess']) && isset($_COOKIE['ORGlogin']));
$_SESSION = array();
if($has_cookie)
    header("Location: ".url("login.php"));

new_skinned_page('deconnexion.tpl', AUTH_PUBLIC);

$page->display();
?>
