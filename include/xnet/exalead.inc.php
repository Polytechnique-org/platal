<?php

require_once($globals->xnet->exalead.'/exalead.parser.inc.php');

//Exemple de surclassement du parseur

/*
class OffreHit extends ExaleadHit{

  var $titre = "";
  var $description = "";
  var $type_offre = "";
  var $type_entr = "";
  var $id = "";
  var $zone = "";
  var $pays = "";
  var $entreprise = "";
  var $entr_nom = "";
  var $creation = "";
  var $secteur = "";
  var $fonction = "";
  var $alumnus = "";
  var $salaire = "";
  var $experience = "";

  function OffreHit(){
    parent::ExaleadHit();
  }

  function clear(){
    parent::clear();
    $titre = "";
    $description = "";
    $type_offre = "";
    $type_entr = "";
    $id = "";
    $id = "";
    $zone = "";
    $pays = "";
    $entreprise = "";
    $entr_nom = "";
    $creation = "";
    $secteur = "";
    $fonction = "";
    $alumnus = "";
    $salaire = "";
  }

}

class ExaleadOffre extends Exalead{

  //var $currentOffreHit;
  
  function ExaleadOffre($base_cgi = ''){
    parent::Exalead($base_cgi);
    $this->currentHit = new OffreHit();
  }

  function endHit(){
    $this->data->addHit($this->currentHit);
    $this->currentHit->clear();
  }

  function endHitField(){
  }

  function startHit(&$attrs){
    $res = explode('/', $attrs['URL']);
    $this->currentHit->id = end($res);
  }

  function startHitField(&$attrs){
    if(isset($attrs['VALUE'])){
      if($attrs['NAME'] == 'creation'){
        $date = utf8_decode($attrs['VALUE']);
	$annee = substr($date, 0, 4);
	$mois = substr($date, 5, 2);
	$jour = substr($date, 8, 2);
	$heure = substr($date, 11, 2);
	$minute = substr($date, 14, 2);
	$seconde = substr($date, 17, 2);
        $this->currentHit->$attrs['NAME'] = mktime($heure, $minute, $seconde, $mois, $jour, $annee);
      }
      else
        $this->currentHit->$attrs['NAME'] = utf8_decode($attrs['VALUE']);
    }
  }

}
*/

?>
