<?php

/** donne la liste déroulante des pays
 * @param $current pays actuellement selectionné
 * @return echo
 */
function geoloc_pays($current) {
  $sql = "SELECT a2,pays FROM geoloc_pays ORDER BY pays";

  $result = mysql_query($sql);
  while (list($my_id,$my_pays) = mysql_fetch_row($result))
    printf("<option value=\"%s\" %s>%s</option>\n",$my_id,($current==$my_id?"selected='selected'":""),$my_pays);
}
function _geoloc_pays_smarty($params){
  if(!isset($params['pays']))
    return;
  geoloc_pays($params['pays']);
}
$page->register_function('geoloc_pays', '_geoloc_pays_smarty');

/** donne la liste deroulante des regions pour un pays
 * @param $pays le pays dont on veut afficher les regions
 * @param $current la region actuellement selectionnee
 * @return echo
 */
function geoloc_region($pays,$current) {
  $sql = "SELECT region,name FROM geoloc_region where a2='".$pays."' ORDER BY name";
  $result = mysql_query($sql);
  
  echo "<option value=\"\"></option>";
  while (list($regid,$regname) = mysql_fetch_row($result))
    printf("<option value=\"%s\" %s>%s</option>\n",$regid,($current==$regid?"selected='selected'":""),$regname);
}
function _geoloc_region_smarty($params){
  if(!isset($params['pays']))
    return;
  if(!isset($params['region']))
    return;
  geoloc_region($params['pays'], $params['region']);
}
$page->register_function('geoloc_region', '_geoloc_region_smarty');

?>

