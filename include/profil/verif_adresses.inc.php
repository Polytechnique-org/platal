<?php

function generate_new_adrid(){
  global $adresses;
  if(!isset($adresses)) //aucune adresses => retourne 1
    return 1;
  reset($adresses);
  $i = 0;
  foreach($adresses as $numero => $adr){
    $adrid_array[$i] = $numero;
    $i++;
  }
  sort($adrid_array,SORT_NUMERIC); // classe les adrid dans l'ordre croissant
  $new_adrid = 1;
  while(list($key,$current_adrid) = each($adrid_array)){
    if($current_adrid == $new_adrid)
      $new_adrid++;
    else
      return $new_adrid; //s'ils sont differents, il y a un trou dans la liste des adrid donc new_adrid convient
  }
  //si aucun convient, on retourne le plus grand des adrid actuel + 1
  return $new_adrid;
}

function replace_ifset_adr($varname, $i){
   if (isset($_REQUEST[$varname][$i]))
       $GLOBALS['adresses'][$i][$varname] = stripslashes($_REQUEST[$varname][$i]);
}

function set_flag_adr($varname,$i){
  if(isset($_REQUEST[$varname][$i])){
     $GLOBALS['adresses'][$i][$varname] = 1;
  }
  else
     $GLOBALS['adresses'][$i][$varname] = '0';
}


function replace_address($i){
  if(!isset($GLOBALS['adresses'][$i])){
    $GLOBALS['adresses'][$i]['nouvelle'] = 'ajout';
    $GLOBALS['adresses'][$i]['adrid'] = $i;
  }
  
  replace_ifset_adr('secondaire', $i);
  set_flag_adr('courrier', $i);
  replace_ifset_adr('temporaire', $i);
  if(isset($_REQUEST['adrid_active']) && ($_REQUEST['adrid_active'] == $i))
    $GLOBALS['adresses'][$i]['active'] = 1;
  else
    $GLOBALS['adresses'][$i]['active'] = 0;
  replace_ifset_adr('adr1', $i);
  replace_ifset_adr('adr2', $i);
  replace_ifset_adr('adr3', $i);
  replace_ifset_adr('cp', $i);
  replace_ifset_adr('ville', $i);
  replace_ifset_adr('pays', $i);
  replace_ifset_adr('region', $i);
  replace_ifset_adr('tel', $i);
  replace_ifset_adr('fax', $i);
  set_flag_adr('adr_public', $i);
  set_flag_adr('adr_ax', $i);
  set_flag_adr('tel_public', $i);
  set_flag_adr('tel_ax', $i);
  if($_REQUEST['numero_formulaire'][$i])
    $GLOBALS['adresses'][$i]['numero_formulaire'] = $_REQUEST['numero_formulaire'][$i];
  else
    $GLOBALS['adresses'][$i]['numero_formulaire'] = -1;
}


//remplace par les eventuelles nouvelles valeurs :
for ($adrid = 1; $adrid <= $nb_adr_max; $adrid++) {
  if(isset($_REQUEST['adrid'][$adrid])){ //cet adrid etait donc present dans le formulaire
    replace_address($adrid);
  }
}

