<?php
    
function banana($params) {
    global $globals, $page;
    global $news,$locale,$first,$spool,$group,$post,$id,$profile,$css;
    $sname = $_SERVER['SCRIPT_NAME'];
    $array = explode('/',$sname);
    $sname = array_pop($array);
    unset($array);

    global $news_server,$news_port,$news_auth_pass;

    $news_server    = $globals->banana->server;
    $news_port      = $globals->banana->port;
    $news_auth_pass = $globals->banana->password;

    require_once("../../../banana/$sname");
}

?>
