<?php
/***************************************************************************
 *  Copyright (C) 2003-2004 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************
        $Id: auto.prepend.inc.php,v 1.23 2004-09-19 15:50:45 x2000habouzit Exp $
 ***************************************************************************/


ini_set('include_path', '/home/x2000habouzit/dev/diogenes/lib/:'.ini_get('include_path'));
require("config.xorg.inc.php") ;
require_once("xorg.common.inc.php");

function _new_page($type, $tpl_name, $tpl_head, $min_auth, $popup=false, $admin=false) {
    global $page;
    require_once("xorg.page.inc.php");
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

function new_simple_page($tpl_name, $min_auth, $popup=false, $tpl_head="") {
    global $page;
    _new_page(SKINNED, $tpl_name, $tpl_head, $min_auth, $popup);
    $page->assign('simple', true);
}

function new_nonhtml_page($tpl_name, $min_auth) {
    _new_page(NO_SKIN, $tpl_name, "", $min_auth, false);
}

function new_admin_page($tpl_name, $popup=false, $tpl_head="") {
    _new_page(SKINNED, $tpl_name, $tpl_head, AUTH_MDP, $popup, true);
}

function new_admin_table_editor($table,$idfield) {
    global $editor;
    new_admin_page('table-editor.tpl');
    require_once('xorg.table-editor.inc.php');
    $editor = new XOrgAdminTableEditor($table,$idfield);
}

?>
