<?php

require_once('exalead.class.php');


class Exalead{

  var $parserId;

  var $data;

  var $currentGroup;
  var $currentCategory;
  var $currentSpelling;
  var $currentHit;
  var $currentHitField;
  var $currentTextSegment;
  var $currentQuery;
  var $currentQueryTerm;
  var $currentQueryParameter;
  var $currentKeyword;


/****    Constructeur     *********/


  function Exalead(){
     $this->data = new ExaleadData();
     $this->currentGroup = new ExaleadGroup();
     $this->currentCategory = new ExaleadCategory();
     $this->currentSpelling = new ExaleadSpelling();
     $this->currentHit = new ExaleadHit();
     $this->currentHitField = new ExaleadHitField();
     $this->currentTextSegment = new ExaleadTextSegment();
     $this->currentQuery = new ExaleadQuery();
     $this->currentQueryTerm = new ExaleadQueryTerm();
     $this->currentQueryParameter = new ExaleadQueryParameter();
     $this->currentKeyword = new ExaleadKeyword();
  }



/********      Fonctions annexes relatives au parser     ********/

  function createParser(){
    $this->parserId = xml_parser_create(); 
    xml_set_element_handler($this->parserId, array(&$this, "startElement"), array(&$this, "endElement"));
    xml_set_character_data_handler($this->parserId, array(&$this, "parsePCData"));
  }
  
  function setElementHandler($stratElement, $endElement){
  }
  
  function freeParser(){
    xml_parser_free($this->parserId);
  }
  
  function parseString($string){
    if (!xml_parse($this->parserId, $string, true)) {
       die(sprintf("XML error: %s at line %d",
             xml_error_string(xml_get_error_code($this->parserId)),
             xml_get_current_line_number($this->parserId)));
    }
  }



/********        Méthode qui lance le parser           ***********/

  function parse($string){
    $this->createParser();
    $this->parseString($string);
    $this->freeParser();
  }

/*********      fonctions spécifiques à chaque balise     ******/

//Ces méthodes peuvent être surchargées

  function startQuery(&$attrs){
     $this->currentQuery->query = utf8_decode($attrs['QUERY']);
     $this->currentQuery->context = $attrs['CONTEXT'];
     $this->currentQuery->time = $attrs['TIME'];
     if(isset($attrs['INTERRUPTED'])) $this->currentQuery->interrupted = $attrs['INTERRUPTED'];
     if(isset($attrs['BROWSED'])) $this->currentQuery->browsed = $attrs['BROWSED'];
  }
  
  function StartQueryTerm(&$attrs){
     $this->currentQueryTerm->level = $attrs['LEVEL'];
     $this->currentQueryTerm->regexp = utf8_decode($attrs['REGEXP']);
  }

  function startQueryParameter(&$attrs){
     $this->currentQueryParameter->name = $attrs['NAME'];
     if(isset($attrs['VALUE'])) $this->currentQueryParameter->value = utf8_decode($attrs['VALUE']);
  }

  function startKeyword(&$attrs){
     if(isset($attrs['NAME'])) $this->currentKeyword->name = $attrs['NAME'];
     $this->currentKeyword->display = utf8_decode( $attrs['DISPLAY'] );
     $this->currentKeyword->count = $attrs['COUNT'];
     $this->currentKeyword->automatic = $attrs['AUTOMATIC'];
     if(isset($attrs['REFINEHREF'])) $this->currentKeyword->refine_href = $attrs['REFINEHREF'];
     if(isset($attrs['EXCLUDEHREF'])) $this->currentKeyword->exclude_href = $attrs['EXCLUDEHREF'];
     if(isset($attrs['RESETHREF'])) $this->currentKeyword->reset_href = $attrs['RESETHREF'];
  }

  function startHits(&$attrs){
     $this->data->nmatches = $attrs['NMATCHES'];
     $this->data->nhits = $attrs['NHITS'];
     if(isset($attrs['INTERRUPTED'])) $this->data->interrupted = $attrs['INTERRUPTED'];
     $this->data->last = $attrs['LAST'];
     $this->data->end = $attrs['END'];
     $this->data->start = $attrs['START'];
  }
  
  function startHit(&$attrs){
     $this->currentHit->url = $attrs['URL'];
     $this->currentHit->score = $attrs['SCORE'];
  }
  
  function startHitField(&$attrs){
     $this->currentHitField->name = $attrs['NAME'];
     if(isset($attrs['VALUE'])) $this->currentHitField->value = utf8_decode($attrs['VALUE']);
  }
 
  function startTextSeg(&$attrs){
    $this->currentTextSegment->setHighlighted($attrs['HIGHLIGHTED']);
  }
  function startTextCut(&$attrs){}

  function startSpellingSuggestionVariant(&$attrs){
     $this->currentSpelling->setDisplay($attrs['DISPLAY']);
     $this->currentSpelling->setQueryHref($attrs['QUERY']);
  }

  function startGroup(&$attrs){
     $this->currentGroup->setTitle(utf8_decode($attrs['TITLE']));
     $this->currentGroup->setClipped($attrs['CLIPPED']);
     $this->currentGroup->setCount($attrs['COUNT']);
     $this->currentGroup->setBrowsed($attrs['BROWSED']);
     if(isset($attrs['CLIPHREF'])) $this->currentGroup->setClipHref($attrs['CLIPHREF']);
     if(isset($attrs['RESETHREF'])) $this->currentGroup->setResetHref($attrs['RESETHREF']);
  }

