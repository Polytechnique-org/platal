<?php
require('auto.prepend.inc.php');
new_skinned_page('trackers/show.tpl', AUTH_COOKIE);
require('tracker.inc.php');

$tracker = new Tracker($_GET['tr_id']);

if(empty($tracker->id) || !$tracker->read_perms_ok())
    $page->failure();

$page->assign('tracker', $tracker);

$sql = "SELECT r.date,r.summary,r.pri,r.rq_id, a.username
        FROM      trackers.requests AS r
        LEFT JOIN auth_user_md5     AS a ON(r.admin_id=a.user_id)
        WHERE tr_id = {$tracker->id}
        ORDER BY pri DESC, r.date ASC";
$page->mysql_assign($sql, 'requests');

$page->run();   
?>
