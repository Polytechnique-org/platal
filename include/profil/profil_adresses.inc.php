<?php

require_once('geoloc.inc.php');

// on limite à 6 adresses personnelles par utilisateur
$nb_adr_max = 6; // ( = max(adrid possibles)

//les adresses sont stockées dans un tableau global indéxé par adrid;

function is_adr_empty($adrid){
  $adr = &$GLOBALS['adresses'][$adrid];
  return ( 
    ($adr['adr1'] == '') && ($adr['adr2'] == '') && ($adr['adr3'] == '') &&
    ($adr['cp'] == '') && ($adr['ville'] == '') && ($adr['pays'] == '00') &&
    ($adr['tel'] == '') && ($adr['fax'] == '')
    );
}

function delete_address($adrid, $in_request_array = false){
    mysql_query("DELETE FROM adresses WHERE uid = ".$_SESSION["uid"]." AND adrid = '$adrid'");
    if($in_request_array == true){
      unset($_REQUEST['adrid'][$adrid]);
    }
    else{
      unset($GLOBALS['adresses'][$adrid]);
    }
}

//on verifie si on nous a demande une suppression
for($i = 1; $i <= $nb_adr_max; $i++){
  if( isset( $_REQUEST['adrid_del'][$i] ) ) {
    delete_address($i,true);
  }
}

//$sql_order = "ORDER BY (NOT FIND_IN_SET('active', statut)), FIND_IN_SET('temporaire', statut)";
$sql_order = '';

//recuperation des adrid
$res = mysql_query("SELECT adrid FROM adresses WHERE uid = {$_SESSION['uid']} AND NOT FIND_IN_SET('pro',statut) ".$sql_order);
$i = 1;
while(list($adrids[$i]) = mysql_fetch_row($res)){
  $adresses[$adrids[$i]]['adrid'] = $adrids[$i];
  $i++;
}

$page->assign_by_ref('adresses', $adresses);

//recuperation des donnees de la bd
$res = mysql_query(
	"SELECT
	FIND_IN_SET('res-secondaire', statut), FIND_IN_SET('courrier', statut),
	FIND_IN_SET('active', statut), FIND_IN_SET('temporaire', statut),
	adr1, adr2, adr3, cp, ville,
        pays, region, tel, fax,
	FIND_IN_SET('adr_public', visibilite), FIND_IN_SET('adr_ax', visibilite),
	FIND_IN_SET('tel_public', visibilite), FIND_IN_SET('tel_ax', visibilite)
	FROM adresses
	WHERE uid = {$_SESSION['uid']} AND NOT FIND_IN_SET('pro',statut) ".$sql_order
);

$nb_adr = mysql_num_rows($res);

for ($i = 1; $i <= $nb_adr; $i++) {
  $adrid = $adrids[$i];
  list(
       $adresses[$adrid]['secondaire'], $adresses[$adrid]['courrier'],
       $adresses[$adrid]['active'], $adresses[$adrid]['temporaire'],
       $adresses[$adrid]['adr1'], $adresses[$adrid]['adr2'], $adresses[$adrid]['adr3'], $adresses[$adrid]['cp'], $adresses[$adrid]['ville'],
       $adresses[$adrid]['pays'], $adresses[$adrid]['region'], $adresses[$adrid]['tel'], $adresses[$adrid]['fax'],
       $adresses[$adrid]['adr_public'], $adresses[$adrid]['adr_ax'],
       $adresses[$adrid]['tel_public'], $adresses[$adrid]['tel_ax']) = mysql_fetch_row($res);
  $adresses[$adrid]['nouvelle'] = 'modif';
  $adresses[$adrid]['numero_formulaire'] = -1;
}

?>
