<?php

/** donne la liste déroulante des ecoles d'appli
 * @param $current application actuellement selectionnée
 * @return echo
 * @see include/form_data.inc.php
 * @see include/form_data_maj.inc.php
 * @see include/form_profil.inc.php
 * @see include/form_rech_av.inc.php
function applis_options($current=0) {
  echo '<option value="-1"></option>';
  $res=$globals->db->query("select * from applis_def order by text");
  while ($arr_appli=mysql_fetch_array($res)) { 
    echo '<option value="'.$arr_appli["id"].'"';
    if ($arr_appli["id"]==$current) echo " selected";
    echo '>'.$arr_appli["text"]."</option>\n";
  }
}
 */

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
