<?php

function select_secteur($secteur){
	if($secteur == '') $secteur = -1;
	echo "<option value=\"\" ". (($secteur == '')?"selected":"") .">&nbsp;</option>\n";
	$res = mysql_query("SELECT id, label FROM emploi_secteur");
	while(list($tmp_id, $tmp_label) = mysql_fetch_row($res)){
		echo "<option value=\"$tmp_id\" " . (($secteur == $tmp_id)?"selected":"") . ">$tmp_label</option>\n";
	}
	mysql_free_result($res);
}

function select_ss_secteur($secteur,$ss_secteur){
	if($secteur != ''){
		echo "<option value=\"\">&nbsp;</option>\n";
		$res = mysql_query("SELECT id, label FROM emploi_ss_secteur WHERE secteur = '$secteur'");
		while(list($tmp_id, $tmp_label) = mysql_fetch_row($res)){
			echo "<option value=\"$tmp_id\" ". (($ss_secteur == $tmp_id)?"selected":"") .">$tmp_label</option>\n";
		}
		mysql_free_result($res);
	}
	else{
	  echo "<option value=\"\" selected>&nbsp;</option>\n";
	}
}

//fonctions pour smarty
function _select_secteur_smarty($params){
  select_secteur($params['secteur']);
}
function _select_ss_secteur_smarty($params){
  if(!isset($params['secteur'])) return;
  select_ss_secteur($params['secteur'], $params['ss_secteur']);
}
$page->register_function('select_secteur', '_select_secteur_smarty');
$page->register_function('select_ss_secteur', '_select_ss_secteur_smarty');
?>
