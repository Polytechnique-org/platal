<?php

  /** mise à jour des abonnements
   * @param $_subscriptions ARRAY liste des forums auxquel l'utilisateur est abonné
   */

function update_subscriptions($_subscriptions) {
    global $globals;
    $uid = Session::getInt('uid');

    $globals->xdb->execute("DELETE FROM {$globals->banana->table_prefix}abos WHERE uid={?}", $uid);
    if (!count($_subscriptions)) {
        return true;
    }
    
    // Récupération des fid
    $req  = $globals->xdb->iterRow("SELECT fid,nom FROM {$globals->banana->table_prefix}list");
    $fids =array();
    while (list($fid,$fnom) = $req->next()) {
        $fids[$fnom] = $fid;
    }

    // MAJ table de forums
    if (count(array_keys($fids))) {
        $diff = array_diff($_subscriptions,array_keys($fids));
    } else {
        $diff = $_subscriptions;
    }
    if (count($diff)) {
        foreach ($diff as $g) {
            $globals->xdb->execute("INSERT INTO {$globals->banana->table_prefix}list (nom) VALUES ({?})", $g);
            $fids[$g] = mysql_insert_id();
        }
    }
    // MAJ Abonnements
    foreach ($_subscriptions as $g) {
        $globals->xdb->execute("REPLACE INTO {$globals->banana->table_prefix}abos (fid,uid) VALUES ({?},{?})", $fids[$g], $uid);
    }
}

?>
