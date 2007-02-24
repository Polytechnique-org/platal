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

    IL NE FAUT PAS CHANGER LES NOMS DES CHAMPS DE ADRESSES
    S'IL Y A DES MODIFS A FAIRE VOIR AVEC MANAGEURS admin@manageurs.com
*/
function get_annuaire_infos($method, $params) {
    global $error_mat, $error_key, $globals;

      

    //verif du mdp
    if(!isset($params[0]) || ($params[0] != $globals->manageurs->manageurs_pass)){return false;}
    //si on a adresse == -1 => on ne recupère aucune adresse
    if(isset($params[2]) && ($params[2] == -1)) unset($params[2]);


    if( !empty($params[1]) ){ // on verifie qu'on a bien un matricule

        //on ne recupere pas les adresses inutilement
        if(!isset($params[2])){
            $res = XDB::iterRow(
                    "SELECT  q.profile_mobile AS cell, a.naissance AS age
                    FROM  auth_user_md5 AS a
                    INNER JOIN auth_user_quick AS q USING (user_id)
                    WHERE  a.matricule = {?}", $params[1]);
            $array = $res->next();
        }
        else{
            $res = XDB::iterRow(
                 "SELECT     q.profile_mobile AS cell, a.naissance AS age,
                             adr.adr1, adr.adr2, adr.adr3,
                             adr.postcode, adr.city, adr.country,
                             adr.uid, adr.adrid
                       FROM  auth_user_md5 AS a
                 INNER JOIN  auth_user_quick AS q USING (user_id)
                  LEFT JOIN  adresses AS adr ON(adr.uid = a.user_id)
                      WHERE  a.matricule = {?} AND
                             NOT FIND_IN_SET('pro', adr.statut)
                   ORDER BY  NOT FIND_IN_SET('active', adr.statut),
                             FIND_IN_SET('res-secondaire', adr.statut),
                             NOT FIND_IN_SET('courrier', adr.statut)", $params[1]);
            //traitement des adresses si necessaire
            if(list($cell, $age, $adr['adr1'], $adr['adr2'], $adr['adr3'], $adr['cp'], $adr['ville'],
                        $adr['pays'], $uid, $adr['adrid']) = $res->next())
            {
                $array['cell']      = $cell;
                $array['age']       = $age;
                $array['adresse'][] = $adr;


                //on clamp le numero au nombre d'adresses dispo
                $adresse = min((int) $params[2], $res->total());

                if ($adresse != 1) { //on ne veut pas la premiere adresse
                    $i = 2;
                    while(list($cell, $age, $adr['adr1'], $adr['adr2'], $adr['adr3'], $adr['cp'], $adr['ville'],
                                $adr['pays'], , $adr['adrid']) = $res->next())
                    {
                        if($adresse == $i){//si on veut cette adresse en particulier
                            $array['adresse'][0] = $adr;
                            //$res->free();
                            break;
                        }
                        elseif($adresse == 0){//si on veut toutes les adresses
                            $array['adresse'][] = $adr;
                        }
                        $i++;
                    }
                }
                
                // on rajoute les numéros de tél
                $adrid_index = array();
                foreach ($array['adresse'] as $i => $a) $adrid_index[$a['adrid']] = $i;
                // on rajoute les numéros de tels
                $restel = XDB::iterator(
                    "SELECT t.tel, t.tel_type, t.adrid
                       FROM tels AS t
                 INNER JOIN adresses AS a ON (t.adrid = a.adrid AND t.uid = a.uid)
                      WHERE t.uid = {?} AND NOT FIND_IN_SET('pro', a.statut)", $uid);
                while ($tel = $restel->next()) $array['adresse'][$adrid_index[$tel['adrid']]]['tels'][] = $tel;
                foreach ($array['adresse'] as $i => $adr) {
                    unset($lasttel);
                    foreach($adr['tels'] as $j => $t){
                        if (!isset($array['adresse'][$i]['tel']) && (strpos($t['tel_type'], 'Tél') === 0)) $array['adresse'][$i]['tel'] = $t['tel']; 
                        elseif (!isset($array['adresse'][$i]['fax']) && (strpos($t['tel_type'], 'Fax') === 0)) $array['adresse'][$i]['fax'] = $t['tel'];
                        else $lasttel = $t['tel'];
                        if (isset($array['adresse'][$i]['tel']) && isset($array['adresse'][$i]['fax'])) break; 
                    }
                    if (!isset($array['adresse'][$i]['tel']) && isset($lasttel))
                        $array['adresse'][$i]['tel'] = $lasttel;
                    elseif (!isset($array['adresse'][$i]['fax']) && isset($lasttel))
                        $array['adresse'][$i]['fax'] = $lasttel;
                    unset($array['adresse'][$i]['adrid']);
                    unset($array['adresse'][$i]['tels']);
                }
            }
            else{
                $array = false;
            }
        }

        if ($array) { // on a bien eu un résultat : le matricule etait bon

            //on n'envoit que l'age à manageurs le format est YYYY-MM-DD 0123-56-89
            $year  = (int) substr($array['age'],0,4);
            $month = (int) substr($array['age'],5,2);
            $day   = (int) substr($array['age'],8,2);
            $age   = (int) date('Y') - $year - 1;
            if(( $month < (int)date('m')) ||
                    (($month == (int)date('m')) && ($day >= (int)date('d'))))
            {
                $age += 1;
            }
            $array['age'] = $age;

            //on commence le cryptage des donnees
            if (manageurs_encrypt_init($params[1]) == 1) {//on a pas trouve la cle pour crypter
                $args  = array("erreur" => 3, "erreurstring" => $error_key);
                $reply = xmlrpc_encode_request(NULL,$args);
            } else {
                $reply = manageurs_encrypt_array($array);
                manageurs_encrypt_close();
            }
        } else {//le matricule n'etait pas valide
            $args  = array("erreur" => 2, "erreurstring" => $erreur_mat);
            $reply = xmlrpc_encode_request(NULL,$args);
        }
    } else {//le matricule n'etait pas en argument
        $args  = array("erreur" => 1, "erreurstring" => $error_mat);
        $reply = xmlrpc_encode_request(NULL,$args);
    }
            
    return $reply; 
} 

function get_nouveau_infos($method, $params) {
    global $error_mat, $error_key, $globals;
    //verif du mdp
    if(!isset($params[0]) || ($params[0] != $globals->manageurs->manageurs_pass)){return false;}
    if( !empty($params[1]) ){ // on verifie qu'on a bien un matricule

        $res = XDB::query(
            "SELECT  a.nom, a.nom_usage,a.prenom, FIND_IN_SET('femme', a.flags) as femme ,a.deces!= 0 as decede ,
            a.naissance, a.promo, concat(al.alias, '@m4x.org') as mail 
            FROM  auth_user_md5 AS a
            INNER JOIN aliases as al ON a.user_id=al.id
            WHERE al.flags='bestalias' and  a.matricule = {?}",$params[1]);
        $data=$res->fetchOneAssoc();
        //$data['mail'].='@polytechnique.org';

        
        //on commence le cryptage des donnees
        if (manageurs_encrypt_init($params[1]) == 1) {//on a pas trouve la cle pour crypter
            $args  = array("erreur" => 3, "erreurstring" => $error_key);
            $reply = xmlrpc_encode_request(NULL,$args);
        } else {
            $reply = manageurs_encrypt_array($data);
            manageurs_encrypt_close();
        }

    }
    else{
    $reply=false;
    }
        return $reply;

}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
