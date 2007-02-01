<?php
/***************************************************************************
 *  Copyright (C) 2003-2007 Polytechnique.org                              *
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

global $adresses;

//on génère une éventuelle nouvelle adresse
if (!isset($adresses) || (count($adresses) < $nb_adr_max)){
  $adrid = generate_new_adrid();
  $adresses[$adrid]['adrid'] = $adrid;
  $adr = &$adresses[$adrid];
  $adr['adr1'] = '';
  $adr['adr2'] = '';
  $adr['adr3'] = '';
  $adr['postcode'] = '';
  $adr['city'] = '';
  $adr['country'] = '00';
  $adr['region'] = '';
  $adr['secondaire'] = 1;
  $adr['courrier'] = 0;
  $adr['active'] = 0;
  $adr['temporaire'] = 1;
  $adr['pub'] = 'private';
  $adr['nouvelle'] = 'new'; //n'est pas issue d'un formulaire (sert dans update_adresses...)
}

unset($adr);
unset($adrid);

reset($adresses);
//on génère un éventuel nouveau tel pour chaque adresse
foreach($adresses as $adrid => $adr){
  if (!isset($adr['tels'])  || count($adr['tels']) == 0) {
    $adresses[$adrid]['tels'] = array(
      array('tel' => '', 'tel_pub' => 'private', 'tel_type' => 'Tél.', 'telid' => 0, 'new_tel' => true),
      array('tel' => '', 'tel_pub' => 'private', 'tel_type' => 'Fax', 'telid' => 1, 'new_tel' => true));
  } elseif (count($adr['tels']) < $nb_tel_max) {
    $adresses[$adrid]['tels'][] = 
      array('tel' => '', 'tel_pub' => 'private', 'tel_type' => 'Autre', 'telid' => generate_new_telid($adr), 'new_tel' => true);
  }
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

$page->assign('adresses', $adresses);

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
