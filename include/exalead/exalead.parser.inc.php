<?php

require_once('exalead.class.php');

function convert_url($string){
  return str_replace('+', '%2B', $string);
}

$GLOBALS['query_all'] = 'a*';

class Exalead{

  var $parserId;

  var $data;

  var $currentGroup;
  var $currentCategories = array();
  var $currentSpelling;
  var $currentHit;
  var $currentHitField;
  var $currentHitGroup;
  var $currentHitCategory;
  var $currentAction;
  var $currentTextSegment;
  var $currentQuery;
  var $currentQueryTerm;
  var $currentQueryParameter;
  var $currentKeyword;

  //url de base du produit Exalead
  var $base_cgi = '';

  // Query to dump indexed database
  var $query_all= '';

/****    Constructeur     *********/


  function Exalead($base_cgi = '', $override_query_all = ''){
     $this->data = new ExaleadData();
     $this->currentGroup = new ExaleadGroup();
     $this->currentCategories = array();
     $this->currentSpelling = new ExaleadSpelling();
     $this->currentHit = new ExaleadHit();
     $this->currentHitField = new ExaleadHitField();
     $this->currentHitGroup = new ExaleadHitGroup();
     $this->currentHitCategory = new ExaleadHitCategory();
     $this->currentAction = new ExaleadAction();
     $this->currentTextSegment = new ExaleadTextSegment();
     $this->currentQuery = new ExaleadQuery();
     $this->currentQueryTerm = new ExaleadQueryTerm();
     $this->currentQueryParameter = new ExaleadQueryParameter();
     $this->currentKeyword = new ExaleadKeyword();

     //url de base du produit Exalead
     $this->base_cgi = $base_cgi;
     if(!empty($override_query_all)){
       $this->query_all = $override_query_all;
     }
     else{
       $this->query_all = $GLOBALS['query_all'];
     }
  }

/****  Fonctions d'interface avec le cgi d'Exalead Corporate   ******/

  function set_base_cgi($base_cgi){
    $this->base_cgi = $base_cgi;
  }

  //retourne vrai si une requete a été faite, faux sinon
  function query($varname = 'query'){
    if(!empty($_REQUEST[$varname])){

      $this->first_query(stripslashes($_REQUEST[$varname]));
      return true;
    }
    elseif(isset($_REQUEST['_C'])){

      $this->handle_request();
      return true;
    }
    return false;
  }

  //a appeller pour faire la premiere requete
  function first_query($query, $offset = 0){
    if(empty($this->base_cgi)) return false;
    
    //$tmp = parse_url($this->base_cgi);
    //$view_name = substr($tmp['path'], 5);
    //$query_exa = $this->base_cgi."?_q=".urlencode($query)."&_f=xml2&A=-1&_vn=".$view_name;
    $query_exa = $this->base_cgi."?_q=".urlencode($query)."&_f=xml2";
    if($offset > 0){
      $query_exa .= "&_s=".$offset;
    }
    
    $xml_response = file_get_contents($query_exa);
    /*$xml_response = '';
    $query_explode = parse_url($query_exa);
    
    $fp = fsockopen("murphy.m4x.org", 10000, $errno, $errstr, 30);
    if (!$fp) {
      echo "$errstr ($errno)<br />\n";
    } else {
      $out = "GET {$query_explode['path']}?{$query_explode['query']} HTTP/1.1\r\n";
      $out .= "Host: murphy.m4x.org:10000\r\n";
      $out .= "Accept: text/xml\r\n";
      $out .= "Accept-Charset: utf-8\r\n";
      $out .= "Connection: Close\r\n\r\n";
    
      fwrite($fp, $out);
      $body = false;
      while (!feof($fp)) {
       $s = fgets($fp, 1024);
        if ( $body )
          $xml_response .= $s;
        if ( $s == "\r\n" )
          $body = true;
      }
      fclose($fp);
    }*/
    //echo $xml_response;exit;
    $this->parse($xml_response);
    //var_dump($this);
  }

  //pour recuperer tous les résultats d'une base indexée
  function get_db_dump(){
    $this->first_query($this->query_all);
  }

  function handle_request(){
    if(empty($this->base_cgi)) return false;
    if(empty($_REQUEST['_C'])) return false;// _C est le contexte Exalead
    $query_exa = $this->base_cgi.'/_C='.str_replace(' ', '%20', $_REQUEST['_C']).'&_f=xml2';
    if(!empty($_REQUEST['_s'])){
      $query_exa .= "&_s=".((int) $_REQUEST['_s']);
    }
    $xml_response = file_get_contents($query_exa);
    $this->parse($xml_response);
  }

/********      Fonctions annexes relatives au parser     ********/

