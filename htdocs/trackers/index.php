<?php
require('auto.prepend.inc.php');
new_skinned_page('trackers/index.tpl', AUTH_COOKIE);
if(has_perms())
    header("Location: admin.php");

if(!$page->xorg_is_cached()) {
    // we know when a new tracker is added so we can trust cached version
    $sql = "SELECT tr_id,tr.texte AS tr_name,description,ml.short,ml.texte AS ml_name
            FROM      trackers.trackers AS tr 
            LEFT JOIN trackers.mail_lists AS ml USING(ml_id) 
            WHERE tr.bits NOT LIKE '%perso%' AND tr.perms!='admin'
            ORDER BY tr.texte";
    $page->mysql_assign($sql, 'trackers');
}

$page->run();
?>
