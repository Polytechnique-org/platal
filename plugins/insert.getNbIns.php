<?php
/*
 * Smarty plugin
 * ------------------------------------------------------------- 
 * File:     insert.getNbIns.php
 * Type:     insert
 * Name:     getNbIns
 * Purpose:  
 * -------------------------------------------------------------
 */
function smarty_insert_getNbIns($params, &$smarty)
{
    $result=mysql_query("SELECT COUNT(*) FROM auth_user_md5 AS a INNER JOIN identification AS i
            ON a.matricule=i.matricule where i.deces = 0");
    list($stats_count)=mysql_fetch_row($result);
    mysql_free_result($result);
    return "$stats_count";
}
?>
