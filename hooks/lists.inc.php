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

// {{{ config HOOK
// {{{ class ListsConfig

class ListsConfig
{
    var $rpchost     = 'localhost';
    var $rpcport     = 4949;
    
    var $spool       = '/var/spool/platal/archives/';

    var $admin_owner = '';
    var $vhost_sep   = '_';
}

// }}}

function lists_config()
{
    global $globals;
    $globals->lists = new ListsConfig;
}

// }}}
// {{{ menu HOOK

function lists_menu()
{
    global $globals;
    $globals->menu->addPrivateEntry(XOM_SERVICES, 20, 'Listes de diffusion',   'listes/');
}

// }}}
// {{{ subscribe HOOK

function lists_subscribe($forlife, $uid, $promo, $password)
{
    require_once('lists.inc.php');
    $client =& lists_xmlrpc($uid, $password);
    $client->subscribe("promo$promo");
}

// }}}
 
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
