<?php
/***************************************************************************
 *  Copyright (C) 2003-2006 Polytechnique.org                              *
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
 ***************************************************************************/
require_once('platal.inc.php');
require_once('xorg/globals.inc.php');
require_once('xorg/session.inc.php');
XorgGlobals::init();
XorgGlobals::setlocale();
XorgSession::init();

// {{{ function _new_page()

function _new_page($type, $tpl_name, $min_auth, $admin=false)
{
    global $page,$globals;
    require_once("xorg/page.inc.php");
    if ($min_auth == AUTH_PUBLIC && Env::get('force_login') == '1')
        $min_auth = AUTH_COOKIE;
    if (!empty($admin)) {
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

    $page->assign('xorg_tpl', $tpl_name);
}

// }}}
function new_identification_page()
{
    _new_page(SKINNED, '', AUTH_MDP);
}
// {{{ function new_skinned_page()

function new_skinned_page($tpl_name, $min_auth)
{
    _new_page(SKINNED, $tpl_name, $min_auth);
}

// }}}
// {{{ function new_simple_page()

function new_simple_page($tpl_name, $min_auth)
{
    global $page;
    _new_page(SKINNED, $tpl_name, $min_auth);
    $page->assign('simple', true);
}

// }}}
// {{{ function new_nonhtml_page()

function new_nonhtml_page($tpl_name, $min_auth)
{
    _new_page(NO_SKIN, $tpl_name, $min_auth, false);
}

// }}}
// {{{ function new_admin_page()

function new_admin_page($tpl_name)
{
    _new_page(SKINNED, $tpl_name, AUTH_MDP, true);
}

// }}}
// {{{ function new_admin_table_editor()

function new_admin_table_editor($table, $idfield, $idedit=false)
{
    array_walk($_GET, 'unfix_gpc_magic');
    array_walk($_POST, 'unfix_gpc_magic');
    array_walk($_REQUEST, 'unfix_gpc_magic');

    global $editor;
    new_admin_page('table-editor.tpl');
    require_once('xorg.table-editor.inc.php');
    $editor = new XOrgAdminTableEditor($table,$idfield,$idedit);
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
