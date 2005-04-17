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

// {{{ class XnetPage

class XnetPage extends PlatalPage
{
    // {{{ function XnetPage()

    function XnetPage($tpl, $type=SKINNED)
    {
        $this->PlatalPage($tpl, $type);
        if (Get::has('auth')) {
            $_SESSION['session']->doAuthX($this);
        }
    }

    // }}}
    // {{{ function run()

    function run()
    {
        $this->_run('xnet/skin.tpl');
    }

    // }}}
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
        $_SESSION['session']->doAuth($this);
    }
    
    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
