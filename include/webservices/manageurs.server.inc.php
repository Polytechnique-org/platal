<?php

require_once('webservices/manageurs.inc.php');

$error_mat = "You didn't provide me with a valid matricule number...";
$error_key = "You didn't provide me with a valid cipher key...";

/**
  le premier parametre doit etre le matricule
  le second parametre facultatif doit etre le numero de l'adresse voulue :
    -1 => on ne veut pas d'adresse
    0 => on veut toutes les adresses
    n => on veut l'adresse numero n
*/
function get_annuaire_infos($method, $params) {
  global $error_mat, $error_key;

  //verif du mdp
  if(!isset($params[0]) || ($params[0] != $GLOBALS['manageurs_pass'])){return false;}

  //si on a adresse == -1 => on ne recupère aucune adresse
  if(isset($params[2]) && ($params[2] == -1)) unset($params[2]);
  
  
  if( !empty($params[1]) ){ // on verifie qu'on a bien un matricule

     //on ne recupere pas les adresses inutilement
     if(!isset($params[2])){
        $res = mysql_query("SELECT a.mobile AS cell, a.naissance AS age
	                    FROM auth_user_md5 AS a
			    WHERE a.matricule = '".addslashes($params[1])."'");
     }
     else{
       $res = mysql_query("SELECT a.mobile AS cell, a.naissance AS age,
                                  adr.adr1, adr.adr2, adr.adr3,
				  adr.cp, adr.ville, adr.pays,
				  adr.tel, adr.fax
	                   FROM auth_user_md5 AS a
			   LEFT JOIN adresses AS adr ON(adr.uid = a.user_id)
			   WHERE a.matricule = '".addslashes($params[1])."' AND
			         NOT FIND_IN_SET('pro', adr.statut)
			   ORDER BY NOT FIND_IN_SET('active', adr.statut),
				    FIND_IN_SET('res-secondaire', adr.statut),
				    NOT FIND_IN_SET('courrier', adr.statut)");
     }

     //traitement des adresss si necessaire
     if(isset($params[2])){
       if(list($cell, $age, $adr['adr1'], $adr['adr2'], $adr['adr3'],
               $adr['cp'], $adr['ville'],
               $adr['pays'], $adr['tel'], $adr['fax']) = mysql_fetch_row($res)){
           $array['cell'] = $cell;
	   $array['age'] = $age;
	   $array['adresse'][] = $adr;

           //on clamp le numero au nombre d'adresses dispo
           $adresse = (int) $params[2];
	   if($adresse > mysql_num_rows($res)) $adresse = mysql_num_rows($res);

	   
           if($adresse != 1){//on ne veut pas la premiere adresse
	     $i = 2;
             while(list($cell, $age, $adr['adr1'], $adr['adr2'], $adr['adr3'],
                        $adr['cp'], $adr['ville'],
                        $adr['pays'], $adr['tel'], $adr['fax']) = mysql_fetch_row($res)){
	       if($adresse == $i){//si on veut cette adresse en particulier
                 $array['adresse'][0] = $adr;
		 break;
	       }
	       elseif($adresse == 0){//si on veut toutes les adresses
                 $array['adresse'][] = $adr;
	       }
	       $i++;
	     }
	   }
       }
       else{
         $array = false;
       }
     }
     else{ //cas où on ne veut pas d'adresse
       $array = mysql_fetch_array($res);
     }
     
     if ($array) { // on a bien eu un résultat : le matricule etait bon

       //on n'envoit que l'age à manageurs le format est YYYY-MM-DD 0123-56-89
       $year = (int) substr($array['age'],0,4);
       $month = (int) substr($array['age'],5,2);
       $day = (int) substr($array['age'],8,2);
       $age = (int) date('Y') - $year - 1;
       if(( $month < (int)date('m')) ||
          (($month == (int)date('m')) && ($day >= (int)date('d')))) $age += 1;
       $array['age'] = $age;

       //on commence le cryptage des donnees
       if(manageurs_encrypt_init($params[1]) == 1){//on a pas trouve la cle pour crypter
          $args = array("erreur" => 3, "erreurstring" => $error_key);
          $reply = xmlrpc_encode_request(NULL,$args);
       }
       else{
         $reply = manageurs_encrypt_array($array);
         manageurs_encrypt_close();
       }
     } else {//le matricule n'etait pas valide
       $args = array("erreur" => 2, "erreurstring" => $erreur_mat);
       $reply = xmlrpc_encode_request(NULL,$args);
     }
  } else {//le matricule n'etait pas en argument
     $args = array("erreur" => 1, "erreurstring" => $error_mat);
     $reply = xmlrpc_encode_request(NULL,$args);
  } 
  return $reply; 
} 

?>
