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
    $Id: menu.inc.php,v 1.2 2004/11/23 20:56:43 x2000habouzit Exp $
 ***************************************************************************/

// {{{ defines

define('XOM_NO',0);
define('XOM_CUSTOM',   "Personnaliser");
define('XOM_SERVICES', "Services");
define('XOM_GROUPS',   "Communauté X");
define('XOM_ADMIN',    "***");

define('XOM_US',       'Polytechniciens');
define('XOM_EXT',      'Visiteurs');

define('XOM_INFOS',    "Informations");

// }}}
// {{{ class XOrgMenu

/**
 * Class used for the left menu construction
 *
 * @category XOrgCore
 * @package  XOrgCore
 * @author   Pierre Habouzit <pierre.habouzit@m4x.org>
 * @version  $Id: menu.inc.php,v 1.2 2004/11/23 20:56:43 x2000habouzit Exp $
 * @access   public
 */

class XOrgMenu
{
    // {{{ properties

    var $_ext = Array();
    var $_int = Array();
    
    // }}}
    // {{{ constructor

    function XOrgMenu($path)
    {
        global $globals;
        
        $this->_int[XOM_NO]       = Array();
        $this->_int[XOM_CUSTOM]   = Array();
        $this->_int[XOM_SERVICES] = Array();
        $this->_int[XOM_GROUPS]   = Array();
        $this->_int[XOM_INFOS]    = Array();
        $this->_int[XOM_ADMIN]    = Array();

        $this->_ext[XOM_US]       = Array();
        $this->_ext[XOM_EXT]      = Array();
        $this->_ext[XOM_INFOS]    = Array();
        
        foreach (glob($path.'*.menu.inc.php') as $inc) {
            require_once($inc);
        }
    }

    // }}}
    // {{{ function addPublicEntry

    function addPublicEntry($head, $prio, $text, $url)
    {
        $this->_ext[$head][] = Array($prio, 'text' => $text, 'url' => $url);
    }

    // }}}
    // {{{ function addPrivateEntry

    function addPrivateEntry($head, $prio, $text, $url)
    {
        $this->_int[$head][] = Array($prio, 'text' => $text, 'url' => $url);
    }

    // }}}
    // {{{ function menu()

    function menu()
    {
        $res = logged() ? $this->_int : $this->_ext;
        if (identified()) {
            $res[XOM_NO][] = Array(0, 'text' => 'Déconnexion', 'url' => 'deconnexion.php');
        }
        if (!has_perms()) {
            unset($res[XOM_ADMIN]);
        }
        foreach (array_keys($res) as $key) {
            if (empty($res[$key])) {
                unset($res[$key]);
            } else {
                sort($res[$key]);
            }
        }
        return $res;
    }

    // }}}
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
