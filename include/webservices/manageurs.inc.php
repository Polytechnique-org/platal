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
        $Id: manageurs.inc.php,v 1.1 2004-11-11 11:46:11 x2000coic Exp $
 ***************************************************************************/

$error_mat = "You didn't provide me with a valid matricule number...";
$error_key = "You didn't provide me with a valid cipher key...";

$tripledes = '';


function manageurs_encrypt_init($id_assoce){
  global $tripledes;
  if(!$tripledes){
    if(empty($GLOBALS['manageurs_cipher_key'])){
      return 1;
    }
    $tripledes = mcrypt_module_open('tripledes', '', 'ecb', '');
    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($tripledes), MCRYPT_RAND);
    mcrypt_generic_init($tripledes, $GLOBALS['manageurs_cipher_key'].$id_assoce, $iv);
    return 0;
  }
}

function manageurs_encrypt_close(){
  global $tripledes;
  if($tripledes){
    mcrypt_generic_deinit($tripledes);
    mcrypt_module_close($tripledes);
  }
}

function manageurs_encrypt($message){
  global $tripledes;
  return base64_encode(mcrypt_generic($tripledes, $message));
}

function manageurs_decrypt($message){
  global $tripledes;
  return mdecrypt_generic($tripledes, base64_decode($message));
}

function manageurs_encrypt_array($array){
  foreach($array as $key => $value){
    $result[manageurs_encrypt($key)] = manageurs_encrypt($value);
  }
  return $result;
}

function manageurs_decrypt_array($array){
  foreach($array as $key => $value){
    $result[manageurs_decrypt($key)] = manageurs_decrypt($value);
  }
  return $result;
}


function get_annuaire_infos($method, $params) { 
    if (!empty($params[0])) {
        $res = mysql_query("SELECT nom AS nom, epouse AS nom_patro, prenom AS prenom, promo AS prenom, deces=0 AS decede, mobile AS cell FROM auth_user_md5 WHERE matricule = '".addslashes($params[0])."'");
	if ($array = mysql_fetch_array($res)) {
	    // then it's perfectly fine ! we just have to use a good cypher...
	    
	    if(manageurs_encrypt_init($params[0]) == 1){
	      $args = array("faultCode" => 1, "faultString" => $error_key);
              $reply = xmlrpc_encode_request(NULL,$args);
	    }
	    else{
	      $reply = manageurs_encrypt_array($array);

	      manageurs_encrypt_close();
	    }
	} else {
            $args = array("faultCode" => 1, "faultString" => $error_mat);
	    $reply = xmlrpc_encode_request(NULL,$args);
	}
    } else {
        $args = array("faultCode" => 1, "faultString" => $error_mat);
	$reply = xmlrpc_encode_request(NULL,$args);
    } 
    return $reply; 
} 

?>
