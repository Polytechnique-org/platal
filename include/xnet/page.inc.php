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
 ***************************************************************************/

require_once('platal/page.inc.php');
require_once('xnet/smarty.plugins.inc.php');

// {{{ class XnetPage

class XnetPage extends PlatalPage
{
    // {{{ function XnetPage()

    function XnetPage($tpl, $type=SKINNED)
    {
        $this->PlatalPage($tpl, $type);
    }

    // }}}
    // {{{ function run()

    function run()
    {
        $this->_run('xnet/skin.tpl');
    }

    // }}}
    // {{{ function setType

    function setType($type)
    {
        $this->assign('xnet_type', strtolower($type));
    }

    // }}}
    // {{{ function useMenu

    function useMenu()
    {
        $this->assign('menu', true);
    }

    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        $this->register_function('list_all_my_groups', 'list_all_my_groups');
        $this->assign('it_is_xnet', true);
        if (Get::has('auth')) {
            $_SESSION['session']->doAuthX($this);
        }
    }
}

// }}}
// {{{ class XnetAuth

/** Une classe pour les pages nécessitant l'authentification.
 * (equivalent de controlauthentification.inc.php)
 */
class XnetAuth extends XnetPage
{
    // {{{ function XnetAuth()

    function XnetAuth($tpl, $type=SKINNED)
    {
        $this->XnetPage($tpl, $type);
    }

    // }}}
    // {{{ function doAuth()

    function doAuth()
    {
        parent::doAuth();
        $_SESSION['session']->doAuth($this);
    }
    
    // }}}
}

// }}}
// {{{ class XnetAdmin

/** Une classe pour les pages réservées aux admins (authentifiés!).
 */
class XnetAdmin extends XnetAuth
{
    // {{{ function XnetAdmin()
    
    function XnetAdmin($tpl, $type=SKINNED)
    {
        $this->XnetAuth($tpl, $type);
        check_perms();
    }
    
    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
