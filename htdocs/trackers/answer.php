<?php
require('auto.prepend.inc.php');
new_skinned_page('trackers/answer.tpl', AUTH_COOKIE);
require('tracker.inc.php');

$tracker = new Tracker($_GET['tr_id']);

if(isset($_POST['a_sub']) && $tracker->post_perms_ok()) {
    $sql = "INSERT INTO trackers.followups
            SET         user_id='{$_SESSION['uid']}',
                        texte='{$_POST['a_text']}',
                        rq_id='{$_GET['rq_id']}'";
    $globals->db->query($sql);
    header("Location: show_rq.php?tr_id={$_GET['tr_id']}&rq_id={$_GET['rq_id']}");
    
}

$res = $globals->db->query("SELECT date,summary,texte
                            FROM   trackers.requests
                            WHERE  tr_id='{$_GET['tr_id']}' AND rq_id='{$_GET['rq_id']}'");

if(empty($tracker->id) || !$tracker->post_perms_ok() || !mysql_num_rows($res))
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