if(($_REQUEST['old_tab'] == 'adresses') && isset($_REQUEST['modifier'])){ // on ne valide que qd on vient du formulaire
$adresses_principales = 0;
reset($adresses);
foreach($adresses as $adrid => $adr) {
  //validité de chaque adresse
  $description = (($adr['numero_formulaire'] > 0)?"Adresse n°{$adr['numero_formulaire']}":"Nouvelle adresse");
  if (strlen(strtok($adr['adr1'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['adr1']))
    {
      $str_error = $str_error."Le champ '$description - Ligne 1' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adr['adr2'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['adr2']))
    {
      $str_error = $str_error."Le champ '$description - Ligne 2' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adr['adr3'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['adr3']))
    {
      $str_error = $str_error."Le champ '$description - Ligne 3' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adr['cp'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['cp']))
    {
      $str_error = $str_error."Le champ '$description - Code Postal' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adr['ville'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['ville']))
    {
      $str_error = $str_error."Le champ '$description - Ville' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adr['tel'],"<>{}@&#~\/:;?,!§*_`[]|%$^=\"")) < strlen($adr['tel']))
    {
      $str_error = $str_error."Le champ '$description - Téléphone' contient un caractère interdit.<BR />";
    }
  if (strlen(strtok($adr['fax'],"<>{}@&#~\/:;?,!§*_`[]|%$^=\"")) < strlen($adr['fax']))
    {
      $str_error = $str_error."Le champ '$description - Fax' contient un caractère interdit.<BR />";
    }
  if(!$adr['secondaire']){
    if($adresses_principales == 1){ //deja une adresse principale
      $str_error = $str_error."Tu ne peux avoir qu'une résidence principale.<BR />";
      $adresses_principales++;//pour eviter de repeter le message plusieurs fois
    }
    else $adresses_principales = 1;
  }
}

}

//on vire les adresses vides :
if(isset($adresses)){ // s'il y en a
  reset($adresses);
  foreach($adresses as $adrid => $adr){
    if(is_adr_empty($adrid)){
       delete_address($adrid);
    }
  }
}
//on génère une éventuelle nouvelle adresse
if (!isset($adresses) || (count($adresses) < $nb_adr_max)){
  $adrid = generate_new_adrid();
  $adresses[$adrid]['adrid'] = $adrid;
  $adr = &$adresses[$adrid];
  $adr['adr1'] = '';
  $adr['adr2'] = '';
  $adr['adr3'] = '';
  $adr['cp'] = '';
  $adr['ville'] = '';
  $adr['pays'] = '00';
  $adr['region'] = '';
  $adr['tel'] = '';
  $adr['fax'] = '';
  $adr['secondaire'] = 1;
  $adr['courrier'] = 0;
  $adr['active'] = 0;
  $adr['temporaire'] = 1;
  $adr['adr_public'] = 0;
  $adr['adr_ax'] = 0;
  $adr['tel_public'] = 0;
  $adr['tel_ax'] = 0;
  $adr['nouvelle'] = 'new'; //n'est pas issue d'un formulaire (sert dans update_adresses...)
}

unset($adr);
unset($adrid);

//tri des adresses :

reset($adresses);
$i = 1;
foreach($adresses as $adrid_ => $adr_){
  if(($adresses[$adrid_]['active']) && ($adr_['nouvelle'] != 'new')){
    $ordre_des_adrid[$i] = $adrid_;
    $i++;
    $est_attribuee[$adrid_] = 1;
  }
  else
    $est_attribuee[$adrid_] = 0;
}

reset($adresses);
foreach($adresses as $adrid_ => $adr_){
  if(($adresses[$adrid_]['secondaire'] == 0) && ($est_attribuee[$adrid_] == 0) && ($adr_['nouvelle'] != 'new')){ // principale et non attribuee
    $ordre_des_adrid[$i] = $adrid_;
    $i++;
    $est_attribuee[$adrid_] = 1;
  }
}

reset($adresses);
foreach($adresses as $adrid_ => $adr_){
  if(($adresses[$adrid_]['temporaire'] == 0) && ($est_attribuee[$adrid_] == 0) && ($adr_['nouvelle'] != 'new')){ // permanente et non attribuee
    $ordre_des_adrid[$i] = $adrid_;
    $i++;
    $est_attribuee[$adrid_] = 1;
  }
}
reset($adresses);
foreach($adresses as $adrid_ => $adr_){
  if($est_attribuee[$adrid_] == 0){ // non attribuee
     $ordre_des_adrid[$i] = $adrid_;
     $i++;
     $est_attribuee[$adrid_] = 1;
  }
}

$nb_adr = $i - 1;
$page->assign_by_ref('ordre_adrid',$ordre_des_adrid);
$page->assign('nb_adr',$nb_adr+1);

?>
