<?php

/*
 * Smarty plugin
 * ------------------------------------------------------------- 
 * File:     insert.mkStats.php
 * Type:     insert
 * Name:     mkStats
 * Purpose:  
 * -------------------------------------------------------------
 */
function smarty_insert_mkStats($params, &$smarty)
{
    global $conn;
    $req = mysql_query("select count(*) from requests",$conn);
    list($stats_req) = mysql_fetch_row($req);
    mysql_free_result($req);
    return ($stats_req ? $stats_req : "-");
}
?>
