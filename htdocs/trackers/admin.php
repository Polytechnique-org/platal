<?php
require('auto.prepend.inc.php');
require('tracker.inc.php');

if(isset($_POST['action'])) {
    switch($_POST['action']) {
    // insertion ou création du tracker dans le formulaire
        case 'update':
            new_admin_page('trackers/admin.tpl');
            if(!empty($_POST['short']) && !empty($_POST['texte'])) {
                $globals->db->query("INSERT INTO trackers.mail_lists
                                     SET short='{$_POST['short']}',texte='{$_POST['texte']}'");
                $mlid = mysql_insert_id();
            } else
                $mlid = clean_request('mlid');
            $bits = new Flagset();
            if(!empty($_POST['nomail']))
                $bits->addFlag('no_mail');
            if(!empty($_POST['perso']))
                $bits->addFlag('perso');
            tracker_update($_POST['name'], $_POST['desc'], $_POST['perms'],
                    $mlid, $bits, $_POST['pris'], $_POST['trid']);

            $page->xorg_clear_cache('trackers/admin.tpl');
            $page->xorg_clear_cache('trackers/index.tpl');
            // TODO
            break;
    // édition des propriétés d'un tracker ou nouveau tracker
        case 'edit':
            new_admin_page('trackers/edit.tpl');
            $sql = "SELECT ml_id,short
                    FROM trackers.mail_lists
                    ORDER BY short";
            $page->mysql_assign($sql, 'ml_list');
            $tr_id = clean_request('trid');
            $page->assign('tracker',new Tracker($tr_id));
            $page->run();
    // suppression d'un tracker
        case 'del':
            new_admin_page('trackers/admin.tpl');
            $page->xorg_clear_cache('trackers/admin.tpl');
            $page->xorg_clear_cache('trackers/index.tpl');
            $tracker = new Tracker($_POST['trid']);
            $tracker -> destroy();
            break;
    // nettoyage BD
        case 'clean':
            new_admin_page('trackers/admin.tpl');
            tracker_clean_bd();
    }
} else 
    new_admin_page('trackers/admin.tpl');

if(!$page->xorg_is_cached()) {
    // we know when a new tracker is added so we can trust cached version
    $sql = "SELECT tr_id,tr.texte AS tr_name,description,ml.short,ml.texte AS ml_name
            FROM      trackers.trackers AS tr 
            LEFT JOIN trackers.mail_lists AS ml USING(ml_id) 
            WHERE tr.bits NOT LIKE '%perso%'
            ORDER BY tr.texte";
    $page->mysql_assign($sql, 'trackers');

    $sql = "SELECT tr_id,tr.texte AS tr_name,description,ml.short,ml.texte AS ml_name
            FROM       trackers.trackers AS tr 
            LEFT JOIN trackers.mail_lists AS ml USING(ml_id) 
            WHERE tr.bits LIKE '%perso%'
            ORDER BY tr.texte";
    $page->mysql_assign($sql, 'persos');
}

$page->run();
?>
