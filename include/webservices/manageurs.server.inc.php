<?php

require_once('webservices/manageurs.inc.php');

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
