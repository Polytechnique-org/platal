<?php
    
function banana($params) {
    global $globals,$news_server,$news_port,$page,$news_auth_pass;
    global $news,$locale,$first,$spool,$group,$post,$id,$profile,$css;
    $sname = $_SERVER['SCRIPT_NAME'];
    $array = explode('/',$sname);
    $sname = array_pop($array);
    unset($array);

    require_once("../../../banana/$sname");
}

?>
