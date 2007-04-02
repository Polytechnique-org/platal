<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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
require_once('globals.inc.php');
require_once('xnet/session.inc.php');
$globals = new PlatalGlobals('XnetSession');
XnetSession::init();

// {{{ function new_skinned_page()

function new_skinned_page($tpl_name, $refuse_access = false)
{
    global $page, $globals;
    require_once("xnet/page.inc.php");
    if (!$page instanceof XnetPage) {
        $page = new XnetPage($tpl_name, $type);
    } else {
        $page->changeTpl($tpl_name, $type);
    }
    if ($refuse_access) {
        $page->kill("Vous n'avez pas les droits suffisants pour accéder à cette page");
    }
}

// }}}
// {{{ function new_annu_page()

function new_annu_page($tpl_name)
{
    global $globals;
    new_skinned_page($tpl_name, 
                     !may_update()
                     && (!is_member()  || $globals->asso('pub') != 'public')
                     && $globals->asso('cat') != 'Promotions');
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
