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

require_once('platal/page.inc.php');

// {{{ class XorgPage

class XorgPage extends PlatalPage
{
    // {{{ function XorgPage()

    function XorgPage($tpl, $type=SKINNED)
    {
        $this->PlatalPage($tpl, $type);
    }

    // }}}
    // {{{ function run()

    function run()
    {
        global $globals;
        if ($this->_page_type != NO_SKIN) {
            $this->assign('menu', $globals->menu->menu());
        }
        $this->_run('skin/'.Session::get('skin'));
    }

    // }}}
}

// }}}
// {{{ class XOrgAuth

/** Une classe pour les pages nécessitant l'authentification.
 * (equivalent de controlauthentification.inc.php)
 */
class XorgAuth extends XorgPage
{
    // {{{ function XorgAuth()

    function XorgAuth($tpl, $type=SKINNED)
    {
        $this->XorgPage($tpl, $type);
    }

    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        $_SESSION['session']->doAuth($this);
    }
    
    // }}}
}

// }}}
// {{{ class XorgCookie

/** Une classe pour les pages nécessitant l'authentification permanente.
 * (equivalent de controlpermanent.inc.php)
 */
class XorgCookie extends XorgPage
{
    // {{{ function XorgCookie()
    
    function XorgCookie($tpl, $type=SKINNED)
    {
        $this->XorgPage($tpl, $type);
    }
    
    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        $_SESSION['session']->doAuthCookie($this);
    }
    
    // }}}
}

// }}}
// {{{ class XorgAdmin

/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XorgAdmin extends XorgAuth
{
    // {{{ function XorgAdmin()
    
    function XorgAdmin($tpl, $type=SKINNED)
    {
        $this->XorgAuth($tpl, $type);
    }
    
    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
