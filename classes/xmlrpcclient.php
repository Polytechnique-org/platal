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

/* taken from : http://fr2.php.net/xml-rpc
 * Author mboeren@php.net
 *
 * Usage:
 * $client = new xmlrpc_client("http://localhost:7080");
 * print $client->echo('x')."\n";
 * print $client->add(1, 3)."\n";
 */

class XmlrpcClient
{
    private $url;
    private $urlparts;

    public function __construct($url)
    {
        $this->url = $url;
        $this->urlparts = parse_url($this->url);

        if (empty($this->urlparts['port'])) {
            $this->urlparts['port'] = 80;
        }

        if (empty($this->urlparts['path'])) {
            $this->urlparts['path'] = '/';
        }
    }

    private function http_post($request)
    {
        $host = $path = $port = $user = $pass = null;
        extract($this->urlparts);

        if ($scheme == 'https') {
            $host = 'ssl://'.$host;
        }

        $query_fd    = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$query_fd)
            return null;

        $auth = '';
        if ($user) {
            $auth = 'Authorization: Basic ' . base64_encode("$user:$pass") . "\r\n";
        }

        $content_len = strlen($request);
        $http_request =
            "POST $path HTTP/1.0\r\n" .
            $auth .
            "Content-Type: text/xml\r\n" .
            "Content-Length: $content_len\r\n" .
            "Connection: Close\r\n" .
            "Host: $host:$port\r\n" .
            "\r\n" .
            $request;

        fputs($query_fd, $http_request, strlen($http_request));

        $buf = '';
        while (!feof($query_fd)) {
            $buf .= fread($query_fd, 8192);
        }

        fclose($query_fd);
        return $buf;
    }

    private function find_and_decode_xml($buf)
    {
        $pos = strpos($buf, '<?xml');
        if ($pos !== false) {
            return xmlrpc_decode(substr($buf, $pos));
        }
        trigger_error("Cannot parse XML\n".$buf);
    }

    public function __call($method, $args)
    {
        $query  = xmlrpc_encode_request($method, $args);
        $answer = $this->http_post($query, $this->urlparts);
        $result = $this->find_and_decode_xml($answer);

        if (is_array($result) && isset($result['faultCode'])) {
            trigger_error("Error in xmlrpc call $function\n".
                          "  code   : {$result['faultCode']}\n".
                          "  message: {$result['faultString']}\n");
            return null;
        }
        return $result;
    }
}

// vim:set et sw=4 sts=4 sws=4:
?>
