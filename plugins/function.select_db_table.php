<?php

function select_options($table,$valeur,$champ="text",$pad=false,$where="") {
    global $globals;
    $sql = "SELECT id,$champ FROM $table $where ORDER BY $champ";
    $result = $globals->db->query($sql);

    $sel = ' selected="selected"';

    // on ajoute une entree vide si $pad est vrai
    $html = "";
    if ($pad)
	$html.= '<option value="0"'.($valeur==0?$sel:"")."></option>\n";
    while (list($my_id,$my_text) = mysql_fetch_row($result)) {
	$html .= printf("<option value=\"%s\" %s>%s</option>\n",$my_id,($valeur==$my_id?$sel:""),$my_text);
    }
    mysql_free_result($result);
    return $html;
}

function smarty_function_select_db_table($params, &$smarty) {
    if(empty($params['table']))
	return;
    if(empty($params['champ']))
	$params['champ'] = 'text';
    if(empty($params['pad']) || !($params['pad']))
	$pad = false;
    else
	$pad = true;
    if(empty($params['where']))
	$params['where'] = '';
    return select_options($params['table'], $params['valeur'], $params['champ'], $pad, $params['where']);
}

?>
