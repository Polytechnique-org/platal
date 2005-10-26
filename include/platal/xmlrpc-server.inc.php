<?php

/******************************************************************************
 *                                                                            *
 *  Original file can be found on http://xmlrpc-epi.sourceforge.net/          *
 *  in the module xmlrpc-epi-php v0.51 file samples/utils/utils.php           *
 *                                                                            *
 *                                                The Polytechnique.org TEAM  *
 *                                                                            *
 ******************************************************************************/

/*
  This file is part of, or distributed with, libXMLRPC - a C library for 
  xml-encoded function calls.

  Author: Dan Libby (dan@libby.com)
  Epinions.com may be contacted at feedback@epinions-inc.com
*/

/*  
  Copyright 2001 Epinions, Inc. 

  Subject to the following 3 conditions, Epinions, Inc.  permits you, free 
  of charge, to (a) use, copy, distribute, modify, perform and display this 
  software and associated documentation files (the "Software"), and (b) 
  permit others to whom the Software is furnished to do so as well.  

  1) The above copyright notice and this permission notice shall be included 
  without modification in all copies or substantial portions of the 
  Software.  

  2) THE SOFTWARE IS PROVIDED "AS IS", WITHOUT ANY WARRANTY OR CONDITION OF 
  ANY KIND, EXPRESS, IMPLIED OR STATUTORY, INCLUDING WITHOUT LIMITATION ANY 
  IMPLIED WARRANTIES OF ACCURACY, MERCHANTABILITY, FITNESS FOR A PARTICULAR 
  PURPOSE OR NONINFRINGEMENT.  

  3) IN NO EVENT SHALL EPINIONS, INC. BE LIABLE FOR ANY DIRECT, INDIRECT, 
  SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES OR LOST PROFITS ARISING OUT 
  OF OR IN CONNECTION WITH THE SOFTWARE (HOWEVER ARISING, INCLUDING 
  NEGLIGENCE), EVEN IF EPINIONS, INC.  IS AWARE OF THE POSSIBILITY OF SUCH 
  DAMAGES.    

*/

/* xmlrpc utilities (xu) 
 * author: Dan Libby (dan@libby.com)
 */

/* generic function to call an http server with post method */
function xu_query_http_post($request, $host, $uri, $port, $timeout, $user,
        $pass, $secure=false)
{
    $response_buf = '';
    if ($host && $uri && $port) {
	$content_len = strlen($request);
	$fsockopen   = $secure ? 'fsockopen_ssl' : 'fsockopen';
	$query_fd    = $fsockopen($host, $port, $errno, $errstr, 10);

	if ($query_fd) {

	    $auth = '';
	    if ($user) {
		$auth = 'Authorization: Basic ' .  base64_encode($user . ':' . $pass) . "\r\n";
	    }

	    $http_request =
		"POST $uri HTTP/1.0\r\n" .
		"Host: $host:$port\r\n" .
		$auth .
		"User-Agent: xmlrpc-epi-php/0.2 (PHP)\r\n" .
		"Content-Type: text/xml\r\n" .
		"Content-Length: $content_len\r\n" . 
		"Connection: Close\r\n" .
		"\r\n" .
		$request;

	    fputs($query_fd, $http_request, strlen($http_request));

	    $header_parsed = false;
	    while (!feof($query_fd)) {
		$line = fgets($query_fd, 4096);
		if (!$header_parsed) {
		    if ($line === "\r\n" || $line === "\n") {
			$header_parsed = 1;
		    }
		} else {
		    $response_buf .= $line;
		}
	    }

	    fclose($query_fd);
	}
    }

    return $response_buf;
}

function find_and_decode_xml($buf)
{
    if (strlen($buf)) {
        $xml_begin = substr($buf, strpos($buf, '<?xml'));
        if (strlen($xml_begin)) {
            $retval = xmlrpc_decode($xml_begin);
        }
    }
    return $retval;
}

 
/**
 * @param params         a struct containing 3 or more of these key/val pairs:
 * @param host		 remote host             (required)
 * @param uri		 remote uri	         (required)
 * @param port		 remote port             (required)
 * @param method         name of method to call
 * @param args	         arguments to send       (parameters to remote xmlrpc server)
 * @param timeout	 timeout in secs.        (0 = never)
 * @param user		 user name for authentication.  
 * @param pass		 password for authentication
 * @param secure	 secure. wether to use fsockopen_ssl. (requires special php build).
 */
function xu_rpc_http_concise($params) {
    $host = $uri = $port = $method = $args = null;
    $timeout = $user = $pass = $secure = null;

    extract($params);

    // default values
    if (!$port) {
        $port = 80;
    }
    if (!$uri) {
        $uri = '/';
    }
    if ($host && $uri && $port) {
        $request_xml  = xmlrpc_encode_request($method, $args);
        $response_buf = xu_query_http_post($request_xml, $host, $uri, $port,
                $timeout, $user, $pass, $secure);
        $retval       = find_and_decode_xml($response_buf);
   }
   return $retval;
}

?>
