<?php

  /** mise à jour des abonnements
   * @param $_subscriptions ARRAY liste des forums auxquel l'utilisateur est abonné
   */

function update_subscriptions($_subscriptions) {
    global $globals;
    $uid = Session::getInt('uid');

    mysql_query("DELETE FROM {$globals->banana->table_prefix}abos WHERE uid='$uid'");
    if (!count($_subscriptions)) {
        return true;
    }
    // Récupération des fid
    $req = mysql_query("SELECT fid,nom FROM {$globals->banana->table_prefix}list");
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
            mysql_query("INSERT INTO {$globals->banana->table_prefix}list (nom) VALUES ('$g')");
            $fids[$g]=mysql_insert_id();
        }
    }
    // MAJ Abonnements
    foreach ($_subscriptions as $g) {
        mysql_query("REPLACE INTO {$globals->banana->table_prefix}abos (fid,uid) VALUES ('{$fids[$g]}','$uid')");
    }
}

?>
