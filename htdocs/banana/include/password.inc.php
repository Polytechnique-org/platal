<?php

$sname = $_SERVER['SCRIPT_NAME'];
$array = explode('/',$sname);
$sname = array_pop($array);
unset($array);

if ($sname == "spoolgen.php") {
    $news["user"] = $globals->banana->web_user;
    $news["pass"] = $globals->banana->web_pass;
} elseif (isset($_SESSION['forlife'])) {
    $news["user"]= "web_".$_SESSION['forlife'];
    $news["pass"]= $globals->banana->password;
}
$news['server']="$news_server:$news_port";
?>
