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

// {{{ import class definitions

require_once("xml-rpc-client.inc.php");

// }}}
// {{{ function lists_xmlrpc

function &lists_xmlrpc($uid, $pass, $fqdn=null)
{
    global $globals;

    $dom = empty($fqdn) ? $globals->mail->domain : $fqdn;
    $url = "http://$uid:$pass@{$globals->lists->rpchost}:{$globals->lists->rpcport}/$dom";
    $client = new xmlrpc_client($url);
    return $client;
}

// }}}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
?>