  function startCategory(&$attrs){
     $this->currentCategory->name = $attrs['NAME'];
     $this->currentCategory->display = utf8_decode($attrs['DISPLAY']);
     $this->currentCategory->count = $attrs['COUNT'];
     $this->currentCategory->automatic = $attrs['AUTOMATIC'];
     if(isset($attrs['REFINEHREF'])) $this->currentCategory->refine_href = '_c=%2B'.substr($attrs['REFINEHREF'],4);
     if(isset($attrs['EXCLUDEHREF'])) $this->currentCategory->exclude_href = $attrs['EXCLUDEHREF'];
     if(isset($attrs['RESETHREF'])) $this->currentCategory->reset_href = $attrs['RESETHREF'];
     $this->currentCategory->cref = $attrs['CREF'];
     $this->currentCategory->gid = $attrs['GID'];
     $this->currentCategory->gcount = $attrs['GCOUNT'];
  }

  function startSearch(&$attrs){}

  function startElement($parser, $name, $attrs) {
   //echo "start $name<br />";
   //recupération des paramètres de query
   if($name == 'QUERY'){
     $this->startQuery($attrs);
   }
   elseif($name == 'QUERYTERM'){
     $this->startQueryTerm($attrs);
   }
   elseif($name == 'QUERYPARAMETER'){
     $this->startQueryParameter($attrs);
   }
   //gestion des mots-clés
   elseif($name == 'KEYWORD'){
     $this->startKeyword($attrs);
   }
   //gestion des resultats
   elseif($name == 'HITS'){
     $this->startHits($attrs);
   }
   elseif($name == 'HIT'){
     $this->startHit($attrs);
   }
   elseif($name == 'HITFIELD'){
     $this->startHitField($attrs);
   }
   elseif($name == 'TEXTSEG'){
     $this->startTextSeg($attrs);
   }
   elseif($name == 'TEXTCUT'){
     $this->startTextCut($attrs);
   }
   //gestion suggestions d'orthographe
   elseif($name == 'SPELLINGSUGGESTIONVARIANT'){
     $this->startSpellingSuggestionVariant($attrs);
   }
   //gestion des categories pour raffiner
   elseif($name == 'GROUP'){
     $this->startGroup($attrs);
   }
   elseif($name == 'CATEGORY'){
     $this->startCategory($attrs);
   }
   elseif($name == 'SEARCH'){
     $this->startSearch($attrs);
   }
  }

  function endQuery(){
     $this->data->query = $this->currentQuery;
     $this->currentQuery->clear();
  }
  function endQueryTerm(){
     $this->currentQuery->addTerm($this->currentQueryTerm);
     $this->currentQueryTerm->clear();
  }
  function endQueryParameter(){
     $this->currentQuery->addParameter($this->currentQueryParameter);
     $this->currentQueryParameter->clear();
  }
  function endKeyword(){
     $this->data->addKeyword($this->currentKeyword);
     $this->currentKeyword->clear();
  }
  function endHits(){
  }
  function endHit(){
     $this->data->addHit($this->currentHit);
     $this->currentHit->clear();
  }
  function endHitField(){
     $this->currentHit->addHitField($this->currentHitField);
     $this->currentHitField->clear();
  }
  function endTextSeg(){
     $this->currentHitField->addTextSegment($this->currentTextSegment);
     $this->currentTextSegment->clear();
  }
  function endTextCut(){
     $this->currentHitField->setHasTextCut(true);
  }
  function endSpellingSuggestionVariant(){
     $this->data->addSpelling($this->currentSpelling);
     $this->currentSpelling->clear();
  }
  function endGroup(){
     $this->data->addGroup($this->currentGroup);
     $this->currentGroup->clear();
  }
  function endCategory(){
     $this->currentGroup->addCategory($this->currentCategory);
     $this->currentCategory->clear();
  }
  function endSearch(){
  }

  
  function endElement($parser, $name) {
   //echo "end $name<br >";
   if($name == 'QUERY'){
     $this->endQuery();
   }
   elseif($name == 'QUERYTERM'){
     $this->endQueryTerm();
   }
   elseif($name == 'QUERYPARAMETER'){
     $this->endQueryParameter();
   }
   elseif($name == 'KEYWORD'){
     $this->endKeyword();
   }
   elseif($name == 'HITS'){
     $this->endHits();
   }
   elseif($name == 'HIT'){
     $this->endHit();
   }
   elseif($name == 'HITFIELD'){
     $this->endHitField();
   }
   elseif($name == 'TEXTSEG'){
     $this->endTextSeg();
   }
   elseif($name == 'TEXTCUT'){
     $this->endTextCut();
   }
   //gestion suggestions d'orthographe
   elseif($name == 'SPELLINGSUGGESTIONVARIANT'){
     $this->endSpellingSuggestionVariant();
   }
   //gestion des categories pour raffiner
   elseif($name == 'GROUP'){
     $this->endGroup();
   }
   elseif($name == 'CATEGORY'){
     $this->endCategory();
   }
   elseif($name == 'SEARCH'){
     $this->endSearch();
   }
  }

  function parsePCData($parser, $text){
    $this->currentTextSegment->append(utf8_decode($text));
  }

}

?>
