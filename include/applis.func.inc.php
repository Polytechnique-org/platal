<?php

/** donne la liste déroulante des ecoles d'appli
 * @param $current application actuellement selectionnée
 * @return echo
 * @see include/form_data.inc.php
 * @see include/form_data_maj.inc.php
 * @see include/form_profil.inc.php
 * @see include/form_rech_av.inc.php
 */
function applis_options($current=0) {
    global $globals;
    echo '<option value="-1"></option>';
    $res=$globals->db->query("select * from applis_def order by text");
    while ($arr_appli=mysql_fetch_array($res)) { 
	echo '<option value="'.$arr_appli["id"].'"';
	if ($arr_appli["id"]==$current) echo " selected";
	echo '>'.$arr_appli["text"]."</option>\n";
    }
}
/** pour appeller applis_options depuis smarty
 */
function _applis_options_smarty($params){
    if(!isset($params['selected']))
	$params['selected'] = 0;
    applis_options($params['selected']);
}
$page->register_function('applis_options','_applis_options_smarty');


/** affiche un Array javascript contenant les types de chaque appli
 */
function applis_type(){
    global $globals;
    $res=$globals->db->query("select type from applis_def order by text");
    if (list($appli_type)=mysql_fetch_row($res))
	echo "new Array('".str_replace(",","','",$appli_type)."')";
    while (list($appli_type)=mysql_fetch_row($res))
	echo ",\nnew Array('".str_replace(",","','",$appli_type)."')";
    mysql_free_result($res);
}
$page->register_function('applis_type','applis_type');

/** affiche tous les types possibles d'applis
 */
function applis_type_all(){
    global $globals;
    $res = $globals->db->query("show columns from applis_def like 'type'");
    $arr_appli = mysql_fetch_array($res);
    echo str_replace(")","",str_replace("set(","",$arr_appli["Type"]));
    mysql_free_result($res);
}
$page->register_function('applis_type_all','applis_type_all');

/** formatte une ecole d'appli pour l'affichage
 */
function applis_fmt($params, &$smarty) {
    extract($params);
    $txt="";
    if (($type!="Ingénieur")&&($type!="Diplôme"))
	$txt .= $type;
    if ($text!="Université") {
	if ($txt) $txt .= " ";
	if ($url) 
	    $txt .= "<a target=\"_blank\" href=\"$url\">$text</a>";
	else 
	    $txt .= $text;
    }
    return $txt;
}
$page->register_function('applis_fmt','applis_fmt');

?>
