<?php

  /** mise à jour des abonnements
   * @param $_subscriptions ARRAY liste des forums auxquel l'utilisateur est abonné
   */

function update_subscriptions($_subscriptions) {
  mysql_query("DELETE FROM forums.abos WHERE uid='{$_SESSION['uid']}'");
  if (!count($_subscriptions)) {
    return true;
  }
  // Récupération des fid
  $req = mysql_query("SELECT fid,nom FROM forums.list");
  $fids=array();
  while (list($fid,$fnom)=mysql_fetch_row($req)) {
    $fids[$fnom]=$fid;
  }
  // MAJ table de forums
  if (count(array_keys($fids))) {
    $diff = array_diff($_subscriptions,array_keys($fids));
  } else {
    $diff = $_subscriptions;
  }
  if (count($diff)) {
    foreach ($diff as $g) {
      mysql_query("INSERT INTO forums.list (nom) VALUES ('$g')");
      $fids[$g]=mysql_insert_id();
    }
  }
  // MAJ Abonnements
  foreach ($_subscriptions as $g) {
    mysql_query("REPLACE INTO forums.abos (fid,uid) VALUES "
        ."('{$fids[$g]}','{$_SESSION['uid']}')");
  }
}

?>
