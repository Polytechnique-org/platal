<?php

function select_fonction($fonction){
	echo "<option value=\"\" ". (($fonction == '0')?"selected='selected'":"") .">&nbsp;</option>\n";
        $res = mysql_query("SELECT id, fonction_fr, FIND_IN_SET('titre', flags) from fonctions_def ORDER BY id");
	while(list($fid, $flabel, $ftitre) = mysql_fetch_row($res)){
		if($ftitre)
			echo "<option value=\"$fid\" " . (($fonction == $fid)?"selected='selected'":"") . ">$flabel</option>\n";
		else
			echo "<option value=\"$fid\" " . (($fonction == $fid)?"selected='selected'":"") . ">* $flabel</option>\n";
	}
	mysql_free_result($res);
}

function _select_fonction_smarty($params){
  select_fonction($params['fonction']);
}
$page->register_function('select_fonction', '_select_fonction_smarty');
?>
