<?php
require('auto.prepend.inc.php');
require('tracker.inc.php');

new_skinned_page('trackers/post.tpl', AUTH_MDP);

$tracker = new Tracker($_GET['tr_id']);

if(empty($tracker->id) || !$tracker->post_perms_ok())
    $page->failure();

if(empty($_POST['text']) || empty($_POST['sujet'])) {
    $page->assign('tracker', $tracker);
    $page->mysql_assign('SELECT * FROM trackers.states', 'states');
} else {
    $rq_id = $tracker->post($_POST['sujet'], $_POST['text'], $_POST['prio'], $_POST['statut']);
    header("Location: show_rq.php?tr_id={$_GET['tr_id']}&rq_id=$rq_id");
}

$page->run();   
?>
