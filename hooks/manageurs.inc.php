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

// {{{ config HOOK
// {{{ class ManageursConfig

class ManageursConfig
{
    var $authorized_ips = '129.104.30.32 129.104.30.33 213.251.145.200';
    var $manageurs_cipher_key = '';
    var $manageurs_pass = '';
}

// }}}

function manageurs_config()
{
    global $globals;
    $globals->manageurs = new ManageursConfig;
}

// }}}
 
// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
