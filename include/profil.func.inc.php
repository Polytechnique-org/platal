<?php

require_once('applis.func.inc.php');

function replace_ifset(&$var,$req) {
  if (isset($_REQUEST[$req])){
    $var = stripslashes($_REQUEST[$req]);
  }
}

function replace_ifset_i(&$var,$req,$i) {
  if (isset($_REQUEST[$req][$i])){
    $var[$i] = stripslashes($_REQUEST[$req][$i]);
  }
}

function replace_ifset_i_j(&$var,$req,$i,$j) {
  if (isset($_REQUEST[$req][$j])){
    $var[$i] = stripslashes($_REQUEST[$req][$j]);
  }
}

//pour afficher qqchose en html par un modifier smarty
function _print_html_modifier($string){
  return htmlentities($string);
}


//pour afficher depuis le php
function print_html($string){
  echo _print_html_modifier($string);
}

//pour rentrer qqchose dans la base
function put_in_db($string){
  return trim(addslashes($string));
}

function select_options($table,$valeur,$champ="text",$pad=false,$where="") {
  $sql = "SELECT id,$champ FROM $table $where ORDER BY $champ";
  $result = mysql_query($sql);
  // on ajoute une entree vide si $pad est vrai
  if ($pad) 
    printf("<option value=\"0\" %s></option>\n",($valeur==0?"selected":""));
  while (list($my_id,$my_text) = mysql_fetch_row($result)) {
    printf("<option value=\"%s\" %s>%s</option>\n",$my_id,($valeur==$my_id?"selected":""),$my_text);
  }
  mysql_free_result($result);
}

function _select_options_smarty($params){
  if((!isset($params['table'])) || (!isset($params['valeur'])))
    return;
  if(!isset($params['champ']))
    $params['champ'] = 'text';
  if(!isset($params['pad']) || !($params['pad']))
    $pad = false;
  else
    $pad = true;
  if(!isset($params['where']))
    $params['where'] = '';
  select_options($params['table'], $params['valeur'], $params['champ'], $pad, $params['where']);
}

?>
