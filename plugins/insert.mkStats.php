<?php

function url()
{
  $chemins = Array('.', '..', '../..');
  foreach ($chemins as $ch) {
    if (file_exists("$ch/login.php"))
      return "$ch";
  }
  return "";
}
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
    $stats_req = ($stats_req ? $stats_req : "-");

    $rel = url();
    return <<<EOF
        <table class="bicol"
          style="font-weight:normal;text-align:center; border-left:0px; border-right:0px; margin-top:0.5em; width:100%; margin-left: 0; font-size: smaller;">
        <tr>
          <th>Valid</th>
        </tr>
        <tr class="impair">
          <td><a href="$rel/admin/valider.php">$stats_req</a></td>
        </tr>
        </table>
EOF;
}
?>
