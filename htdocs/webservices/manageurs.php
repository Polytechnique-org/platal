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
        $Id: manageurs.php,v 1.2 2004-11-09 21:42:34 x2000chevalier Exp $
 ***************************************************************************/

require("auto.prepend.inc.php");


$error_msg = "You didn't provide me with a valid matricule number...";

function get_annuaire_infos($method, $params) { 
    $cipher_key = "super toto !";
    if (!empty($params[0])) { 
        $res = mysql_query("SELECT nom AS nom, epouse AS nom_patro, prenom AS prenom, promo AS prenom, deces=0 AS decede, mobile AS cell FROM auth_user_md5 WHERE matricule = '".addslashes($params[0])."'");
	if ($array = mysql_fetch_array($res)) {
	    // then it's perfectly fine ! we just have to use a good cypher...
	    $td = mcrypt_module_open('tripledes', '', 'ecb', '');
	    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	    mcrypt_generic_init($td, $cipher_key.$params[0], $iv);
	    foreach ( $array as $key => $value ) { 
	        $reply[base64_encode(mcrypt_generic($td, $key)] = base64_encode(mcrypt_generic($td, $value));
	    } 
	    mcrypt_generic_deinit($td);
	    mcrypt_module_close($td);
	} else {
            $args = array("faultCode" => 1, "faultString" => $error_msg);
	    $reply = xmlrpc_encode_request(NULL,$args);
	}
    } else {
        $args = array("faultCode" => 1, "faultString" => $error_msg);
	$reply = xmlrpc_encode_request(NULL,$args);
    } 
    return $reply; 
} 

$server = xmlrpc_server_create();

xmlrpc_server_register_method($server, "getAnnuaireInfos", "get_annuaire_infos");

$request = $GLOBALS['HTTP_RAW_POST_DATA'];

$response = xmlrpc_server_call_method($server, $request, null);
header('Content-Type: text/xml');
print $response;

xmlrpc_server_destroy($server);

?>
