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
require_once('xnet/globals.inc.php');
require_once('xnet/session.inc.php');
XnetGlobals::init();
XnetGlobals::setlocale();
XnetSession::init();

// {{{ function new_skinned_page()

function new_page($tpl_name, $min_auth)
{
    global $page,$globals;
    require_once("xnet/page.inc.php");
    switch($min_auth) {
        case AUTH_PUBLIC:
            $page = new XnetPage($tpl_name, $type);
            break;

        default:
            $page = new XnetAuth($tpl_name, $type);
    }
    $page->assign('xorg_tpl', $tpl_name);
}

function new_skinned_page($tpl_name, $min_auth)
{
    return new_page($tpl_name, $min_auth);
}

// }}}
function new_identification_page()
{
    new_page('', AUTH_MDP);
    global $page;
    $page->useMenu();
}

// {{{ function new_group_page()

function new_group_page($tpl_name)
{
    global $page,$globals;
    require_once("xnet/page.inc.php");
    $page = new XnetGroupPage($tpl_name);
    $page->assign('xorg_tpl', $tpl_name);
}

// }}}
// {{{ function new_groupadmin_page()

function new_groupadmin_page($tpl_name)
{
    global $page,$globals;
    require_once("xnet/page.inc.php");
    $page = new XnetGroupAdmin($tpl_name);
    $page->assign('xorg_tpl', $tpl_name);
}

// }}}
// {{{ function new_admin_page()

function new_admin_page($tpl_name)
{
    global $page,$globals;
    require_once("xnet/page.inc.php");
    $page = new XnetAdmin($tpl_name);
    $page->assign('xorg_tpl', $tpl_name);
}

// }}}
// {{{ function new_nonhtml_page()

function new_nonhtml_page($tpl_name)
{
    global $page, $globals;
    require_once("xnet/page.inc.php");
    $page = new XnetGroupPage($tpl_name, NO_SKIN);
    $page->assign('xorg_tpl', $tpl_name);
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
