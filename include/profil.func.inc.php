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

?>
