<?php
    
function banana($params) {
    global $globals, $page;
    global $banana,$css;
    $sname = basename($_SERVER['SCRIPT_NAME']);
    require_once('config.inc.php');
    require_once("../../../banana/$sname");
}

?>
