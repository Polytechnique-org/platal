<?php
function nb_trk($pri,$tr) {
    global $conn;
    $req = mysql_query("select count(rq_id) from trackers.requests left join trackers.states as st using(st_id)
            where tr_id='$tr' and admin_id<=0 and pri='$pri' and st.texte!='fermé'", $conn);
    list($res) = mysql_fetch_row($req);
    mysql_free_result($req);
    return ($res?$res:"-");
}

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

    $nbtrk = array(nb_trk(5,2), nb_trk(4,2), nb_trk(5,1), nb_trk(4,1));

    $rel = url();
    return <<<EOF
        <table class="bicol"
          style="font-weight:normal;text-align:center; border-left:0px; border-right:0px; margin-top:0.5em; width:100%; margin-left: 0; font-size: smaller;">
        <tr>
          <th style="width:33%">Valid</th>
          <th style="width:33%">Bugs</th>
          <th style="width:33%">Todo</th>
        </tr>
        <tr class="impair">
          <td><a href="$rel/admin/valider.php">$stats_req</a></td>
          <td>
            <a href="$rel/trackers/tracker_show.php?tr_id=2">
            <strong>{$nbtrk[0]}</strong> / {$nbtrk[1]}
            </a>
          </td>
          <td>
            <a href="$rel/trackers/tracker_show.php?tr_id=1">
            <strong>{$nbtrk[2]}</strong> / {$nbtrk[3]}
            </a>
          </td>
        </tr>
        </table>
EOF;
}
?>
