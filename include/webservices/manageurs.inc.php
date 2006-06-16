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


$tripledes = '';


function manageurs_encrypt_init($id_assoce){
  global $tripledes, $globals;
  $cipher_key = $globals->manageurs->manageurs_cipher_key;
  if(!$tripledes){
    if(empty($cipher_key)){
      return 1;
    }
    $tripledes = mcrypt_module_open('tripledes', '', 'ecb', '');
    $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($tripledes), MCRYPT_RAND);
    mcrypt_generic_init($tripledes, $cipher_key.$id_assoce, $iv);
    return 0;
  }
}

function manageurs_encrypt_close(){
  global $tripledes;
  if($tripledes){
    mcrypt_generic_deinit($tripledes);
    mcrypt_module_close($tripledes);
    $tripledes = '';
  }
}

function manageurs_encrypt($message){
  global $tripledes;
  return base64_encode(mcrypt_generic($tripledes, $message));
}

function manageurs_decrypt($message){
  global $tripledes;
  return trim(mdecrypt_generic($tripledes, base64_decode($message)));
}

function manageurs_encrypt_array($array){
  foreach($array as $key => $value){
    if(is_array($value)){
      $result[manageurs_encrypt($key)] = manageurs_encrypt_array($value);
    }
    else{
      $result[manageurs_encrypt($key)] = manageurs_encrypt($value);
    }
  }
  return $result;
}

function manageurs_decrypt_array($array){
  foreach($array as $key => $value){
    if(is_array($value)){
      $result[manageurs_decrypt($key)] = manageurs_decrypt_array($value);
    }
    else{
      $result[manageurs_decrypt($key)] = manageurs_decrypt($value);
    }
  }
  return $result;
}

?>
