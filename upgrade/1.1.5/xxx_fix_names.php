#!/usr/bin/php5
<?php
require_once 'connect.db.inc.php';
require_once '../../include/name.func.inc.php';

$pids = XDB::fetchOneRow("SELECT  pid
                            FROM  profile_public_names
                           WHERE  firstname_ordinary = '0' OR lastname_marital = '0' OR lastname_ordinary = '0'");

XDB::execute("UPDATE  profile_public_names
                 SET  firstname_ordinary = ''
               WHERE  firstname_ordinary = '0'");
XDB::execute("UPDATE  profile_public_names
                 SET  lastname_ordinary = ''
               WHERE  lastname_ordinary = '0'");
XDB::execute("UPDATE  profile_public_names
                 SET  lastname_ordinary = ''
               WHERE  lastname_ordinary = '0'");

foreach ($pids as $pid) {
    $profile = Profile::get($pid);
    $item = XDB::fetchOneAssoc('SELECT  *
                                  FROM  profile_public_names
                                 WHERE  pid = {?}', $pid);
    update_display_names($profile, $item);
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