  function createParser(){
    $this->parserId = xml_parser_create(); 
    xml_set_element_handler($this->parserId, array(&$this, "startElement"), array(&$this, "endElement"));
    xml_set_character_data_handler($this->parserId, array(&$this, "parsePCData"));
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
     if(isset($attrs['REFINEHREF'])) $this->currentKeyword->refine_href = convert_url($attrs['REFINEHREF']);
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
 
  function startHitGroup(&$attrs){
     $this->currentHitGroup->title = utf8_decode($attrs['TITLE']);
     $this->currentHitGroup->gid = $attrs['GID'];
  }
  
  function startHitCategory(&$attrs){
     $this->currentHitCategory->name = $attrs['NAME'];
     $this->currentHitCategory->display = utf8_decode($attrs['DISPLAY']);
     $this->currentHitCategory->cref = $attrs['CREF'];
     $this->currentHitCategory->gid = $attrs['GID'];
     if(isset($attrs['BROWSEHREF'])) $this->currentHitCategory->browsehref = $attrs['BROWSEHREF'];
  }
  
  function startAction(&$attrs){
     $this->currentAction->display = $attrs['DISPLAY'];
     $this->currentAction->kind = $attrs['KIND'];
     if(isset($attrs['EXECHREF']))$this->currentAction->execHref = $attrs['EXECHREF'];
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
     $this->currentGroup->setGid(utf8_decode($attrs['GID']));
     $this->currentGroup->setTitle(utf8_decode($attrs['TITLE']));
     $this->currentGroup->setClipped($attrs['CLIPPED']);
     $this->currentGroup->setCount($attrs['COUNT']);
     $this->currentGroup->setBrowsed($attrs['BROWSED']);
     if(isset($attrs['CLIPHREF'])) $this->currentGroup->setClipHref($attrs['CLIPHREF']);
     if(isset($attrs['RESETHREF'])) $this->currentGroup->setResetHref($attrs['RESETHREF']);
  }

  function startCategory(&$attrs){
     $currentCategory = new ExaleadCategory();
     $currentCategory->name = utf8_decode($attrs['NAME']);
     $currentCategory->display = utf8_decode($attrs['DISPLAY']);
     $currentCategory->count = $attrs['COUNT'];
     $currentCategory->automatic = $attrs['AUTOMATIC'];
     $currentCategory->cref = $attrs['CREF'];
     if(isset($attrs['REFINEHREF'])) $currentCategory->refine_href = convert_url($attrs['REFINEHREF']);
     $currentCategory->exclude_href = '_c=-'.$currentCategory->cref;
     if(isset($attrs['RESETHREF'])){
       $currentCategory->reset_href = $attrs['RESETHREF'];
     }
     $currentCategory->gid = $attrs['GID'];
     $currentCategory->gcount = $attrs['GCOUNT'];
     $this->currentCategories[] = $currentCategory;
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
   elseif($name == 'HITGROUP'){
     $this->startHitGroup($attrs);
   }
   elseif($name == 'HITCATEGORY'){
     $this->startHitCategory($attrs);
   }
   elseif($name == 'ACTION'){
     $this->startAction($attrs);
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
  function endHitGroup(){
    $this->currentHit->addHitGroup($this->currentHitGroup);
    $this->currentHitGroup->clear();
  }
  function endHitCategory(){
    $this->currentHitGroup->addHitCategory($this->currentHitCategory);
    $this->currentHitCategory->clear();
  }
  function endAction(){
    $this->currentHit->addAction($this->currentAction);
    $this->currentAction->clear();
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
     //the parent element is a Group element ?
     if(count($this->currentCategories) == 1){
       $this->currentGroup->addCategory(array_pop($this->currentCategories));
     }
     else{
       //var_dump($this->currentCategories);
       $category = array_pop($this->currentCategories);
       //reset($this->currentCategories);
       end($this->currentCategories);
       //var_dump($this->currentCategories);
       $parentCategory = &$this->currentCategories[key($this->currentCategories)];
       //var_dump($parentCategory);
       $parentCategory->addCategory($category);
     }
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
   elseif($name == 'HITGROUP'){
     $this->endHitGroup();
   }
   elseif($name == 'HITCATEGORY'){
     $this->endHitCategory();
   }
   elseif($name == 'ACTION'){
     $this->endAction();
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
