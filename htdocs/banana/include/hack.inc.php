<?php
    
function banana($params) {
    global $globals, $page;
    global $banana,$first,$group,$post,$id,$css;
    $sname = basename($_SERVER['SCRIPT_NAME']);

    global $news_server,$news_port;

    $news_server    = $globals->banana->server;
    $news_port      = $globals->banana->port;

    require_once('config.inc.php');
    require_once("../../../banana/$sname");
}

?>
