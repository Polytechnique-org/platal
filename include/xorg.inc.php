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
require_once('globals.inc.php');
require_once('xorg/session.inc.php');
$globals = new PlatalGlobals('XorgSession');
XorgSession::init();

require_once dirname(__FILE__).'/../classes/page.php';

// {{{ class XorgPage

class XorgPage extends PlatalPage
{
    function XorgPage($tpl, $type = SKINNED)
    {
        $this->PlatalPage($tpl, $type);
    }

    function run()
    {
        global $globals;
        $this->assign('globals', $globals);
        $this->_run('skin/'.S::v('skin', 'default.tpl'));
    }
}

// }}}
// {{{ class XorgAdmin

/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XorgAdmin extends XorgPage
{
    // {{{ function XorgAdmin()

    function XorgAdmin($tpl, $type = SKINNED)
    {
        $this->XorgPage($tpl, $type);
        check_perms();
    }

    // }}}
}

// }}}

function _new_page($type, $tpl_name, $admin=false)
{
    global $page;
    if (!empty($admin)) {
        $page = new XorgAdmin($tpl_name, $type);
    } else {
        $page = new XorgPage($tpl_name, $type);
    }

    $page->assign('xorg_tpl', $tpl_name);
}

// {{{ function new_skinned_page()

function new_skinned_page($tpl_name)
{
    _new_page(SKINNED, $tpl_name);
}

// }}}
// {{{ function new_admin_page()

function new_admin_page($tpl_name)
{
    _new_page(SKINNED, $tpl_name, true);
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
