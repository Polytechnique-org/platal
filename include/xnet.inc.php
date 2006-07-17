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

function new_page($tpl_name, $type = SKINNED)
{
    global $page, $globals;
    require_once("xnet/page.inc.php");
    $page = new XnetPage($tpl_name, $type);
    $page->assign('xorg_tpl', $tpl_name);
}

function new_skinned_page($tpl_name)
{
    return new_page($tpl_name);
}

// }}}

function new_identification_page()
{
    global $page;

    new_page('');
    $page->doAuth(true);
    $page->useMenu();
}

// {{{ function new_group_page()

function new_group_page($tpl_name)
{
    global $page, $globals;

    new_page($tpl_name);

    $page->doAuth(true);
    if (!is_member() && !S::has_perms()) {
        $page->kill("You have not sufficient credentials");
    }

    $page->useMenu();
    $page->assign('asso', $globals->asso());
    $page->setType($globals->asso('cat'));
}

// }}}
// {{{ function new_groupadmin_page()

function new_groupadmin_page($tpl_name)
{
    global $page, $globals;

    new_page($tpl_name);

    if (!may_update()) {
        $page->kill("You have not sufficient credentials");
    }

    $page->useMenu();
    $page->assign('asso', $globals->asso());
    $page->setType($globals->asso('cat'));
}

// }}}
// {{{ function new_admin_page()

function new_admin_page($tpl_name)
{
    global $page, $globals;

    new_page($tpl_name);

    check_perms();

    $page->useMenu();
    if ($globals->asso('cat')) {
        $page->assign('asso', $globals->asso());
        $page->setType($globals->asso('cat'));
    }
}

// }}}
// {{{ function new_nonhtml_page()

function new_nonhtml_page($tpl_name)
{
    global $page, $globals;

    new_page($tpl_name, NO_SKIN);

    $page->doAuth(true);
    if (!is_member() && !S::has_perms()) {
        $page->kill("You have not sufficient credentials");
    }

    $page->useMenu();
    $page->assign('asso', $globals->asso());
    $page->setType($globals->asso('cat'));
}

// }}}
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
