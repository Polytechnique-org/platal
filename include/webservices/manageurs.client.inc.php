<?php

require_once('webservices/manageurs.inc.php');
require_once('platal/xmlrpc-client.inc.php');

function get_annuaire_infos($amicale, $id_assoce, $adresse){

  $url = '';

  $url = 'http://www.polytechniciens.org:80/manageurs.php';
    //decommenter pour ajouter un webservice chez l'AX :
   // return array('adresse' => array(0 => array('adr1' => 'test AX', 'city' => 'Trou perdu')));

  $client = new xmlrpc_client($url);
 
  global $globals;
  if($array = $client->get_annuaire_infos($globals->webservice->pass, $id_assoce, $adresse)){
    
    if( is_string($array) ){
      $erreur = xmlrpc_decode($array);
      echo $erreur['erreurstring']."\n";
      return $erreur['erreur'];
    }
    else{
    manageurs_encrypt_init($id_assoce);
    $reply = manageurs_decrypt_array($array);
    manageurs_encrypt_close();
    return $reply;
    }
  }
  else return false;
}

?>
