#!/usr/bin/php5
<?php

require_once 'connect.db.inc.php';
require_once 'xorg.misc.inc.php';

// Fetches the list of unregistered users.
$users = XDB::iterRow(
    "SELECT  user_id, prenom, nom, promo
       FROM  auth_user_md5
      WHERE  hruid IS NULL");

// Creates missing human readable uids.
while (list($user_id, $prenom, $nom, $promo) = $users->next()) {
    $forlife = make_forlife($prenom, $nom, $promo);
    if (!XDB::execute("UPDATE auth_user_md5 SET hruid = {?} WHERE user_id = {?}", $forlife, $user_id)) {
        echo "WARNING: Duplicate forlife for user $user_id and forlife '$forlife'. Please check manually the entry.\n";
    }
}
?>
