<?php
require('auto.prepend.inc.php');
new_skinned_page('trackers/show_rq.tpl', AUTH_COOKIE);
require('tracker.inc.php');

$tracker = new Tracker($_GET['tr_id']);

$res = $globals->db->query("SELECT r.*, a.username, b.username AS admin, s.texte AS state
                            FROM trackers.requests    AS r
                            LEFT JOIN trackers.states AS s USING(st_id)
                            LEFT JOIN auth_user_md5   AS a ON(r.user_id=a.user_id)
                            LEFT JOIN auth_user_md5   AS b ON(r.admin_id = b.user_id)
                            WHERE tr_id='{$_GET['tr_id']}' AND rq_id='{$_GET['rq_id']}'");

if(empty($tracker->id) || !$tracker->read_perms_ok() || !mysql_num_rows($res))
    $page->failure();

$request = mysql_fetch_assoc($res);
mysql_free_result($res);

$page->assign('request', $request);
$page->assign('tracker', $tracker);

$sql = "SELECT *, username
        FROM      trackers.followups 
        LEFT JOIN auth_user_md5 USING(user_id)
        WHERE rq_id='{$_GET['rq_id']}'";
$page->mysql_assign($sql, 'fups');

$page->run();   
?>
