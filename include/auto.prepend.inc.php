<?php
require("config.xorg.inc.php") ;
ini_set('include_path', ".:..:{$globals->root}/include/:{$globals->root}/configs/:$globals->libroot") ;
setlocale(LC_TIME, "fr_FR");
require("xorg.common.inc.php");

function _new_page($type, $tpl_name, $tpl_head, $min_auth, $popup=false, $admin=false) {
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
            break;
        case AUTH_MDP:
            $page = new XorgAuth($tpl_name, $type);
    }

    $page->assign('xorg_head', $tpl_head);
    $page->assign('xorg_tpl', $tpl_name);
    if($popup)
        $page->assign('popup_enable', true);
}

function new_skinned_page($tpl_name, $min_auth, $popup=false, $tpl_head="") {
    _new_page(SKINNED, $tpl_name, $tpl_head, $min_auth, $popup);
}

function new_popup_page($tpl_name, $min_auth, $popup=false, $tpl_head="") {
    _new_page(SKINNED, $tpl_name, $tpl_head, $min_auth, $popup);
}

function new_admin_page($tpl_name, $popup=false, $tpl_head="") {
    _new_page(SKINNED, $tpl_name, $tpl_head, AUTH_MDP, $popup, true);
}

?>
