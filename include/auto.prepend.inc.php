<?php
ini_set('include_path', ".:..:./include:../include/:/home/x2000habouzit/dev/diogenes/lib/:/home/x2000habouzit/dev/smarty/");
require("xorg.common.inc.php");

function _new_page($type, $tpl_name, $tpl_head, $min_auth, $admin=false) {
    global $page;
    require("xorg.page.inc.php");
    if(!empty($admin)) {
        $page = new XorgAdmin($tpl_name, $type);
    } else switch($min_auth) {
        case AUTH_PUBLIC:
            $page = new XorgPage($tpl_name, $type);
            break;
        case AUTH_COOKIE:
            $page = new XorgCookie($tpl_name, $type);
        case AUTH_MDP:
            $page = new XorgAuth($tpl_name, $type);
    }

    $page->assign('xorg_head', $tpl_head);
    $page->assign('xorg_tpl', $tpl_name);

    $page->compile_check=true;
//    $page->caching=true; // XXX  note sure for now
}

function new_skinned_page($tpl_name, $min_auth, $tpl_head="") {
    _new_page(SKINNED, $tpl_name, $tpl_head, $min_auth);
}

function new_popup_page($tpl_name, $min_auth, $tpl_head="") {
    _new_page(SKINNED, $tpl_name, $tpl_head, $min_auth);
}

function new_admin_page($tpl_name, $tpl_head="") {
    require("xorg.page.inc.php");
    _new_page(SKINNED, $tpl_name, $tpl_head, AUTH_MDP, true);
}

?>
