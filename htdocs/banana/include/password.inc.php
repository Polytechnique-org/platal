<?php

$sname = $_SERVER['SCRIPT_NAME'];
$array = explode('/',$sname);
$sname = array_pop($array);
unset($array);

if ($sname == "spoolgen.php") {
    require('config.xorg.inc.php');
    $news["user"]=$news_web_user;
    $news["pass"]=$news_web_pass;
} elseif (isset($_SESSION['forlife'])) {
    $news["user"]="web_pierre.habouzit";#.$_SESSION['forlife'];
    $news["pass"]=$news_auth_pass;
}
$news['server']="$news_server:$news_port";
?>
