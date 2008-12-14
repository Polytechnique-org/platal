#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';

// Fetches the list of unregistered users.
$users = XDB::iterRow(
    "SELECT  user_id, prenom, nom, promo
       FROM  auth_user_md5
      WHERE  hruid IS NULL");

// Creates missing human readable uids.
while (list($user_id, $prenom, $nom, $promo) = $users->next()) {
    $forlife = make_forlife($prenom, $nom, $promo);
    $hruid = XDB::query('SELECT * FROM auth_user_md5 WHERE hruid = {?} AND user_id != {?}', $forlife, $user_id);
    if ($hruid->numRows() > 0) {
        echo "WARNING: Duplicate forlife for user $user_id and forlife '$forlife'. Please check manually the entry.\n";
    } else {
        XDB::execute('UPDATE auth_user_md5 SET hruid = {?} WHERE user_id = {?}', $forlife, $user_id);
    }
}
?>
