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


function generate_new_adrid(){
  global $adresses;
  if(!isset($adresses)) //aucune adresses => retourne 1
    return 1;
  reset($adresses);
  $i = 0;
  $adrid_array = Array();
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

function generate_new_telid($adr){
  $i = 0;
  $telid_array = Array();
  foreach($adr['tels'] as $tel){
    $telid_array[$i] = $tel['telid'];
    $i++;
  }
  sort($telid_array,SORT_NUMERIC); // classe les adrid dans l'ordre croissant
  $new_telid = 0;
  foreach($telid_array as $current_telid)
    if ($current_telid == $new_telid)
      $new_telid ++;
    else 
      return $new_telid;//s'ils sont differents, il y a un trou dans la liste des telid donc new_telid convient
  //si aucun convient, on retourne le plus grand des telid actuel + 1
  return $new_telid;
}

function replace_ifset_adr($varname, $i){
  $tab = Env::v($varname, Array());
  if (isset($tab[$i]))
       $GLOBALS['adresses'][$i][$varname] = $tab[$i];
}

function replace_ifset_tel($varname, $i, $t){
  $tab = Env::v($varname.$t, Array());
  if (isset($tab[$i]))
       $GLOBALS['adresses'][$i]['tels'][$t][$varname] = $tab[$i];  
}

function get_adr_arg($varname, $i) {
  $tab = Env::v($varname, Array());
  return $tab[$i];
}

function set_flag_adr($varname,$i){
  $tab = Env::v($varname, Array());
  if (isset($tab[$i])){
     $GLOBALS['adresses'][$i][$varname] = 1;
  }
  else
     $GLOBALS['adresses'][$i][$varname] = '0';
}

function replace_tel($i, $t){
  replace_ifset_tel('telid', $i, $t);
  replace_ifset_tel('tel', $i, $t);
  replace_ifset_tel('tel_pub', $i, $t);
  replace_ifset_tel('tel_type', $i, $t);
  replace_ifset_tel('new_tel', $i, $t);
}

function replace_address($i){
  global $adresses, $nb_tel_max;
  if(!isset($adresses[$i])){
    $adresses[$i]['nouvelle'] = 'ajout';
    $adresses[$i]['adrid'] = $i;
  }
  
  replace_ifset_adr('secondaire', $i);
  set_flag_adr('courrier', $i);
  replace_ifset_adr('temporaire', $i);
  if(Env::i('adrid_active', $i+1) == $i)
    $adresses[$i]['active'] = 1;
  else
    $adresses[$i]['active'] = 0;
  replace_ifset_adr('adr1', $i);
  replace_ifset_adr('adr2', $i);
  replace_ifset_adr('adr3', $i);
  replace_ifset_adr('postcode', $i);
  replace_ifset_adr('city', $i);
  replace_ifset_adr('cityid', $i);
  replace_ifset_adr('country', $i);
  replace_ifset_adr('region', $i);
  replace_ifset_adr('pub', $i);
  
  for ($telid = 0; $telid <= $nb_tel_max; $telid++) {
    $tab = Env::v('telid'.$telid, Array());
    if(isset($tab[$i])){ //ce telid etait donc present dans le formulaire
      replace_tel($i, $telid);
    }
  }
 
  if (!get_adr_arg('parsevalid', $i)) replace_ifset_adr('txt', $i);
  $tab = Env::v('numero_formulaire', Array());
  if($tab[$i])
    $adresses[$i]['numero_formulaire'] = $tab[$i];
  else
    $adresses[$i]['numero_formulaire'] = -1;
}

function geoloc_adresse($i) {
  global $adresses;
  $change = Env::v('change'.$i);
  if (get_adr_arg('parsevalid', $i) || ($adresses[$i]['txt'] && $change) || (!$adresses[$i]['cityid'])) {
  	require_once('geoloc.inc.php');
	// erases the previous address (but not the phone or pub)
	$adresses[$i] = array_merge($adresses[$i], empty_address());
	// localize new address
	$new = get_address_infos($adresses[$i]['txt']);
	if (compare_addresses_text($adresses[$i]['txt'], $geotxt = get_address_text($new)) || get_adr_arg('parsevalid', $i)) 
		$adresses[$i] = array_merge($adresses[$i], $new);
	else {
		$adresses[$i] = array_merge($adresses[$i], cut_address($adresses[$i]['txt']));
		$adresses[$i]['geoloc'] = $geotxt;
		$adresses[$i]['geoloc_cityid'] = $new['cityid'];
	}
  }
  $adresses[$i]['txt'] = get_address_text($adresses[$i]);
}

//remplace par les eventuelles nouvelles valeurs :
for ($adrid = 1; $adrid <= $nb_adr_max; $adrid++) {
  $tab = Env::v('adrid', Array());
  if(isset($tab[$adrid])){ //cet adrid etait donc present dans le formulaire
    replace_address($adrid);
  }
  if (isset($adresses[$adrid]['txt']))
    geoloc_adresse($adrid);
}

if ($opened_tab == 'adresses' && Env::has('modifier')){ // on ne valide que qd on vient du formulaire
$adresses_principales = 0;
reset($adresses);
foreach($adresses as $adrid => $adr) {
  //validité de chaque adresse
  $description = (($adr['numero_formulaire'] > 0)?"Adresse n°{$adr['numero_formulaire']}":"Nouvelle adresse");
  if (strlen(strtok($adr['adr1'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['adr1']))
    {
      $page->trig("Le champ '$description - Ligne 1' contient un caractère interdit.");
    }
  if (strlen(strtok($adr['adr2'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['adr2']))
    {
      $page->trig("Le champ '$description - Ligne 2' contient un caractère interdit.");
    }
  if (strlen(strtok($adr['adr3'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['adr3']))
    {
      $page->trig("Le champ '$description - Ligne 3' contient un caractère interdit.");
    }
  if (strlen(strtok($adr['postcode'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['postcode']))
    {
      $page->trig("Le champ '$description - Code Postal' contient un caractère interdit.");
    }
  if (strlen(strtok($adr['city'],"<>{}@~?!§*`|%$^=+")) < strlen($adr['postcode']))
    {
      $page->trig("Le champ '$description - Ville' contient un caractère interdit.");
    }
  foreach ($adr['tels'] as $tel) {
    if (strlen(strtok($tel['tel'],"<>{}@&#~\/:;?,!§*_`[]|%$^=\"")) < strlen($tel['tel']))
      {
        $page->trig("Le champ '$description - ".$tel['tel_type']."' contient un caractère interdit.");
      }
    }
  if(!$adr['secondaire']){
    if($adresses_principales == 1){ //deja une adresse principale
      $page->trig("Tu ne peux avoir qu'une résidence principale.");
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
    // on vire les tels vides
    foreach ($adr['tels'] as $telid => $tel) {
      if ($tel['tel'] == '') unset($adresses[$adrid]['tels'][$telid]);
    }
    if(is_adr_empty($adrid)){
       delete_address($adrid);
    }
  }
}

?>
