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

require_once 'platal/xmlrpc-server.inc.php';

/* taken from : http://fr2.php.net/xml-rpc
 * Author mboeren@php.net
 *
 * Usage:
 * $client = new xmlrpc_client("http://localhost:7080");
 * print $client->echo('x')."\n";
 * print $client->add(1, 3)."\n";
 */

class xmlrpc_client
{
    var $url;
    var $urlparts;

    function xmlrpc_client($url)
    {
        $this->url = $url;
        $this->urlparts = parse_url($this->url);
        foreach (array('scheme', 'host', 'user', 'pass', 'path', 'query', 'fragment') as $part) {
            if (!isset($this->urlparts[$part])) { 
                $this->urlparts[$part] = null;
            }
        }
    }

    function __call($function, $arguments, &$return)
    {
        $requestprms['host']    = $this->urlparts['host'];
        $requestprms['port']    = $this->urlparts['port'];
        $requestprms['uri']     = $this->urlparts['path'];
        $requestprms['user']    = $this->urlparts['user'];
        $requestprms['pass']    = $this->urlparts['pass'];
        $requestprms['method']  = $function;
        $requestprms['args']    = $arguments;
        $requestprms['timeout'] = 0;
        $requestprms['secure']  = 0;

        $result = xu_rpc_http_concise($requestprms);
        if (is_array($result) && isset($result['faultCode'])) {
            print('Error in xmlrpc call \''.$function.'\''."\n");
            print('  code  : '.$result['faultCode']."\n");
            print('  message: '.$result['faultString']."\n");
            return false;
        }
        $return = $result;
        return true;
    }

}

overload('xmlrpc_client');

// vim:set et sw=4 sts=4 sws=4:
?>
