<?php
require("auto.prepend.inc.php");
new_admin_page('admin/newsletter_archi.tpl');

if (!isset($_REQUEST['action'])) $_REQUEST['action'] = "";

switch ($_REQUEST['action']) {
    case "update":
        if (empty($_REQUEST['nl_id'])) {
            $globals->db->query("insert into newsletter set date='{$_REQUEST['nl_date']}', "
                    ."titre='{$_REQUEST['nl_titre']}', text='{$_REQUEST['nl_text']}'");
        } else {
            $globals->db->query("update newsletter set date='{$_REQUEST['nl_date']}', "
                    ."titre='{$_REQUEST['nl_titre']}', text='{$_REQUEST['nl_text']}' where id='{$_REQUEST['nl_id']}'");
        }
    break;

    case "edit":
        $res = $globals->db->query("select id, date, titre, text from newsletter where id='{$_REQUEST['nl_id']}'");
        $page->assign('nl', mysql_fetch_assoc($res));
    break;
    case "delete":
        $globals->db->query("delete from newsletter where id='{$_REQUEST['nl_id']}'");
}

$sql = "SELECT id,date,titre FROM newsletter ORDER BY date DESC";
$page->mysql_assign($sql, 'nl_list');
$page->run();
?>
