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

require_once('platal/globals.inc.php');

// {{{ class XorgGlobals

class XorgGlobals extends PlatalGlobals
{
    function XorgGlobals()
    {
        $this->PlatalGlobals('XorgSession');
    }

    function init()
    {
        global $globals;
        require_once('xorg/hook.inc.php');
        require_once('xorg/menu.inc.php');

        $globals       = new XorgGlobals;
        $globals->core = new CoreConfig;
        $globals->root = dirname(dirname(dirname(__FILE__)));
        $globals->hook = new XOrgHook();
        $globals->menu = new XOrgMenu();

        $globals->hook->config(null);

        $globals->read_config();

        $globals->hook->menu(null);

        $globals->dbconnect();
        if ($globals->debug & 1) {
            $globals->db->trace_on();
        }
    }
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
